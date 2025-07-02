<?php

// ================================================================
// 2. app/Http/Controllers/ActivityLogController.php
// ================================================================

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
        // Hanya admin yang bisa akses activity logs
        // $this->middleware('admin')->except(['myActivities']);
    }

    // Tampilkan daftar activity logs
    public function index(Request $request)
    {
        $query = ActivityLog::with('user.userLevel')
            ->orderBy('created_at', 'desc');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        // Filter by table
        if ($request->filled('table_name')) {
            $query->byTable($request->table_name);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->byAction($request->action);
        }

        // Filter by risk level
        if ($request->filled('risk_level')) {
            switch ($request->risk_level) {
                case 'high':
                    $query->highRisk();
                    break;
                case 'suspicious':
                    $query->suspicious();
                    break;
            }
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = $request->filled('end_date')
                ? Carbon::parse($request->end_date)->endOfDay()
                : now()->endOfDay();

            $query->dateRange($startDate, $endDate);
        }

        // Search by description/IP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('record_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('full_name', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        // Statistics
        $stats = ActivityLog::getActivityStats(7);

        // Filter options
        $users = User::active()->orderBy('full_name')->get();
        $tables = ActivityLog::distinct('table_name')->pluck('table_name');
        $actions = ActivityLog::distinct('action')->pluck('action');

        return view('activity-logs.index', compact(
            'logs', 'stats', 'users', 'tables', 'actions'
        ));
    }

    // Tampilkan detail activity log
    public function show(ActivityLog $activityLog)
    {
        $activityLog->load('user.userLevel');

        // Get related activities (same user, same time period)
        $relatedActivities = ActivityLog::byUser($activityLog->user_id)
            ->where('log_id', '!=', $activityLog->log_id)
            ->where('created_at', '>=', $activityLog->created_at->subMinutes(5))
            ->where('created_at', '<=', $activityLog->created_at->addMinutes(5))
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Change summary
        $changeSummary = $activityLog->getChangeSummary();

        // Risk assessment
        $riskLevel = $activityLog->getRiskLevel();
        $isSuspicious = $activityLog->isSuspicious();

        return view('activity-logs.show', compact(
            'activityLog', 'relatedActivities', 'changeSummary',
            'riskLevel', 'isSuspicious'
        ));
    }

    // User's own activities
    public function myActivities(Request $request)
    {
        $userId = Auth::id();

        $query = ActivityLog::byUser($userId)
            ->orderBy('created_at', 'desc');

        // Filter by date range
        if ($request->filled('start_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = $request->filled('end_date')
                ? Carbon::parse($request->end_date)->endOfDay()
                : now()->endOfDay();

            $query->dateRange($startDate, $endDate);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->byAction($request->action);
        }

        $logs = $query->paginate(20)->withQueryString();

        // User's statistics
        $userStats = [
            'total_activities' => ActivityLog::byUser($userId)->count(),
            'today_activities' => ActivityLog::byUser($userId)->whereDate('created_at', today())->count(),
            'this_week_activities' => ActivityLog::byUser($userId)->where('created_at', '>=', now()->startOfWeek())->count(),
            'last_login' => ActivityLog::byUser($userId)->where('action', 'login')->latest()->first()?->created_at,
        ];

        return view('activity-logs.my-activities', compact('logs', 'userStats'));
    }

    // Dashboard untuk monitoring
   public function dashboard(Request $request)
{
    $days = $request->get('days', 7);
    $startDate = now()->subDays($days);

    try {
        // Overall statistics
        $stats = ActivityLog::getActivityStats($days);

        // Activity trends (per day) - dengan error handling
        $activityTrends = collect();
        try {
            $activityTrends = ActivityLog::where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->date => $item->count];
                });
        } catch (\Exception $e) {
            \Log::error('Error getting activity trends: ' . $e->getMessage());
            $activityTrends = collect();
        }

        // Top active users - dengan error handling
        $topUsers = collect();
        try {
            $topUsers = ActivityLog::where('created_at', '>=', $startDate)
                ->with('user.userLevel')
                ->selectRaw('user_id, COUNT(*) as activity_count')
                ->groupBy('user_id')
                ->orderBy('activity_count', 'desc')
                ->take(10)
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error getting top users: ' . $e->getMessage());
            $topUsers = collect();
        }

        // Most performed actions - dengan error handling
        $topActions = collect();
        try {
            $topActions = ActivityLog::where('created_at', '>=', $startDate)
                ->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->take(10)
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error getting top actions: ' . $e->getMessage());
            $topActions = collect();
        }

        // Recent suspicious activities - dengan error handling
        $suspiciousActivities = collect();
        try {
            $suspiciousActivities = ActivityLog::suspicious()
                ->with('user.userLevel')
                ->where('created_at', '>=', $startDate)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error getting suspicious activities: ' . $e->getMessage());
            $suspiciousActivities = collect();
        }

        // Recent high-risk activities - dengan error handling
        $highRiskActivities = collect();
        try {
            $highRiskActivities = ActivityLog::highRisk()
                ->with('user.userLevel')
                ->where('created_at', '>=', $startDate)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error getting high risk activities: ' . $e->getMessage());
            $highRiskActivities = collect();
        }

        return view('activity-logs.dashboard', compact(
            'stats', 'activityTrends', 'topUsers', 'topActions',
            'suspiciousActivities', 'highRiskActivities', 'days'
        ));

    } catch (\Exception $e) {
        \Log::error('Error in dashboard: ' . $e->getMessage());

        // Return view with empty data jika ada error
        return view('activity-logs.dashboard', [
            'stats' => [
                'total_activities' => 0,
                'unique_users' => 0,
                'high_risk_activities' => 0,
                'suspicious_activities' => 0,
                'login_attempts' => 0,
                'failed_logins' => 0,
            ],
            'activityTrends' => collect(),
            'topUsers' => collect(),
            'topActions' => collect(),
            'suspiciousActivities' => collect(),
            'highRiskActivities' => collect(),
            'days' => $days
        ])->with('error', 'Terjadi kesalahan saat memuat dashboard. Silakan coba lagi.');
    }
}


    // Export activity logs
    public function export(Request $request)
    {
        $query = ActivityLog::with('user');

        // Apply same filters as index
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->filled('start_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = $request->filled('end_date')
                ? Carbon::parse($request->end_date)->endOfDay()
                : now()->endOfDay();

            $query->dateRange($startDate, $endDate);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        // Simple CSV export
        $filename = 'activity_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Timestamp', 'User', 'Level', 'Action', 'Table', 'Record ID',
                'IP Address', 'Risk Level', 'Description'
            ]);

            // CSV data
            foreach ($logs as $log) {
                $riskLevel = $log->getRiskLevel();
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->getUserName(),
                    $log->getUserLevel(),
                    $log->getFormattedAction(),
                    $log->getFormattedTableName(),
                    $log->record_id,
                    $log->ip_address,
                    $riskLevel['text'],
                    $log->getDescription(),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // API endpoint untuk real-time monitoring
    public function realtimeStats()
    {
        $stats = ActivityLog::getActivityStats(1); // Last 24 hours

        // Recent activities (last 10 minutes)
        $recentActivities = ActivityLog::with('user')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->log_id,
                    'user' => $log->getUserName(),
                    'action' => $log->getFormattedAction(),
                    'table' => $log->getFormattedTableName(),
                    'time' => $log->created_at->format('H:i:s'),
                    'risk_level' => $log->getRiskLevel(),
                ];
            });

        return response()->json([
            'stats' => $stats,
            'recent_activities' => $recentActivities,
            'timestamp' => now()->toISOString()
        ]);
    }
}
