<?php

// ================================================================
// app/Http/Controllers/DashboardController.php
// Command: php artisan make:controller DashboardController
// ================================================================

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserLevel;
// use App\Models\Item;
// use App\Models\Stock;
// use App\Models\PurchaseOrder;
// use App\Models\Transaction;

class DashboardController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        // Data dasar untuk dashboard
        $data = [
            'user' => $user,
            'user_level' => $user->getLevelName(),
            'is_admin' => $user->isAdmin(),
            'is_logistik' => $user->isLogistik(),
            'is_teknisi' => $user->isTeknisi(),
        ];

        // Statistik dasar yang bisa ditampilkan saat ini
        $data['basic_stats'] = $this->getBasicStats();

        // Menu yang tersedia berdasarkan level user
        $data['available_menus'] = $this->getAvailableMenus($user);

        // Aktivitas terbaru user (jika tabel activity_logs sudah ada)
        $data['recent_activities'] = $this->getRecentActivities($user);

        // Alert/notifikasi sederhana
        $data['alerts'] = $this->getAlerts($user);

        return view('dashboard.index', $data);
    }

    // Statistik dasar dari tabel yang sudah ada
    private function getBasicStats()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_user_levels' => UserLevel::count(),
                'users_by_level' => UserLevel::withCount('users')->get(),
            ];

            // Nanti bisa ditambah ketika tabel lain sudah ada
            /*
            $stats['total_items'] = Item::count();
            $stats['total_suppliers'] = Supplier::count();
            $stats['active_pos'] = PurchaseOrder::where('status', '!=', 'cancelled')->count();
            $stats['pending_transactions'] = Transaction::where('status', 'pending')->count();
            $stats['low_stock_items'] = DB::table('items')
                ->join('stocks', 'items.item_id', '=', 'stocks.item_id')
                ->where('stocks.quantity_available', '<=', DB::raw('items.min_stock'))
                ->count();
            */

            return $stats;
        } catch (\Exception $e) {
            // Jika ada error (misal tabel belum ada), return stats kosong
            return [
                'total_users' => 0,
                'active_users' => 0,
                'total_user_levels' => 0,
                'users_by_level' => collect(),
            ];
        }
    }

    // Menu yang tersedia berdasarkan level user
    private function getAvailableMenus($user)
    {
        $baseMenus = [
            [
                'title' => 'Dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'route' => 'dashboard',
                'active' => true,
                'permission' => 'dashboard.read'
            ]
        ];

        $allMenus = [
            // User Management
            [
                'title' => 'Pengguna',
                'icon' => 'fas fa-users',
                'route' => 'users.index',
                'permission' => 'users.read',
                'admin_only' => true
            ],
            [
                'title' => 'Level Pengguna',
                'icon' => 'fas fa-user-tag',
                'route' => 'user-levels.index',
                'permission' => 'user_levels.read',
                'admin_only' => true
            ],

            // Data Master (nanti)
            [
                'title' => 'Kategori Barang',
                'icon' => 'fas fa-tags',
                'route' => 'categories.index',
                'permission' => 'categories.read',
                'coming_soon' => true
            ],
            [
                'title' => 'Data Supplier',
                'icon' => 'fas fa-truck',
                'route' => 'suppliers.index',
                'permission' => 'suppliers.read',
                'coming_soon' => true
            ],
            [
                'title' => 'Data Barang',
                'icon' => 'fas fa-boxes',
                'route' => 'items.index',
                'permission' => 'items.read',
                'coming_soon' => true
            ],

            // Inventory (nanti)
            [
                'title' => 'Stok Barang',
                'icon' => 'fas fa-warehouse',
                'route' => 'stocks.index',
                'permission' => 'stocks.read',
                'coming_soon' => true
            ],
            [
                'title' => 'Purchase Order',
                'icon' => 'fas fa-shopping-cart',
                'route' => 'purchase-orders.index',
                'permission' => 'purchase_orders.read',
                'coming_soon' => true
            ],
            [
                'title' => 'Transaksi',
                'icon' => 'fas fa-exchange-alt',
                'route' => 'transactions.index',
                'permission' => 'transactions.read',
                'coming_soon' => true
            ],

            // Tools
            [
                'title' => 'Scan QR Code',
                'icon' => 'fas fa-qrcode',
                'route' => 'qr.scanner',
                'permission' => 'qr_scanner.scan',
                'coming_soon' => true
            ],
        ];

        // Filter menu berdasarkan level user
        $availableMenus = $baseMenus;

        foreach ($allMenus as $menu) {
            $canAccess = true;

            // Cek jika menu hanya untuk admin
            if (isset($menu['admin_only']) && $menu['admin_only'] && !$user->isAdmin()) {
                $canAccess = false;
            }

            // Nanti bisa ditambah permission check
            // if (isset($menu['permission']) && !$user->hasPermission($module, $action)) {
            //     $canAccess = false;
            // }

            if ($canAccess) {
                $availableMenus[] = $menu;
            }
        }

        return $availableMenus;
    }

    // Aktivitas terbaru user
    private function getRecentActivities($user)
    {
        try {
            // Cek apakah model ActivityLog ada dan tabel sudah ada
            if (class_exists(\App\Models\ActivityLog::class)) {
                return $user->activityLogs()
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(function ($log) {
                        return [
                            'action' => $log->action,
                            'table_name' => $log->table_name,
                            'created_at' => $log->created_at,
                            'description' => $this->formatActivityDescription($log)
                        ];
                    });
            }
        } catch (\Exception $e) {
            // Jika tabel belum ada atau error lain
        }

        // Return aktivitas dummy untuk sementara
        return collect([
            [
                'action' => 'login',
                'table_name' => 'users',
                'created_at' => now(),
                'description' => 'Login ke sistem'
            ]
        ]);
    }

    // Format deskripsi aktivitas
    private function formatActivityDescription($log)
    {
        $tableNames = [
            'users' => 'Pengguna',
            'user_levels' => 'Level Pengguna',
            'items' => 'Barang',
            'suppliers' => 'Supplier',
            'categories' => 'Kategori',
            'purchase_orders' => 'Purchase Order',
            'transactions' => 'Transaksi',
        ];

        $actions = [
            'create' => 'Menambah',
            'update' => 'Mengubah',
            'delete' => 'Menghapus',
            'login' => 'Login',
            'logout' => 'Logout',
        ];

        $tableName = $tableNames[$log->table_name] ?? $log->table_name;
        $action = $actions[$log->action] ?? $log->action;

        return "{$action} {$tableName}";
    }

    // Alert/notifikasi untuk user
    private function getAlerts($user)
    {
        $alerts = [];

        // Alert welcome untuk user baru
        if ($user->created_at->diffInDays(now()) <= 1) {
            $alerts[] = [
                'type' => 'info',
                'message' => 'Selamat datang di Sistem Logistik! Anda adalah user baru.',
                'icon' => 'fas fa-info-circle'
            ];
        }

        // Alert untuk admin tentang sistem
        if ($user->isAdmin()) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Sistem masih dalam tahap pengembangan. Beberapa fitur belum tersedia.',
                'icon' => 'fas fa-exclamation-triangle'
            ];
        }

        // Nanti bisa ditambah alert lain seperti:
        /*
        // Alert stok rendah
        if ($user->isLogistik() || $user->isAdmin()) {
            $lowStockCount = $this->getLowStockCount();
            if ($lowStockCount > 0) {
                $alerts[] = [
                    'type' => 'danger',
                    'message' => "Ada {$lowStockCount} barang dengan stok rendah!",
                    'icon' => 'fas fa-exclamation-circle',
                    'link' => route('stocks.index', ['filter' => 'low'])
                ];
            }
        }

        // Alert PO pending approval
        if ($user->isAdmin()) {
            $pendingPO = $this->getPendingPOCount();
            if ($pendingPO > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => "Ada {$pendingPO} Purchase Order menunggu persetujuan!",
                    'icon' => 'fas fa-clock',
                    'link' => route('purchase-orders.index', ['status' => 'pending'])
                ];
            }
        }
        */

        return $alerts;
    }

    // Method untuk get data summary (untuk AJAX calls)
    public function getSummary()
    {
        $user = Auth::user();

        return response()->json([
            'user' => [
                'name' => $user->full_name,
                'level' => $user->getLevelName(),
                'last_login' => $user->updated_at->format('d/m/Y H:i')
            ],
            'stats' => $this->getBasicStats(),
            'alerts_count' => count($this->getAlerts($user))
        ]);
    }
}
