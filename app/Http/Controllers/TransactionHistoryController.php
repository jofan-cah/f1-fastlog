<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\ItemDetail;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionHistoryController extends Controller
{
    /**
     * Display transaction history dashboard
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['item', 'createdBy', 'approvedBy']);

        // Role-based filtering
        $user = auth()->user();
        $levelName = strtolower($user->getUserLevel()->level_name ?? '');

        if ($levelName === 'teknisi') {
            $query->where('created_by', $user->user_id);
        }

        // Apply filters
        if ($request->has('type') && $request->type !== '') {
            $query->where('transaction_type', $request->type);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id') && $request->user_id !== '' && $levelName !== 'teknisi') {
            $query->where('created_by', $request->user_id);
        }

        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->paginate(20);

        // Get summary statistics
        $stats = $this->getHistoryStats($request);

        // Get filter options
        $transactionTypes = Transaction::getTransactionTypes();
        $transactionStatuses = Transaction::getStatuses();
        $users = [];

        if ($levelName !== 'teknisi') {
            $users = \App\Models\User::where('is_active', true)
                ->orderBy('full_name')
                ->get(['user_id', 'full_name']);
        }

        return view('transaction-history.index', compact(
            'transactions',
            'stats',
            'transactionTypes',
            'transactionStatuses',
            'users'
        ));
    }

    /**
     * Show detailed transaction view
     */
    public function show(Transaction $transaction)
    {
        // Check permission for teknisi
        $user = auth()->user();
        $levelName = strtolower($user->getUserLevel()->level_name ?? '');

        if ($levelName === 'teknisi' && $transaction->created_by !== $user->user_id) {
            abort(403, 'Unauthorized access');
        }

        $transaction->load([
            'item.stock',
            'createdBy',
            'approvedBy',
            'transactionDetails.itemDetail'
        ]);

        // Get related transactions for context
        $relatedTransactions = Transaction::where('item_id', $transaction->item_id)
            ->where('transaction_id', '!=', $transaction->transaction_id)
            ->with(['createdBy', 'approvedBy'])
            ->orderBy('transaction_date', 'desc')
            ->limit(5)
            ->get();

        return view('transaction-history.show', compact('transaction', 'relatedTransactions'));
    }

    /**
     * Get transaction analytics
     */
    public function analytics(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        // Basic stats
        $basicStats = [
            'total_transactions' => Transaction::where('transaction_date', '>=', $startDate)->count(),
            'pending_transactions' => Transaction::where('status', Transaction::STATUS_PENDING)->count(),
            'completed_transactions' => Transaction::where('status', Transaction::STATUS_APPROVED)
                ->where('approved_date', '>=', $startDate)->count(),
            'rejected_transactions' => Transaction::where('status', Transaction::STATUS_REJECTED)
                ->where('approved_date', '>=', $startDate)->count(),
        ];

        // Transaction trends by type
        $typeStats = Transaction::where('transaction_date', '>=', $startDate)
            ->groupBy('transaction_type')
            ->selectRaw('transaction_type, COUNT(*) as count')
            ->get()
            ->pluck('count', 'transaction_type');

        // Daily transaction trends
        $dailyTrends = Transaction::where('transaction_date', '>=', $startDate)
            ->selectRaw('DATE(transaction_date) as date, transaction_type, COUNT(*) as count')
            ->groupBy(['date', 'transaction_type'])
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        // Most active users
        $activeUsers = Transaction::where('transaction_date', '>=', $startDate)
            ->with('createdBy')
            ->groupBy('created_by')
            ->selectRaw('created_by, COUNT(*) as transaction_count')
            ->orderBy('transaction_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'user_name' => $item->createdBy->full_name ?? 'Unknown',
                    'transaction_count' => $item->transaction_count
                ];
            });

        // Most requested items
        $popularItems = Transaction::where('transaction_date', '>=', $startDate)
            ->where('transaction_type', Transaction::TYPE_OUT)
            ->with('item')
            ->groupBy('item_id')
            ->selectRaw('item_id, COUNT(*) as request_count')
            ->orderBy('request_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'item_name' => $item->item->item_name ?? 'Unknown',
                    'item_code' => $item->item->item_code ?? 'N/A',
                    'request_count' => $item->request_count
                ];
            });

        return response()->json([
            'success' => true,
            'analytics' => [
                'period_days' => $days,
                'basic_stats' => $basicStats,
                'transaction_by_type' => $typeStats,
                'daily_trends' => $dailyTrends,
                'active_users' => $activeUsers,
                'popular_items' => $popularItems
            ]
        ]);
    }

    /**
     * Get item transaction timeline
     */
    public function itemTimeline(Request $request)
    {
        $request->validate([
            'item_detail_id' => 'required|exists:item_details,item_detail_id'
        ]);

        try {
            $itemDetail = ItemDetail::with('item')->findOrFail($request->item_detail_id);

            // Get transaction history for this specific item
            $timeline = TransactionDetail::where('item_detail_id', $request->item_detail_id)
                ->with(['transaction.createdBy', 'transaction.approvedBy'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($detail) {
                    return $detail->getTimelineEntry();
                });

            return response()->json([
                'success' => true,
                'item_info' => [
                    'serial_number' => $itemDetail->serial_number,
                    'item_name' => $itemDetail->item->item_name,
                    'item_code' => $itemDetail->item->item_code,
                    'current_status' => $itemDetail->status,
                    'location' => $itemDetail->location
                ],
                'timeline' => $timeline
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get item timeline'
            ], 500);
        }
    }

    /**
     * Generate transaction report
     */
    public function report(Request $request)
    {
        $request->validate([
            'format' => 'required|in:json,csv',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'type' => 'nullable|in:' . implode(',', array_keys(Transaction::getTransactionTypes())),
            'status' => 'nullable|in:' . implode(',', array_keys(Transaction::getStatuses())),
            'user_id' => 'nullable|exists:users,user_id'
        ]);

        try {
            $query = Transaction::with([
                'item.category',
                'createdBy',
                'approvedBy',
                'transactionDetails.itemDetail'
            ]);

            // Role-based filtering
            $user = auth()->user();
            $levelName = strtolower($user->getUserLevel()->level_name ?? '');

            if ($levelName === 'teknisi') {
                $query->where('created_by', $user->user_id);
            }

            // Apply filters
            $query->whereBetween('transaction_date', [$request->date_from, $request->date_to]);

            if ($request->type) {
                $query->where('transaction_type', $request->type);
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->user_id && $levelName !== 'teknisi') {
                $query->where('created_by', $request->user_id);
            }

            $transactions = $query->orderBy('transaction_date', 'desc')->get();

            // Format data for report
            $reportData = $transactions->map(function($transaction) {
                return [
                    'transaction_number' => $transaction->transaction_number,
                    'transaction_date' => $transaction->transaction_date->format('Y-m-d H:i:s'),
                    'type' => $transaction->getTypeInfo()['text'],
                    'status' => $transaction->getStatusInfo()['text'],
                    'item_code' => $transaction->item->item_code ?? 'N/A',
                    'item_name' => $transaction->item->item_name ?? 'Unknown',
                    'category' => $transaction->item->category->category_name ?? 'N/A',
                    'serial_numbers' => $transaction->transactionDetails->map(function($detail) {
                        return $detail->itemDetail->serial_number ?? 'N/A';
                    })->join(', '),
                    'from_location' => $transaction->from_location,
                    'to_location' => $transaction->to_location,
                    'notes' => $transaction->notes,
                    'created_by' => $transaction->createdBy->full_name ?? 'Unknown',
                    'approved_by' => $transaction->approvedBy->full_name ?? '',
                    'approved_date' => $transaction->approved_date?->format('Y-m-d H:i:s') ?? '',
                    'status_changes' => $transaction->transactionDetails->map(function($detail) {
                        return $detail->getChangeSummary();
                    })->join('; ')
                ];
            });

            // Generate summary
            $summary = [
                'total_transactions' => $transactions->count(),
                'date_range' => $request->date_from . ' to ' . $request->date_to,
                'by_type' => $transactions->groupBy('transaction_type')->map->count(),
                'by_status' => $transactions->groupBy('status')->map->count(),
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'generated_by' => auth()->user()->full_name
            ];

            if ($request->format === 'json') {
                return response()->json([
                    'success' => true,
                    'report' => [
                        'summary' => $summary,
                        'data' => $reportData
                    ]
                ]);
            } else {
                // CSV format
                $filename = 'transaction_report_' . $request->date_from . '_to_' . $request->date_to . '.csv';

                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];

                $callback = function() use ($reportData, $summary) {
                    $file = fopen('php://output', 'w');

                    // Write summary
                    fputcsv($file, ['Transaction Report Summary']);
                    fputcsv($file, ['Total Transactions', $summary['total_transactions']]);
                    fputcsv($file, ['Date Range', $summary['date_range']]);
                    fputcsv($file, ['Generated At', $summary['generated_at']]);
                    fputcsv($file, ['Generated By', $summary['generated_by']]);
                    fputcsv($file, []); // Empty row

                    // Write headers
                    fputcsv($file, [
                        'Transaction Number',
                        'Date',
                        'Type',
                        'Status',
                        'Item Code',
                        'Item Name',
                        'Category',
                        'Serial Numbers',
                        'From Location',
                        'To Location',
                        'Notes',
                        'Created By',
                        'Approved By',
                        'Approved Date',
                        'Status Changes'
                    ]);

                    // Write data
                    foreach ($reportData as $row) {
                        fputcsv($file, $row);
                    }

                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get critical transaction alerts
     */
    public function alerts()
    {
        $alerts = [];

        // Long pending transactions (>24 hours)
        $longPending = Transaction::pending()
            ->where('transaction_date', '<=', now()->subHours(24))
            ->with(['item', 'createdBy'])
            ->get();

        if ($longPending->count() > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Long Pending Transactions',
                'message' => "{$longPending->count()} transactions pending for more than 24 hours",
                'count' => $longPending->count(),
                'items' => $longPending->map(function($transaction) {
                    return [
                        'id' => $transaction->transaction_id,
                        'number' => $transaction->transaction_number,
                        'item_name' => $transaction->item->item_name ?? 'Unknown',
                        'created_by' => $transaction->createdBy->full_name ?? 'Unknown',
                        'hours_pending' => $transaction->transaction_date->diffInHours(now())
                    ];
                })->toArray()
            ];
        }

        // Critical status changes (lost/damaged items)
        $criticalChanges = TransactionDetail::getCriticalChangesReport(7);

        if ($criticalChanges['total_critical_changes'] > 0) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Critical Status Changes',
                'message' => "{$criticalChanges['total_critical_changes']} items lost/damaged in last 7 days",
                'count' => $criticalChanges['total_critical_changes'],
                'breakdown' => [
                    'lost_items' => $criticalChanges['lost_items'],
                    'damaged_items' => $criticalChanges['damaged_items']
                ]
            ];
        }

        // High transaction volume users (potential abuse detection)
        $highVolumeUsers = Transaction::where('transaction_date', '>=', now()->subDays(7))
            ->groupBy('created_by')
            ->havingRaw('COUNT(*) > 20') // More than 20 transactions in a week
            ->with('createdBy')
            ->selectRaw('created_by, COUNT(*) as transaction_count')
            ->get();

        if ($highVolumeUsers->count() > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'High Transaction Volume',
                'message' => "{$highVolumeUsers->count()} users with unusually high transaction volume",
                'count' => $highVolumeUsers->count(),
                'users' => $highVolumeUsers->map(function($item) {
                    return [
                        'user_name' => $item->createdBy->full_name ?? 'Unknown',
                        'transaction_count' => $item->transaction_count
                    ];
                })->toArray()
            ];
        }

        return response()->json([
            'success' => true,
            'alerts' => $alerts,
            'alert_count' => count($alerts)
        ]);
    }

    /**
     * Get stock movement analysis
     */
    public function stockMovement(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        // Get stock changes from approved transactions
        $stockMovements = TransactionDetail::whereHas('transaction', function($query) use ($startDate) {
                $query->where('status', Transaction::STATUS_APPROVED)
                      ->where('approved_date', '>=', $startDate);
            })
            ->with(['transaction', 'itemDetail.item'])
            ->get()
            ->map(function($detail) {
                $impact = $detail->getStockImpact();
                return [
                    'date' => $detail->transaction->approved_date->format('Y-m-d'),
                    'item_name' => $detail->itemDetail->item->item_name ?? 'Unknown',
                    'item_code' => $detail->itemDetail->item->item_code ?? 'N/A',
                    'transaction_type' => $detail->transaction->transaction_type,
                    'status_change' => $detail->status_before . ' → ' . $detail->status_after,
                    'stock_impact' => $impact,
                    'transaction_number' => $detail->transaction->transaction_number
                ];
            })
            ->groupBy('date');

        // Calculate daily stock changes
        $dailyChanges = [];
        foreach ($stockMovements as $date => $movements) {
            $dailyChanges[$date] = [
                'date' => $date,
                'total_movements' => $movements->count(),
                'available_change' => $movements->sum('stock_impact.available_change'),
                'used_change' => $movements->sum('stock_impact.used_change'),
                'total_change' => $movements->sum('stock_impact.total_change'),
                'movements' => $movements->toArray()
            ];
        }

        return response()->json([
            'success' => true,
            'stock_movement' => [
                'period_days' => $days,
                'daily_changes' => $dailyChanges,
                'summary' => [
                    'total_movements' => collect($dailyChanges)->sum('total_movements'),
                    'net_available_change' => collect($dailyChanges)->sum('available_change'),
                    'net_used_change' => collect($dailyChanges)->sum('used_change'),
                    'net_total_change' => collect($dailyChanges)->sum('total_change')
                ]
            ]
        ]);
    }

    /**
     * Get history statistics
     */
    private function getHistoryStats(Request $request)
    {
        $query = Transaction::query();

        // Role-based filtering
        $user = auth()->user();
        $levelName = strtolower($user->getUserLevel()->level_name ?? '');

        if ($levelName === 'teknisi') {
            $query->where('created_by', $user->user_id);
        }

        // Apply same filters as main query
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        return [
            'total_transactions' => $query->count(),
            'pending_count' => (clone $query)->where('status', Transaction::STATUS_PENDING)->count(),
            'approved_count' => (clone $query)->where('status', Transaction::STATUS_APPROVED)->count(),
            'rejected_count' => (clone $query)->where('status', Transaction::STATUS_REJECTED)->count(),
            'by_type' => (clone $query)->groupBy('transaction_type')
                ->selectRaw('transaction_type, COUNT(*) as count')
                ->pluck('count', 'transaction_type')
                ->toArray()
        ];
    }
}
