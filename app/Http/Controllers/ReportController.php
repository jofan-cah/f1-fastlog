<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Item;
use App\Models\ItemDetail;
use App\Models\Stock;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionReportExport;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Display main report dashboard
     */
    public function dashboard(Request $request)
    {
        // Check permission
        // if (!auth()->user()->hasPermission('reports', 'read')) {
        //     abort(403, 'Access denied');
        // }

        // Get filter parameters
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $transactionType = $request->get('transaction_type');
        $userLevel = $request->get('user_level');

        // Get comprehensive stats
        $stats = $this->getComprehensiveStats($dateFrom, $dateTo, $transactionType, $userLevel);

        // Get chart data
        $chartData = $this->getChartData($dateFrom, $dateTo, $transactionType);

        // Get recent activities
        $recentActivities = $this->getRecentActivities(10);

        // Get performance metrics
        $performanceMetrics = $this->getPerformanceMetrics($dateFrom, $dateTo);

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'stats' => $stats,
                'chartData' => $chartData,
                'performanceMetrics' => $performanceMetrics
            ]);
        }

        // Get filter options
        $transactionTypes = Transaction::getTransactionTypes();
        $userLevels = [
            'admin' => 'Admin',
            'logistik' => 'Logistik',
            'teknisi' => 'Teknisi'
        ];

        return view('reports.dashboard', compact(
            'stats',
            'chartData',
            'recentActivities',
            'performanceMetrics',
            'transactionTypes',
            'userLevels',
            'dateFrom',
            'dateTo',
            'transactionType',
            'userLevel'
        ));
    }

    /**
     * Get comprehensive statistics
     */
    private function getComprehensiveStats($dateFrom, $dateTo, $transactionType = null, $userLevel = null)
    {
        $query = Transaction::whereBetween('transaction_date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        // Apply filters
        if ($transactionType) {
            $query->where('transaction_type', $transactionType);
        }

        if ($userLevel) {
            $query->whereHas('createdBy.userLevel', function($q) use ($userLevel) {
                $q->where('level_name', ucfirst($userLevel));
            });
        }

        // Basic stats
        $totalTransactions = $query->count();
        $pendingCount = (clone $query)->where('status', 'pending')->count();
        $approvedCount = (clone $query)->where('status', 'approved')->count();
        $rejectedCount = (clone $query)->where('status', 'rejected')->count();

        // Calculate rates
        $approvalRate = $totalTransactions > 0 ? round(($approvedCount / $totalTransactions) * 100, 1) : 0;
        $rejectionRate = $totalTransactions > 0 ? round(($rejectedCount / $totalTransactions) * 100, 1) : 0;

        // Transaction type breakdown
        $typeBreakdown = (clone $query)->groupBy('transaction_type')
            ->selectRaw('transaction_type, COUNT(*) as count')
            ->pluck('count', 'transaction_type')
            ->toArray();

        // Status breakdown
        $statusBreakdown = (clone $query)->groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->pluck('count', 'status')
            ->toArray();

        // Damage analysis (untuk DAMAGED transactions)
        $damageStats = null;
        if (!$transactionType || $transactionType === 'DAMAGED') {
            $damageQuery = Transaction::where('transaction_type', 'DAMAGED')
                ->whereBetween('transaction_date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

            $damageStats = [
                'total_damaged' => $damageQuery->count(),
                'by_level' => (clone $damageQuery)->groupBy('damage_level')
                    ->selectRaw('damage_level, COUNT(*) as count')
                    ->pluck('count', 'damage_level')
                    ->toArray(),
                'by_reason' => (clone $damageQuery)->groupBy('damage_reason')
                    ->selectRaw('damage_reason, COUNT(*) as count, SUM(repair_estimate) as total_estimate')
                    ->get()
                    ->mapWithKeys(function($item) {
                        return [$item->damage_reason => [
                            'count' => $item->count,
                            'total_estimate' => $item->total_estimate ?? 0
                        ]];
                    })
                    ->toArray(),
                'total_repair_estimate' => (clone $damageQuery)->sum('repair_estimate') ?? 0
            ];
        }

        // User performance
        $userPerformance = (clone $query)->join('users', 'transactions.created_by', '=', 'users.user_id')
            ->join('user_levels', 'users.user_level_id', '=', 'user_levels.user_level_id')
            ->groupBy(['users.user_id', 'users.full_name', 'user_levels.level_name'])
            ->selectRaw('
                users.user_id,
                users.full_name,
                user_levels.level_name,
                COUNT(*) as total_transactions,
                SUM(CASE WHEN transactions.status = "approved" THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN transactions.status = "rejected" THEN 1 ELSE 0 END) as rejected_count
            ')
            ->orderBy('total_transactions', 'desc')
            ->limit(10)
            ->get()
            ->map(function($user) {
                $user->success_rate = $user->total_transactions > 0
                    ? round(($user->approved_count / $user->total_transactions) * 100, 1)
                    : 0;
                return $user;
            });

        return [
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
                'days' => Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1
            ],
            'totals' => [
                'transactions' => $totalTransactions,
                'pending' => $pendingCount,
                'approved' => $approvedCount,
                'rejected' => $rejectedCount,
                'approval_rate' => $approvalRate,
                'rejection_rate' => $rejectionRate
            ],
            'breakdowns' => [
                'by_type' => $typeBreakdown,
                'by_status' => $statusBreakdown
            ],
            'damage_analysis' => $damageStats,
            'user_performance' => $userPerformance,
            'filters_applied' => [
                'transaction_type' => $transactionType,
                'user_level' => $userLevel
            ]
        ];
    }

    /**
     * Get chart data for visualizations
     */
    private function getChartData($dateFrom, $dateTo, $transactionType = null)
    {
        $query = Transaction::whereBetween('transaction_date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($transactionType) {
            $query->where('transaction_type', $transactionType);
        }

        // 1. Monthly trends
        $monthlyTrends = (clone $query)->selectRaw('
                DATE_FORMAT(transaction_date, "%Y-%m") as month,
                COUNT(*) as total,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // 2. Daily activity (last 30 days)
        $dailyActivity = Transaction::whereBetween('transaction_date', [
                now()->subDays(30)->format('Y-m-d 00:00:00'),
                now()->format('Y-m-d 23:59:59')
            ])
            ->selectRaw('
                DATE(transaction_date) as date,
                COUNT(*) as count
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill missing dates
        $dailyData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyData[] = [
                'date' => $date,
                'count' => $dailyActivity->get($date)->count ?? 0
            ];
        }

        // 3. Transaction type distribution
        $typeDistribution = (clone $query)->groupBy('transaction_type')
            ->selectRaw('transaction_type, COUNT(*) as count')
            ->get()
            ->map(function($item) {
                $typeInfo = Transaction::getTransactionTypes();
                return [
                    'type' => $item->transaction_type,
                    'label' => $typeInfo[$item->transaction_type] ?? $item->transaction_type,
                    'count' => $item->count
                ];
            });

        // 4. Status distribution
        $statusDistribution = (clone $query)->groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->get()
            ->map(function($item) {
                $statusInfo = Transaction::getStatuses();
                return [
                    'status' => $item->status,
                    'label' => $statusInfo[$item->status] ?? ucfirst($item->status),
                    'count' => $item->count
                ];
            });

        // 5. Damage level analysis (if applicable)
        $damageLevelData = [];
        if (!$transactionType || $transactionType === 'DAMAGED') {
            $damageLevelData = Transaction::where('transaction_type', 'DAMAGED')
                ->whereBetween('transaction_date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->groupBy('damage_level')
                ->selectRaw('
                    damage_level,
                    COUNT(*) as count,
                    AVG(repair_estimate) as avg_estimate,
                    SUM(repair_estimate) as total_estimate
                ')
                ->get()
                ->map(function($item) {
                    $levelInfo = Transaction::getDamageLevels();
                    return [
                        'level' => $item->damage_level,
                        'label' => $levelInfo[$item->damage_level] ?? ucfirst($item->damage_level),
                        'count' => $item->count,
                        'avg_estimate' => round($item->avg_estimate ?? 0, 2),
                        'total_estimate' => $item->total_estimate ?? 0
                    ];
                });
        }

        // 6. Hourly pattern (for operational insights)
        $hourlyPattern = (clone $query)->selectRaw('
                HOUR(transaction_date) as hour,
                COUNT(*) as count
            ')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $hourlyData = [];
        for ($h = 0; $h < 24; $h++) {
            $hourlyData[] = [
                'hour' => sprintf('%02d:00', $h),
                'count' => $hourlyPattern->get($h)->count ?? 0
            ];
        }

        return [
            'monthly_trends' => $monthlyTrends,
            'daily_activity' => $dailyData,
            'type_distribution' => $typeDistribution,
            'status_distribution' => $statusDistribution,
            'damage_levels' => $damageLevelData,
            'hourly_pattern' => $hourlyData
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($limit = 10)
    {
        return ActivityLog::with(['user'])
            ->where('table_name', 'transactions')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->activity_log_id,
                    'user_name' => $log->user->full_name ?? 'System',
                    'action' => $log->action,
                    'description' => $this->formatLogDescription($log),
                    'created_at' => $log->created_at,
                    'created_at_human' => $log->created_at->diffForHumans()
                ];
            });
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics($dateFrom, $dateTo)
    {
        // Average processing time (created to approved)
        $avgProcessingTime = Transaction::where('status', 'approved')
            ->whereBetween('transaction_date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->whereNotNull('approved_date')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, transaction_date, approved_date)) as avg_hours')
            ->value('avg_hours');

        // Items most frequently involved in transactions
        $frequentItems = TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.transaction_id')
            ->join('item_details', 'transaction_details.item_detail_id', '=', 'item_details.item_detail_id')
            ->join('items', 'item_details.item_id', '=', 'items.item_id')
            ->whereBetween('transactions.transaction_date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->groupBy(['items.item_id', 'items.item_name', 'items.item_code'])
            ->selectRaw('
                items.item_id,
                items.item_name,
                items.item_code,
                COUNT(*) as transaction_count
            ')
            ->orderBy('transaction_count', 'desc')
            ->limit(10)
            ->get();

        // Peak hours analysis
        $peakHours = Transaction::whereBetween('transaction_date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->selectRaw('HOUR(transaction_date) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->limit(3)
            ->get();

        return [
            'avg_processing_time' => round($avgProcessingTime ?? 0, 1),
            'frequent_items' => $frequentItems,
            'peak_hours' => $peakHours->map(function($item) {
                return [
                    'hour' => sprintf('%02d:00', $item->hour),
                    'count' => $item->count
                ];
            })
        ];
    }

    /**
     * Export to Excel
     */
    public function exportExcel(Request $request)
    {
        // Check permission
        // if (!auth()->user()->hasPermission('reports', 'export')) {
        //     abort(403, 'Access denied');
        // }

        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'transaction_type' => 'nullable|string',
            'format' => 'nullable|in:summary,detailed,damage_analysis'
        ]);

        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $transactionType = $request->transaction_type;
        $format = $request->format ?? 'summary';

        $filename = 'transaction-report-' . $format . '-' . $dateFrom . '-to-' . $dateTo . '.xlsx';

        return Excel::download(
            new TransactionReportExport($dateFrom, $dateTo, $transactionType, $format),
            $filename
        );
    }

    /**
     * Export to PDF
     */
    public function exportPdf(Request $request)
    {
        // Check permission
        if (!auth()->user()->hasPermission('reports', 'export')) {
            abort(403, 'Access denied');
        }

        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'transaction_type' => 'nullable|string',
            'include_charts' => 'boolean'
        ]);

        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $transactionType = $request->transaction_type;
        $includeCharts = $request->boolean('include_charts', true);

        // Get data for PDF
        $stats = $this->getComprehensiveStats($dateFrom, $dateTo, $transactionType);
        $chartData = $includeCharts ? $this->getChartData($dateFrom, $dateTo, $transactionType) : null;

        // Generate charts as base64 images if needed
        $chartImages = [];
        if ($includeCharts) {
            // This would require a chart generation service
            // For now, we'll skip the chart images
        }

        $pdf = Pdf::loadView('reports.pdf.transaction-report', compact(
            'stats',
            'chartData',
            'chartImages',
            'dateFrom',
            'dateTo',
            'transactionType'
        ));

        $filename = 'transaction-report-' . $dateFrom . '-to-' . $dateTo . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Get API data for charts (AJAX endpoint)
     */
    public function getApiData(Request $request)
    {
        $request->validate([
            'chart_type' => 'required|in:monthly_trends,daily_activity,type_distribution,status_distribution,damage_levels,hourly_pattern',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'transaction_type' => 'nullable|string'
        ]);

        $chartData = $this->getChartData(
            $request->date_from,
            $request->date_to,
            $request->transaction_type
        );

        return response()->json([
            'success' => true,
            'data' => $chartData[$request->chart_type] ?? []
        ]);
    }

    /**
     * Get damage analysis detail
     */
    public function getDamageAnalysis(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'damage_level' => 'nullable|string'
        ]);

        $query = Transaction::where('transaction_type', 'DAMAGED')
            ->whereBetween('transaction_date', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59'
            ]);

        if ($request->damage_level) {
            $query->where('damage_level', $request->damage_level);
        }

        $damages = $query->with(['item', 'createdBy', 'transactionDetails.itemDetail'])
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);

        $summary = [
            'total_items' => $query->count(),
            'total_estimate' => $query->sum('repair_estimate'),
            'avg_estimate' => $query->avg('repair_estimate'),
            'by_reason' => $query->groupBy('damage_reason')
                ->selectRaw('damage_reason, COUNT(*) as count, SUM(repair_estimate) as total')
                ->get()
        ];

        return response()->json([
            'success' => true,
            'damages' => $damages,
            'summary' => $summary
        ]);
    }

    /**
     * Helper method to format log descriptions
     */
    private function formatLogDescription($log)
    {
        $action = $log->action;
        $tableName = $log->table_name;

        $descriptions = [
            'create_transaction' => 'Membuat transaksi baru',
            'update' => 'Mengupdate transaksi',
            'approve' => 'Menyetujui transaksi',
            'reject' => 'Menolak transaksi',
            'cancel' => 'Membatalkan transaksi'
        ];

        return $descriptions[$action] ?? ucfirst(str_replace('_', ' ', $action));
    }

    /**
     * Get real-time stats for dashboard widgets
     */
    public function getRealTimeStats()
    {
        $today = now()->format('Y-m-d');

        return response()->json([
            'success' => true,
            'stats' => [
                'today_transactions' => Transaction::whereDate('transaction_date', $today)->count(),
                'pending_approvals' => Transaction::where('status', 'pending')->count(),
                'today_approvals' => Transaction::where('status', 'approved')
                    ->whereDate('approved_date', $today)->count(),
                'damaged_items_this_week' => Transaction::where('transaction_type', 'DAMAGED')
                    ->whereBetween('transaction_date', [
                        now()->startOfWeek()->format('Y-m-d 00:00:00'),
                        now()->endOfWeek()->format('Y-m-d 23:59:59')
                    ])->count()
            ],
            'timestamp' => now()->toISOString()
        ]);
    }
}

