<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Item;
use App\Models\ItemDetail;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\GoodsReceived;
use App\Models\PurchaseOrder;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Stock Overview
        $stockSummary = Stock::getStockSummary();

        // Transaction Statistics
        $transactionStats = Transaction::getTransactionStats(30);

        // Item Statistics
        $itemStats = $this->getItemStatistics();

        // Low Stock Alerts
        $lowStockAlerts = Stock::getLowStockAlerts();

        // Recent Activities
        $recentActivities = $this->getRecentActivities();

        // Monthly Trends
        $monthlyTrends = $this->getMonthlyTrends();

        // Category Statistics
        $categoryStats = $this->getCategoryStatistics();

        // Goods Received Statistics
        $goodsReceivedStats = GoodsReceived::getStatistics();

        // Status Change Trends
        $statusChangeTrends = TransactionDetail::getStatusChangeStats(30);

        // Critical Alerts
        $criticalAlerts = $this->getCriticalAlerts();

        // Pending Approvals
        $pendingApprovals = $this->getPendingApprovals();

        // Utilization Metrics
        $utilizationMetrics = $this->getUtilizationMetrics();

        return view('dashboard.index', compact(
            'stockSummary',
            'transactionStats',
            'itemStats',
            'lowStockAlerts',
            'recentActivities',
            'monthlyTrends',
            'categoryStats',
            'goodsReceivedStats',
            'statusChangeTrends',
            'criticalAlerts',
            'pendingApprovals',
            'utilizationMetrics'
        ));
    }

    private function getItemStatistics(): array
    {
        $totalItems = Item::count();
        $activeItems = Item::where('is_active', true)->count();
        $inactiveItems = $totalItems - $activeItems;

        $itemsWithStock = Item::whereHas('stock', function($query) {
            $query->where('total_quantity', '>', 0);
        })->count();

        $itemsWithoutStock = $totalItems - $itemsWithStock;

        return [
            'total_items' => $totalItems,
            'active_items' => $activeItems,
            'inactive_items' => $inactiveItems,
            'items_with_stock' => $itemsWithStock,
            'items_without_stock' => $itemsWithoutStock,
            'low_stock_items' => Item::lowStock()->count(),
        ];
    }

    private function getRecentActivities(): array
    {
        $activities = collect();

        // Recent Transactions
        $recentTransactions = Transaction::with(['item', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($transaction) {
                return [
                    'type' => 'transaction',
                    'icon' => 'fas fa-exchange-alt',
                    'title' => 'Transaction ' . $transaction->transaction_number,
                    'description' => $transaction->getTypeInfo()['text'] . ' - ' . ($transaction->item->item_name ?? 'Unknown Item'),
                    'user' => $transaction->createdBy->full_name ?? 'Unknown',
                    'time' => $transaction->created_at,
                    'status' => $transaction->getStatusInfo(),
                ];
            });

        // Recent Goods Received
        $recentGoodsReceived = GoodsReceived::with(['supplier', 'receivedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($gr) {
                return [
                    'type' => 'goods_received',
                    'icon' => 'fas fa-truck',
                    'title' => 'Goods Received ' . $gr->receive_number,
                    'description' => 'From ' . ($gr->supplier->supplier_name ?? 'Unknown Supplier'),
                    'user' => $gr->receivedBy->full_name ?? 'Unknown',
                    'time' => $gr->created_at,
                    'status' => $gr->getStatusInfo(),
                ];
            });

        return $activities->merge($recentTransactions)
            ->merge($recentGoodsReceived)
            ->sortByDesc('time')
            ->take(15)
            ->values()
            ->toArray();
    }

    private function getMonthlyTrends(): array
    {
        $last6Months = collect();

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->startOfMonth();
            $monthEnd = $month->endOfMonth();

            $transactions = Transaction::whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->groupBy('transaction_type')
                ->selectRaw('transaction_type, COUNT(*) as count')
                ->pluck('count', 'transaction_type')
                ->toArray();

            $goodsReceived = GoodsReceived::whereBetween('receive_date', [$monthStart, $monthEnd])
                ->count();

            $last6Months->push([
                'month' => $month->format('M Y'),
                'month_short' => $month->format('M'),
                'transactions' => $transactions,
                'goods_received' => $goodsReceived,
                'total_transactions' => array_sum($transactions),
            ]);
        }

        return $last6Months->toArray();
    }

    private function getCategoryStatistics(): array
    {
        $categories = Category::with(['items.stock'])
            ->where('is_active', true)
            ->get()
            ->map(function($category) {
                $items = $category->items;
                $totalItems = $items->count();
                $totalStock = $items->sum(function($item) {
                    return $item->stock->total_quantity ?? 0;
                });
                $availableStock = $items->sum(function($item) {
                    return $item->stock->quantity_available ?? 0;
                });

                return [
                    'category_name' => $category->category_name,
                    'total_items' => $totalItems,
                    'total_stock' => $totalStock,
                    'available_stock' => $availableStock,
                    'utilization_rate' => $totalStock > 0 ? round((($totalStock - $availableStock) / $totalStock) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('total_stock')
            ->take(10)
            ->values()
            ->toArray();

        return $categories;
    }

    private function getCriticalAlerts(): array
    {
        $alerts = [];

        // Low Stock Items
        $lowStockCount = Stock::lowStock()->count();
        if ($lowStockCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'fas fa-exclamation-triangle',
                'title' => 'Low Stock Alert',
                'message' => "{$lowStockCount} items have low stock levels",
                'action_url' => route('stocks.index', ['filter' => 'low']),
                'action_text' => 'View Details'
            ];
        }

        // Out of Stock Items
        $outOfStockCount = Stock::outOfStock()->count();
        if ($outOfStockCount > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'fas fa-times-circle',
                'title' => 'Out of Stock Alert',
                'message' => "{$outOfStockCount} items are out of stock",
                'action_url' => route('stocks.index', ['filter' => 'empty']),
                'action_text' => 'View Details'
            ];
        }

        // Pending Transactions
        $pendingCount = Transaction::where('status', Transaction::STATUS_PENDING)->count();
        if ($pendingCount > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'fas fa-clock',
                'title' => 'Pending Approvals',
                'message' => "{$pendingCount} transactions waiting for approval",
                'action_url' => route('transactions.index', ['status' => 'pending']),
                'action_text' => 'Review Now'
            ];
        }

        // Items in Repair
        $repairCount = ItemDetail::where('status', 'repair')->count();
        if ($repairCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'fas fa-wrench',
                'title' => 'Items in Repair',
                'message' => "{$repairCount} items are currently in repair",
                'action_url' => route('item-details.index', ['status' => 'repair']),
                'action_text' => 'View Details'
            ];
        }

        return $alerts;
    }

    private function getPendingApprovals(): array
    {
        return Transaction::with(['item', 'createdBy'])
            ->where('status', Transaction::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->transaction_id,
                    'number' => $transaction->transaction_number,
                    'type' => $transaction->getTypeInfo(),
                    'item_name' => $transaction->item->item_name ?? 'Unknown',
                    'created_by' => $transaction->createdBy->full_name ?? 'Unknown',
                    'created_at' => $transaction->created_at,
                    'age_hours' => $transaction->created_at->diffInHours(now()),
                    'notes' => $transaction->notes,
                ];
            })
            ->toArray();
    }

    private function getUtilizationMetrics(): array
    {
        $totalItems = ItemDetail::count();
        $availableItems = ItemDetail::where('status', 'available')->count();
        $usedItems = ItemDetail::where('status', 'used')->count();
        $repairItems = ItemDetail::where('status', 'repair')->count();
        $lostItems = ItemDetail::where('status', 'lost')->count();

        return [
            'total_items' => $totalItems,
            'available_items' => $availableItems,
            'used_items' => $usedItems,
            'repair_items' => $repairItems,
            'lost_items' => $lostItems,
            'utilization_rate' => $totalItems > 0 ? round(($usedItems / $totalItems) * 100, 1) : 0,
            'availability_rate' => $totalItems > 0 ? round(($availableItems / $totalItems) * 100, 1) : 0,
        ];
    }

    public function getChartData(Request $request)
    {
        $type = $request->get('type');

        switch ($type) {
            case 'stock-trends':
                return $this->getStockTrendsData();
            case 'transaction-trends':
                return $this->getTransactionTrendsData();
            case 'category-distribution':
                return $this->getCategoryDistributionData();
            case 'status-distribution':
                return $this->getStatusDistributionData();
            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }

    private function getStockTrendsData(): array
    {
        $last30Days = collect();

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);

            // This is a simplified version - in reality you'd need stock movement tracking
            $stockData = Stock::selectRaw('
                SUM(quantity_available) as total_available,
                SUM(quantity_used) as total_used,
                SUM(total_quantity) as total_inventory
            ')->first();

            $last30Days->push([
                'date' => $date->format('Y-m-d'),
                'date_formatted' => $date->format('M d'),
                'available' => $stockData->total_available ?? 0,
                'used' => $stockData->total_used ?? 0,
                'total' => $stockData->total_inventory ?? 0,
            ]);
        }

        return $last30Days->toArray();
    }

    private function getTransactionTrendsData(): array
    {
        $last30Days = collect();

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);

            $transactions = Transaction::whereDate('transaction_date', $date)
                ->groupBy('transaction_type')
                ->selectRaw('transaction_type, COUNT(*) as count')
                ->pluck('count', 'transaction_type')
                ->toArray();

            $last30Days->push([
                'date' => $date->format('Y-m-d'),
                'date_formatted' => $date->format('M d'),
                'in' => $transactions[Transaction::TYPE_IN] ?? 0,
                'out' => $transactions[Transaction::TYPE_OUT] ?? 0,
                'repair' => $transactions[Transaction::TYPE_REPAIR] ?? 0,
                'return' => $transactions[Transaction::TYPE_RETURN] ?? 0,
                'lost' => $transactions[Transaction::TYPE_LOST] ?? 0,
            ]);
        }

        return $last30Days->toArray();
    }

    private function getCategoryDistributionData(): array
    {
        return Category::with(['items.stock'])
            ->where('is_active', true)
            ->get()
            ->map(function($category) {
                $totalStock = $category->items->sum(function($item) {
                    return $item->stock->total_quantity ?? 0;
                });

                return [
                    'category' => $category->category_name,
                    'total_stock' => $totalStock,
                    'item_count' => $category->items->count(),
                ];
            })
            ->where('total_stock', '>', 0)
            ->sortByDesc('total_stock')
            ->take(10)
            ->values()
            ->toArray();
    }

    private function getStatusDistributionData(): array
    {
        $statusCounts = ItemDetail::groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->pluck('count', 'status')
            ->toArray();

        $statusLabels = [
            'available' => 'Available',
            'used' => 'Used',
            'repair' => 'In Repair',
            'lost' => 'Lost',
            'damaged' => 'Damaged',
            'maintenance' => 'Maintenance',
            'reserved' => 'Reserved',
        ];

        return collect($statusCounts)
            ->map(function($count, $status) use ($statusLabels) {
                return [
                    'status' => $status,
                    'label' => $statusLabels[$status] ?? ucfirst($status),
                    'count' => $count,
                ];
            })
            ->values()
            ->toArray();
    }

    // API Methods for real-time updates
    public function getPendingCount()
    {
        $count = Transaction::where('status', Transaction::STATUS_PENDING)->count();
        return response()->json(['count' => $count]);
    }

    public function getLowStockCount()
    {
        $count = Stock::lowStock()->count();
        return response()->json(['count' => $count]);
    }

    public function getAlerts()
    {
        $alerts = $this->getCriticalAlerts();
        return response()->json(['alerts' => $alerts]);
    }

    public function getQuickStats()
    {
        $stats = [
            'total_items' => Item::count(),
            'available_items' => ItemDetail::where('status', 'available')->count(),
            'pending_approvals' => Transaction::where('status', Transaction::STATUS_PENDING)->count(),
            'low_stock_items' => Stock::lowStock()->count(),
            'transactions_today' => Transaction::whereDate('created_at', today())->count(),
            'goods_received_today' => GoodsReceived::whereDate('created_at', today())->count(),
            'utilization_rate' => $this->calculateUtilizationRate(),
        ];

        return response()->json($stats);
    }

    // public function getRecentActivities()
    // {
    //     $activities = $this->getRecentActivities();
    //     return response()->json(['activities' => $activities]);
    // }

    private function calculateUtilizationRate(): float
    {
        $totalItems = ItemDetail::count();
        $usedItems = ItemDetail::where('status', 'used')->count();

        return $totalItems > 0 ? round(($usedItems / $totalItems) * 100, 1) : 0;
    }
}
