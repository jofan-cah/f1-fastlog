<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\ActivityLog;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalController extends Controller
{
    public function __construct()
    {
        // Only admin and logistik can access approval functions
        // $this->middleware(function ($request, $next) {
        //     if (!Transaction::canUserApprove()) {
        //         abort(403, 'Unauthorized access to approval functions');
        //     }
        //     return $next($request);
        // });
    }

    /**
     * Display pending approvals
     */
    public function index(Request $request)
    {
        $query = Transaction::pending()
            ->with(['item', 'createdBy', 'transactionDetails.itemDetail']);

        // Apply filters
        if ($request->has('type') && $request->type !== '') {
            $query->where('transaction_type', $request->type);
        }

        if ($request->has('created_by') && $request->created_by !== '') {
            $query->where('created_by', $request->created_by);
        }

        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $pendingTransactions = $query->orderBy('transaction_date', 'asc')
            ->paginate(20);

        // Get summary stats
        $stats = [
            'total_pending' => Transaction::pending()->count(),
            'urgent_pending' => Transaction::pending()
                ->where('transaction_date', '<=', now()->subHours(24))
                ->count(),
            'by_type' => Transaction::pending()
                ->groupBy('transaction_type')
                ->selectRaw('transaction_type, COUNT(*) as count')
                ->pluck('count', 'transaction_type')
                ->toArray(),
        ];

        $transactionTypes = Transaction::getTransactionTypes();
        $users = \App\Models\User::where('is_active', true)
            ->orderBy('full_name')
            ->get(['user_id', 'full_name']);

        return view('approvals.index', compact(
            'pendingTransactions',
            'stats',
            'transactionTypes',
            'users'
        ));
    }

    /**
     * Show transaction detail for approval
     */
    public function show(Transaction $transaction)
    {
        if ($transaction->status !== Transaction::STATUS_PENDING) {
            return redirect()->route('approvals.index')
                ->with('error', 'Transaction is not pending approval');
        }

        $transaction->load([
            'item.stock',
            'createdBy',
            'transactionDetails.itemDetail'
        ]);

        // Get item history for context
        $itemHistory = [];
        foreach ($transaction->transactionDetails as $detail) {
            $itemHistory[] = $detail->itemDetail->getTransactionHistory();
        }

        return view('approvals.show', compact('transaction', 'itemHistory'));
    }

    /**
     * Approve single transaction
     */
    // Single method untuk approval (works for both single and multi)
    public function approve($transactionId, Request $request)
    {
        try {
            $transaction = Transaction::where('transaction_id', $transactionId)->first();

            if (!$transaction) {
                throw new \Exception('Transaction not found');
            }

            if (!$transaction->canBeApproved()) {
                throw new \Exception('Transaction cannot be approved');
            }

            DB::beginTransaction();

            // Approve main transaction
            $transaction->status = Transaction::STATUS_APPROVED;
            $transaction->approved_by = auth()->id();
            $transaction->approved_date = now();

            // Add approval notes if provided
            if ($request->approval_notes) {
                $transaction->notes = $transaction->notes . "\n\nApproval Notes: " . $request->approval_notes;
            }

            $transaction->save();

            // Execute all transaction details (same logic for single or multi)
            $itemsProcessed = 0;
            foreach ($transaction->transactionDetails as $detail) {
                $itemDetail = $detail->itemDetail;
                $oldStatus = $itemDetail->status;
                $newStatus = $this->getNewStatusForTransaction($transaction->transaction_type, $oldStatus);

                // Update item detail status
                $itemDetail->status = $newStatus;
                $itemDetail->save();

                // Update transaction detail
                $detail->status_after = $newStatus;
                $detail->save();

                // Update stock if needed
                $this->updateStockForStatusChange($itemDetail->item_id, $oldStatus, $newStatus);

                $itemsProcessed++;
            }

            DB::commit();

            // Log approval
            ActivityLog::logActivity(
                'transactions',
                $transaction->transaction_id,
                'approve',
                ['status' => Transaction::STATUS_PENDING],
                [
                    'status' => Transaction::STATUS_APPROVED,
                    'approved_by' => auth()->id(),
                    'items_processed' => $itemsProcessed
                ]
            );

            return response()->json([
                'success' => true,
                'message' => $itemsProcessed > 1
                    ? "Multi-item transaction approved ({$itemsProcessed} items processed)"
                    : "Transaction approved successfully",
                'items_processed' => $itemsProcessed,
                'transaction' => [
                    'transaction_id' => $transaction->transaction_id,
                    'status' => $transaction->status,
                    'approved_date' => $transaction->approved_date
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }


    private function updateStockForStatusChange($itemId, $oldStatus, $newStatus)
    {
        $stock = Stock::where('item_id', $itemId)->first();
        if (!$stock) return;

        $availableChange = 0;
        $usedChange = 0;
        $totalChange = 0;

        // Calculate stock changes
        if ($oldStatus === 'available') {
            if (in_array($newStatus, ['used', 'repair'])) {
                $availableChange = -1;
                $usedChange = 1;
            } elseif ($newStatus === 'lost') {
                $availableChange = -1;
                $totalChange = -1;
            }
        } elseif ($newStatus === 'available') {
            if (in_array($oldStatus, ['used', 'repair'])) {
                $availableChange = 1;
                $usedChange = -1;
            }
        }

        // Apply changes
        if ($availableChange !== 0 || $usedChange !== 0 || $totalChange !== 0) {
            $stock->quantity_available += $availableChange;
            $stock->quantity_used += $usedChange;
            $stock->total_quantity += $totalChange;
            $stock->last_updated = now();
            $stock->save();
        }
    }

    // Helper methods (same as before)
    private function getNewStatusForTransaction($transactionType, $currentStatus)
    {
        switch ($transactionType) {
            case 'OUT':
                return 'used';
            case 'IN':
            case 'RETURN':
                return 'available';
            case 'REPAIR':
                return 'repair';
            case 'LOST':
                return 'lost';
            default:
                return $currentStatus;
        }
    }


    /**
     * Reject single transaction
     */
    public function reject(Request $request, Transaction $transaction)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        if ($transaction->status !== Transaction::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction is not pending approval'
            ], 400);
        }

        try {
            $success = $transaction->reject($request->reason);

            if (!$success) {
                throw new \Exception('Failed to reject transaction');
            }

            $message = "Transaksi {$transaction->transaction_number} berhasil di-reject";

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'transaction' => [
                        'id' => $transaction->transaction_id,
                        'status' => $transaction->getStatusInfo(),
                        'approved_date' => $transaction->approved_date?->format('Y-m-d H:i:s'),
                        'approved_by' => auth()->user()->full_name
                    ]
                ]);
            }

            return redirect()->route('approvals.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to reject transaction: ' . $e->getMessage());

            $errorMessage = 'Gagal reject transaksi: ' . $e->getMessage();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

            return redirect()->back()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Bulk approve transactions
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'transaction_ids' => 'required|array|min:1',
            'transaction_ids.*' => 'required|exists:transactions,transaction_id',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($request->transaction_ids as $transactionId) {
                try {
                    $transaction = Transaction::find($transactionId);

                    if (!$transaction || $transaction->status !== Transaction::STATUS_PENDING) {
                        $errors[] = "Transaction {$transactionId} is not pending";
                        $errorCount++;
                        continue;
                    }

                    $success = $transaction->approve($request->notes);

                    if ($success) {
                        $successCount++;
                    } else {
                        $errors[] = "Failed to approve transaction {$transaction->transaction_number}";
                        $errorCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error approving {$transactionId}: " . $e->getMessage();
                    $errorCount++;
                }
            }

            DB::commit();

            $message = "Bulk approval completed: {$successCount} approved";
            if ($errorCount > 0) {
                $message .= ", {$errorCount} failed";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'details' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk approve failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Bulk approval failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk reject transactions
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'transaction_ids' => 'required|array|min:1',
            'transaction_ids.*' => 'required|exists:transactions,transaction_id',
            'reason' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($request->transaction_ids as $transactionId) {
                try {
                    $transaction = Transaction::find($transactionId);

                    if (!$transaction || $transaction->status !== Transaction::STATUS_PENDING) {
                        $errors[] = "Transaction {$transactionId} is not pending";
                        $errorCount++;
                        continue;
                    }

                    $success = $transaction->reject($request->reason);

                    if ($success) {
                        $successCount++;
                    } else {
                        $errors[] = "Failed to reject transaction {$transaction->transaction_number}";
                        $errorCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error rejecting {$transactionId}: " . $e->getMessage();
                    $errorCount++;
                }
            }

            DB::commit();

            $message = "Bulk rejection completed: {$successCount} rejected";
            if ($errorCount > 0) {
                $message .= ", {$errorCount} failed";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'details' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk reject failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Bulk rejection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get approval summary for dashboard
     */
    public function getSummary()
    {
        $summary = [
            'pending_count' => Transaction::pending()->count(),
            'approved_today' => Transaction::approved()
                ->whereDate('approved_date', today())
                ->where('approved_by', auth()->id())
                ->count(),
            'urgent_count' => Transaction::pending()
                ->where('transaction_date', '<=', now()->subHours(24))
                ->count(),
            'recent_approvals' => Transaction::approved()
                ->where('approved_by', auth()->id())
                ->with(['item', 'createdBy'])
                ->orderBy('approved_date', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => $transaction->transaction_id,
                        'number' => $transaction->transaction_number,
                        'type' => $transaction->getTypeInfo(),
                        'item_name' => $transaction->item->item_name ?? '',
                        'created_by' => $transaction->createdBy->full_name ?? '',
                        'approved_date' => $transaction->approved_date?->format('Y-m-d H:i:s'),
                    ];
                })
        ];

        return response()->json([
            'success' => true,
            'summary' => $summary
        ]);
    }

    /**
     * Get transaction details for approval modal (AJAX)
     */
    public function getTransactionDetails(Transaction $transaction)
    {
        if ($transaction->status !== Transaction::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction is not pending approval'
            ], 400);
        }

        $transaction->load([
            'item.stock',
            'createdBy',
            'transactionDetails.itemDetail'
        ]);

        // Get impact analysis
        $impactAnalysis = [];
        foreach ($transaction->transactionDetails as $detail) {
            $impactAnalysis[] = [
                'item_detail' => [
                    'serial_number' => $detail->itemDetail->serial_number,
                    'current_status' => $detail->itemDetail->status,
                    'status_info' => $detail->itemDetail->getStatusInfo()
                ],
                'expected_change' => [
                    'from' => $detail->itemDetail->status,
                    'to' => $detail->getExpectedStatusAfter(),
                ],
                'stock_impact' => $detail->getStockImpact()
            ];
        }

        return response()->json([
            'success' => true,
            'transaction' => [
                'id' => $transaction->transaction_id,
                'number' => $transaction->transaction_number,
                'type' => $transaction->getTypeInfo(),
                'status' => $transaction->getStatusInfo(),
                'item_name' => $transaction->item->item_name ?? '',
                'item_code' => $transaction->item->item_code ?? '',
                'current_stock' => [
                    'available' => $transaction->item->stock->quantity_available ?? 0,
                    'used' => $transaction->item->stock->quantity_used ?? 0,
                    'total' => $transaction->item->stock->total_quantity ?? 0,
                ],
                'notes' => $transaction->notes,
                'from_location' => $transaction->from_location,
                'to_location' => $transaction->to_location,
                'created_by' => $transaction->createdBy->full_name ?? 'Unknown',
                'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                'transaction_date' => $transaction->transaction_date->format('Y-m-d H:i:s'),
            ],
            'impact_analysis' => $impactAnalysis
        ]);
    }

    /**
     * Quick approve transaction (single click approval)
     */
    public function quickApprove(Transaction $transaction)
    {
        if ($transaction->status !== Transaction::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction is not pending approval'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $success = $transaction->approve("Quick approval by " . auth()->user()->full_name);

            if (!$success) {
                throw new \Exception('Failed to approve transaction');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Transaction {$transaction->transaction_number} approved",
                'transaction' => [
                    'id' => $transaction->transaction_id,
                    'status' => $transaction->getStatusInfo(),
                    'approved_date' => $transaction->approved_date?->format('Y-m-d H:i:s'),
                    'approved_by' => auth()->user()->full_name
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quick approve failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Quick approval failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get approval history for specific user
     */
    public function history(Request $request)
    {
        $query = Transaction::whereIn('status', [
            Transaction::STATUS_APPROVED,
            Transaction::STATUS_REJECTED
        ])
            ->where('approved_by', auth()->id())
            ->with(['item', 'createdBy']);

        // Apply filters
        if ($request->has('type') && $request->type !== '') {
            $query->where('transaction_type', $request->type);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('approved_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('approved_date', '<=', $request->date_to);
        }

        $approvalHistory = $query->orderBy('approved_date', 'desc')
            ->paginate(20);

        $stats = [
            'total_approved' => Transaction::where('approved_by', auth()->id())
                ->where('status', Transaction::STATUS_APPROVED)
                ->count(),
            'total_rejected' => Transaction::where('approved_by', auth()->id())
                ->where('status', Transaction::STATUS_REJECTED)
                ->count(),
            'approved_today' => Transaction::where('approved_by', auth()->id())
                ->where('status', Transaction::STATUS_APPROVED)
                ->whereDate('approved_date', today())
                ->count(),
        ];

        $transactionTypes = Transaction::getTransactionTypes();
        $transactionStatuses = [
            Transaction::STATUS_APPROVED => 'Disetujui',
            Transaction::STATUS_REJECTED => 'Ditolak'
        ];

        return view('approvals.history', compact(
            'approvalHistory',
            'stats',
            'transactionTypes',
            'transactionStatuses'
        ));
    }

    /**
     * Get approval analytics
     */
    public function analytics(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        // Approval stats by approver
        $approvalStats = Transaction::whereIn('status', [
            Transaction::STATUS_APPROVED,
            Transaction::STATUS_REJECTED
        ])
            ->where('approved_date', '>=', $startDate)
            ->with('approvedBy')
            ->get()
            ->groupBy('approved_by')
            ->map(function ($transactions) {
                return [
                    'approver_name' => $transactions->first()->approvedBy->full_name ?? 'Unknown',
                    'total' => $transactions->count(),
                    'approved' => $transactions->where('status', Transaction::STATUS_APPROVED)->count(),
                    'rejected' => $transactions->where('status', Transaction::STATUS_REJECTED)->count(),
                    'approval_rate' => $transactions->count() > 0 ?
                        round(($transactions->where('status', Transaction::STATUS_APPROVED)->count() / $transactions->count()) * 100, 2) : 0
                ];
            });

        // Daily approval trends
        $dailyTrends = Transaction::whereIn('status', [
            Transaction::STATUS_APPROVED,
            Transaction::STATUS_REJECTED
        ])
            ->where('approved_date', '>=', $startDate)
            ->selectRaw('DATE(approved_date) as date, status, COUNT(*) as count')
            ->groupBy(['date', 'status'])
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        // Average approval time (approximation)
        $avgApprovalTime = Transaction::whereIn('status', [
            Transaction::STATUS_APPROVED,
            Transaction::STATUS_REJECTED
        ])
            ->where('approved_date', '>=', $startDate)
            ->whereNotNull('approved_date')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, transaction_date, approved_date)) as avg_hours')
            ->first();

        return response()->json([
            'success' => true,
            'analytics' => [
                'period_days' => $days,
                'approval_stats' => $approvalStats,
                'daily_trends' => $dailyTrends,
                'avg_approval_time_hours' => round($avgApprovalTime->avg_hours ?? 0, 2),
                'summary' => [
                    'total_processed' => Transaction::whereIn('status', [
                        Transaction::STATUS_APPROVED,
                        Transaction::STATUS_REJECTED
                    ])->where('approved_date', '>=', $startDate)->count(),
                    'approval_rate' => Transaction::whereIn('status', [
                        Transaction::STATUS_APPROVED,
                        Transaction::STATUS_REJECTED
                    ])->where('approved_date', '>=', $startDate)->count() > 0 ?
                        round((Transaction::where('status', Transaction::STATUS_APPROVED)
                            ->where('approved_date', '>=', $startDate)->count() /
                            Transaction::whereIn('status', [Transaction::STATUS_APPROVED, Transaction::STATUS_REJECTED])
                            ->where('approved_date', '>=', $startDate)->count()) * 100, 2) : 0
                ]
            ]
        ]);
    }
}
