<?php

// ================================================================
// routes/web.php - Authentication & Main Routes dengan Permission
// ================================================================

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\GoodsReceivedController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemDetailController;
use App\Http\Controllers\PoDetailController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserLevelController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\TransactionHistoryController;
use App\Models\Transaction; // For static methods in routes

// ================================================================
// PUBLIC ROUTES (Guest Only)
// ================================================================

// Redirect root ke dashboard jika sudah login, ke login jika belum
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authentication Routes (hanya untuk guest)
Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    // Register Routes (opsional - untuk demo/testing)
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

// ================================================================
// PROTECTED ROUTES (Authenticated Users Only)
// ================================================================

Route::middleware('auth')->group(function () {

    // Logout Route
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ================================================================
    // DASHBOARD (All Users)
    // ================================================================
    Route::middleware('permission:dashboard,read')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Dashboard API endpoints for real-time updates
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/chart-data', [DashboardController::class, 'getChartData'])->name('chart-data');
            Route::get('/pending-count', [DashboardController::class, 'getPendingCount'])->name('pending-count');
            Route::get('/low-stock-count', [DashboardController::class, 'getLowStockCount'])->name('low-stock-count');
            Route::get('/alerts', [DashboardController::class, 'getAlerts'])->name('alerts');
            Route::get('/quick-stats', [DashboardController::class, 'getQuickStats'])->name('quick-stats');
            Route::get('/recent-activities', [DashboardController::class, 'getRecentActivities'])->name('recent-activities');
        });
    });

    // ================================================================
    // USER MANAGEMENT ROUTES (Admin Only)
    // ================================================================
    Route::middleware('permission:user_levels,create')->group(function () {
        Route::get('/user-levels/create', [UserLevelController::class, 'create'])->name('user-levels.create');
        Route::post('/user-levels', [UserLevelController::class, 'store'])->name('user-levels.store');
    });
    // User Level Management
    Route::middleware('permission:user_levels,read')->group(function () {
        Route::get('/user-levels', [UserLevelController::class, 'index'])->name('user-levels.index');
        Route::get('/user-levels/{userLevel}', [UserLevelController::class, 'show'])->name('user-levels.show');
    });



    Route::middleware('permission:user_levels,update')->group(function () {
        Route::get('/user-levels/{userLevel}/edit', [UserLevelController::class, 'edit'])->name('user-levels.edit');
        Route::put('/user-levels/{userLevel}', [UserLevelController::class, 'update'])->name('user-levels.update');
    });

    Route::middleware('permission:user_levels,delete')->group(function () {
        Route::delete('/user-levels/{userLevel}', [UserLevelController::class, 'destroy'])->name('user-levels.destroy');
    });
    Route::middleware('permission:users,create')->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });

    // User Management
    Route::middleware('permission:users,read')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    });



    Route::middleware('permission:users,update')->group(function () {
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });

    Route::middleware('permission:users,delete')->group(function () {
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // ================================================================
    // CATEGORY MANAGEMENT
    // ================================================================

    Route::middleware('permission:categories,create')->group(function () {
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    });

    Route::middleware('permission:categories,read')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
        Route::get('/categories/api/children/{parent?}', [CategoryController::class, 'getChildren'])->name('categories.children');
    });



    Route::middleware('permission:categories,update')->group(function () {
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::patch('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    });

    Route::middleware('permission:categories,delete')->group(function () {
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // ================================================================
    // SUPPLIER MANAGEMENT
    // ================================================================
    Route::middleware('permission:suppliers,create')->group(function () {
        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    });
    Route::middleware('permission:suppliers,read')->group(function () {
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    });



    Route::middleware('permission:suppliers,update')->group(function () {
        Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::patch('/suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');
    });

    Route::middleware('permission:suppliers,delete')->group(function () {
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });

    // Supplier API Routes
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/suppliers/search', [SupplierController::class, 'search'])->name('suppliers.search');
        Route::get('/suppliers/list', [SupplierController::class, 'list'])->name('suppliers.list');
    });

    // ================================================================
    // ITEM MANAGEMENT
    // ================================================================
    Route::middleware('permission:items,create')->group(function () {

        Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
        Route::post('/items', [ItemController::class, 'store'])->name('items.store');
        Route::post('/items/{item}/generate-qr', [ItemController::class, 'generateQR'])->name('items.generate-qr');
    });

    Route::middleware('permission:items,read')->group(function () {
        Route::get('/itemsCode/code', [ItemController::class, 'indexViewCode'])->name('itemsCode.indexCode');
        Route::get('/items/export/excel', [ItemController::class, 'exportExcel'])->name('items.export.excel');
        Route::get('/items', [ItemController::class, 'index'])->name('items.index');
        Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
    });


    Route::middleware('permission:items,update')->group(function () {
        Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
        Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
        Route::patch('/items/{item}/toggle-status', [ItemController::class, 'toggleStatus'])->name('items.toggle-status');
        Route::get('/items/{item}/download-qr', [ItemController::class, 'downloadQR'])->name('items.download-qr');
    });

    Route::middleware('permission:items,delete')->group(function () {
        Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
    });

    // Items API Routes
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/items/search', [ItemController::class, 'search'])->name('items.search');
        Route::get('/items/by-category/{category}', [ItemController::class, 'getByCategory'])->name('items.by-category');
    });

    // ================================================================
    // STOCK MANAGEMENT
    // ================================================================

    Route::middleware('permission:stocks,read')->group(function () {
        Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');
        Route::get('/stocks/{stock}', [StockController::class, 'show'])->name('stocks.show');
    });

    Route::middleware('permission:stocks,update')->group(function () {
        Route::get('/stocks/{stock}/edit', [StockController::class, 'edit'])->name('stocks.edit');
        Route::put('/stocks/{stock}', [StockController::class, 'update'])->name('stocks.update');
    });

    Route::middleware('permission:stocks,adjust')->group(function () {
        Route::get('/stocks/adjust/form', [StockController::class, 'adjust'])->name('stocks.adjust');
        Route::post('/stocks/adjustment', [StockController::class, 'adjustment'])->name('stocks.adjustment');
        Route::post('/stocks/bulk-adjustment', [StockController::class, 'bulkAdjustment'])->name('stocks.bulk-adjustment');
        Route::post('/stocks/sync-all', [StockController::class, 'syncAll'])->name('stocks.sync-all');
        Route::post('/stocks/{stock}/sync', [StockController::class, 'syncStock'])->name('stocks.sync');
        Route::get('/stocks/inconsistencies', [StockController::class, 'inconsistencies'])->name('stocks.inconsistencies');
        Route::post('/stocks/auto-fix-inconsistencies', [StockController::class, 'autoFixInconsistencies'])->name('stocks.auto-fix-inconsistencies');
    });

    Route::middleware('permission:stocks,create')->group(function () {
        // Create stock routes jika diperlukan
    });

    Route::middleware('permission:stocks,delete')->group(function () {
        // Delete stock routes jika diperlukan
    });

    // Stock API Routes
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/stocks/check/{item}', [StockController::class, 'checkStock'])->name('stocks.check');
        Route::get('/stocks/low-stock', [StockController::class, 'getLowStockItems'])->name('stocks.low-stock');
        Route::get('/stocks/{stock}/validate', [StockController::class, 'validateConsistency'])->name('stocks.validate');
        Route::get('/stocks/{stock}/movement-summary', [StockController::class, 'getMovementSummary'])->name('stocks.movement-summary');
        Route::get('/stocks/dashboard-summary', [StockController::class, 'getDashboardSummary'])->name('stocks.dashboard-summary');
    });

    // ================================================================
    // PURCHASE ORDER MANAGEMENT - UPDATED WITH WORKFLOW
    // ================================================================

    // CREATE - Logistik only (LVL002)
    Route::middleware('permission:purchase_orders,create')->group(function () {
        Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::post('/purchase-orders/{purchaseOrder}/duplicate', [PurchaseOrderController::class, 'duplicate'])->name('purchase-orders.duplicate');
    });

    // READ - All levels can view (filtered by controller based on user level)
    Route::middleware('permission:purchase_orders,read')->group(function () {
        Route::get('{purchaseOrder}/download-pdf', [PurchaseOrderController::class, 'downloadPDF'])
            ->name('purchase-orders.download-pdf');

        Route::get('{purchaseOrder}/view-pdf', [PurchaseOrderController::class, 'viewPDF'])
            ->name('purchase-orders.view-pdf');
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        Route::get('/purchase-orders/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])->name('purchase-orders.print');
    });

    Route::middleware('auth')->group(function () {
        // Profile routes - cuma edit & update
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    });


    // Tambahkan ke routes/web.php
    Route::middleware('permission:reports,read')->group(function () {
        Route::get('/reports/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
        Route::get('/reports/api/chart-data', [ReportController::class, 'getApiData'])->name('reports.api.chart-data');
        Route::get('/reports/api/damage-analysis', [ReportController::class, 'getDamageAnalysis'])->name('reports.api.damage-analysis');
        Route::get('/reports/api/real-time-stats', [ReportController::class, 'getRealTimeStats'])->name('reports.api.real-time-stats');
           Route::post('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
        Route::post('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    });

    Route::middleware('permission:reports,export')->group(function () {

    });

    // UPDATE - Basic updates (Logistik untuk draft, Admin untuk override)
    Route::middleware('permission:purchase_orders,update')->group(function () {
        Route::get('/purchase-orders/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
        Route::put('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');

        // Legacy status updates (Admin only now)
        Route::patch('/purchase-orders/{purchaseOrder}/update-status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.update-status');
        Route::post('/purchase-orders/{purchaseOrder}/send', [PurchaseOrderController::class, 'send'])->name('purchase-orders.update-send');
        Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
    });

    // NEW: WORKFLOW ROUTES
    Route::middleware('permission:purchase_orders,update')->group(function () {
        // Logistik Actions (LVL002)
        Route::post('/purchase-orders/{purchaseOrder}/submit-to-finance-f1', [PurchaseOrderController::class, 'submitToFinanceF1'])->name('purchase-orders.submit-to-finance-f1');

        // Finance F1 Actions (LVL004, LVL005)
        Route::post('/purchase-orders/{purchaseOrder}/process-finance-f1', [PurchaseOrderController::class, 'processFinanceF1'])->name('purchase-orders.process-finance-f1');
        Route::post('/purchase-orders/{purchaseOrder}/reject-finance-f1', [PurchaseOrderController::class, 'rejectFinanceF1'])->name('purchase-orders.reject-finance-f1');

        // FINANCE RBP Actions (LVL005 only)
        Route::post('/purchase-orders/{purchaseOrder}/approve-finance-f2', [PurchaseOrderController::class, 'approveFinanceF2'])->name('purchase-orders.approve-finance-f2');
        Route::post('/purchase-orders/{purchaseOrder}/reject-finance-f2', [PurchaseOrderController::class, 'rejectFinanceF2'])->name('purchase-orders.reject-finance-f2');
    });

    // APPROVE - Now includes workflow approvals
    Route::middleware('permission:purchase_orders,approve')->group(function () {
        // Admin override actions
        Route::post('/purchase-orders/{purchaseOrder}/return-from-reject', [PurchaseOrderController::class, 'returnFromReject'])->name('purchase-orders.return-from-reject');
    });

    Route::middleware('permission:purchase_orders,delete')->group(function () {
        // Delete PO routes jika diperlukan
        // Route::delete('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');
    });

    // PO Details API - UNCHANGED
    Route::prefix('api/po-details')->name('api.po-details.')->group(function () {
        Route::post('/{purchaseOrder}/add-item', [PoDetailController::class, 'addItem'])->name('add-item');
        Route::put('/{poDetail}', [PoDetailController::class, 'update'])->name('update');
        Route::delete('/{poDetail}', [PoDetailController::class, 'destroy'])->name('destroy');
        Route::get('/{purchaseOrder}/details', [PoDetailController::class, 'getDetails'])->name('get-details');
    });

    // Purchase Order API - ENHANCED
    Route::prefix('api/purchase-orders')->name('api.purchase-orders.')->group(function () {
        // Existing API
        Route::get('/by-supplier/{supplier}', [PurchaseOrderController::class, 'getBySupplier'])->name('by-supplier');

        // NEW: Workflow APIs
        Route::get('/{purchaseOrder}/payment-methods', [PurchaseOrderController::class, 'getAvailablePaymentMethods'])->name('payment-methods');
        Route::get('/workflow-statistics', [PurchaseOrderController::class, 'getWorkflowStatistics'])->name('workflow-statistics');
    });

    // ================================================================
    // GOODS RECEIVED MANAGEMENT
    // ================================================================



    Route::middleware('permission:goods_receiveds,create')->group(function () {
        Route::get('/goods-received/create', [GoodsReceivedController::class, 'create'])->name('goods-received.create');
        Route::post('/goods-received', [GoodsReceivedController::class, 'store'])->name('goods-received.store');
        Route::post('/api/goods-received/validate-serial-number', [GoodsReceivedController::class, 'validateSerialNumber'])->name('validate-serial-number');
    });



    Route::middleware('permission:goods_receiveds,read')->group(function () {
        Route::get('/goods-received', [GoodsReceivedController::class, 'index'])->name('goods-received.index');
        Route::get('/goods-received/{goodsReceived}', [GoodsReceivedController::class, 'show'])->name('goods-received.show');
    });

    Route::middleware('permission:goods_receiveds,update')->group(function () {
        Route::get('/goods-received/{goodsReceived}/edit', [GoodsReceivedController::class, 'edit'])->name('goods-received.edit');
        Route::put('/goods-received/{goodsReceived}', [GoodsReceivedController::class, 'update'])->name('goods-received.update');
    });

    Route::middleware('permission:goods_receiveds,delete')->group(function () {
        Route::delete('/goods-received/{goodsReceived}', [GoodsReceivedController::class, 'destroy'])->name('goods-received.destroy');
    });

    // Goods Received API
    Route::prefix('api/goods-received')->name('api.goods-received.')->group(function () {
        Route::get('/po-details/{po}', [GoodsReceivedController::class, 'getPODetails'])->name('po-details');
    });
    Route::prefix('api/goods-received')->group(function () {
        // Serial Number Management
        Route::post('serial-number-template', [GoodsReceivedController::class, 'getSerialNumberTemplate'])
            ->name('api.goods-received.serial-template');

        Route::post('validate-serial-number', [GoodsReceivedController::class, 'validateSerialNumber'])
            ->name('api.goods-received.validate-serial');

        Route::post('bulk-validate-serial-numbers', [GoodsReceivedController::class, 'bulkValidateSerialNumbers'])
            ->name('api.goods-received.bulk-validate-serials');

        Route::get('serial-number-stats', [GoodsReceivedController::class, 'getSerialNumberStats'])
            ->name('api.goods-received.serial-stats');
    });

    // ================================================================
    // TRANSACTION MANAGEMENT
    // ================================================================

    Route::middleware('permission:transactions,create')->group(function () {
        Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    });

    Route::middleware('permission:transactions,update')->group(function () {
        Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
        Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
        Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
    });


    Route::middleware('permission:transactions,read')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::get('/transactions/history', [TransactionController::class, 'history'])->name('transactions.history');
    });

    Route::middleware('permission:transactions,create')->group(function () {
        Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    });

    Route::middleware('permission:transactions,update')->group(function () {
        Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
        Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
        Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
    });

    Route::middleware('permission:transactions,delete')->group(function () {
        // Delete transaction routes jika diperlukan
    });

    Route::middleware('permission:transactions,approve')->group(function () {
        // Approval Management Routes
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approvals/{transaction}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/{transaction}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{transaction}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
        Route::post('/approvals/{transaction}/quick-approve', [ApprovalController::class, 'quickApprove'])->name('approvals.quick-approve');
        Route::post('/approvals/bulk-approve', [ApprovalController::class, 'bulkApprove'])->name('approvals.bulk-approve');
        Route::post('/approvals/bulk-reject', [ApprovalController::class, 'bulkReject'])->name('approvals.bulk-reject');
        Route::get('/approvals/history', [ApprovalController::class, 'history'])->name('approvals.history');
        Route::get('/approvals/analytics', [ApprovalController::class, 'analytics'])->name('approvals.analytics');
    });

    // Transaction API Routes
    Route::prefix('api/transactions')->name('api.transactions.')->group(function () {
        Route::post('/create-from-qr', [TransactionController::class, 'createFromQR'])->name('create-from-qr');
        Route::post('/scan-qr', [TransactionController::class, 'scanQR'])->name('scan-qr');
        Route::get('/available-items', [TransactionController::class, 'getAvailableItems'])->name('available-items');
        Route::get('/types', function () {
            return response()->json(Transaction::getUserAllowedTypes());
        })->name('types');
        // ✅ ENHANCED: Tambah endpoint untuk damage info
        Route::get('/damage-levels', function () {
            return response()->json(Transaction::getDamageLevels());
        })->name('damage-levels');

        Route::get('/damage-reasons', function () {
            return response()->json(Transaction::getDamageReasons());
        })->name('damage-reasons');

        Route::get('/types', function () {
            return response()->json(Transaction::getUserAllowedTypes());
        })->name('types');
    });

    // Approval API Routes
    Route::prefix('api/approvals')->name('api.approvals.')->group(function () {
        Route::get('/summary', [ApprovalController::class, 'getSummary'])->name('summary');
        Route::get('/{transaction}/details', [ApprovalController::class, 'getTransactionDetails'])->name('details');
    });

    // ================================================================
    // REQUEST MANAGEMENT ROUTES (Teknisi)
    // ================================================================

    Route::middleware('permission:transactions,create')->group(function () {
        Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
        Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');
        Route::get('/requests/my-items', [RequestController::class, 'myItems'])->name('requests.my-items');
        Route::post('/requests/return-item', [RequestController::class, 'returnItem'])->name('requests.return-item');
    });

    Route::middleware('permission:transactions,read')->group(function () {
        Route::get('/requests/{transaction}', [RequestController::class, 'show'])->name('requests.show');
        Route::get('/requests/history', [RequestController::class, 'history'])->name('requests.history');
        Route::post('/requests/{transaction}/cancel', [RequestController::class, 'cancel'])->name('requests.cancel');
    });

    // Request API Routes
    Route::prefix('api/requests')->name('api.requests.')->group(function () {
        Route::get('/quick-request', [RequestController::class, 'quickRequest'])->name('quick-request');
        Route::get('/items-by-category', [RequestController::class, 'getItemsByCategory'])->name('items-by-category');
        Route::get('/search-items', [RequestController::class, 'searchItems'])->name('search-items');
        Route::get('/categories', [RequestController::class, 'getCategories'])->name('categories');
        Route::get('/item-suggestions', [RequestController::class, 'getItemSuggestions'])->name('item-suggestions');
    });

    // ================================================================
    // TRANSACTION HISTORY & REPORTS
    // ================================================================

    Route::middleware('permission:reports,read')->group(function () {
        Route::get('/transaction-history', [TransactionHistoryController::class, 'index'])->name('transaction-history.index');
        Route::get('/transaction-history/{transaction}', [TransactionHistoryController::class, 'show'])->name('transaction-history.show');
        Route::get('/transaction-history/analytics', [TransactionHistoryController::class, 'analytics'])->name('transaction-history.analytics');
        Route::get('/transaction-history/alerts', [TransactionHistoryController::class, 'alerts'])->name('transaction-history.alerts');
        Route::get('/transaction-history/stock-movement', [TransactionHistoryController::class, 'stockMovement'])->name('transaction-history.stock-movement');
    });

    Route::middleware('permission:reports,export')->group(function () {
        Route::get('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
        Route::post('/transaction-history/report', [TransactionHistoryController::class, 'report'])->name('transaction-history.report');
    });

    // Transaction History API Routes
    Route::prefix('api/transaction-history')->name('api.transaction-history.')->group(function () {
        Route::get('/item-timeline', [TransactionHistoryController::class, 'itemTimeline'])->name('item-timeline');
    });

    // ================================================================
    // ACTIVITY LOGS
    // ================================================================

    Route::middleware('permission:activity_logs,read')->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/activity-logs/dashboard', [ActivityLogController::class, 'dashboard'])->name('activity-logs.dashboard');
        Route::get('/activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');
        Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    });

    // Activity Logs API
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/activity-logs/realtime', [ActivityLogController::class, 'realtimeStats'])->name('activity-logs.realtime');
    });

    // ================================================================
    // QR SCANNER
    // ================================================================

    Route::middleware('permission:qr_scanner,read')->group(function () {
        Route::get('/qr/transaction-scanner', function () {
            return view('qr.transaction-scanner');
        })->name('qr.transaction-scanner');
        Route::get('/qr/item-scanner', function () {
            return view('qr.item-scanner');
        })->name('qr.item-scanner');
    });

    Route::middleware('permission:qr_scanner,scan')->group(function () {
        // QR API Routes
        Route::prefix('api/qr')->name('api.qr.')->group(function () {
            Route::post('/scan-for-transaction', [TransactionController::class, 'scanQR'])->name('scan-for-transaction');
            Route::post('/validate-transaction-qr', function (Request $request) {
                $request->validate(['qr_content' => 'required|json']);

                try {
                    $qrData = json_decode($request->qr_content, true);

                    if (!$qrData || $qrData['type'] !== 'item_detail') {
                        throw new \Exception('Invalid QR code type');
                    }

                    $itemDetail = \App\Models\ItemDetail::where('item_detail_id', $qrData['item_detail_id'])->first();

                    return response()->json([
                        'success' => true,
                        'valid' => (bool) $itemDetail,
                        'transaction_ready' => $itemDetail ? $itemDetail->isTransactionReady() : false
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
                    ], 400);
                }
            })->name('validate-transaction-qr');
        });
    });

    // ================================================================
    // SETTINGS (Admin Only)
    // ================================================================

    Route::middleware('permission:settings,read')->group(function () {
        Route::get('/system/transaction-settings', function () {
            return view('system.transaction-settings');
        })->name('system.transaction-settings');
        Route::get('/system/qr-settings', function () {
            return view('system.qr-settings');
        })->name('system.qr-settings');
    });

    Route::middleware('permission:settings,update')->group(function () {
        // Settings update routes bisa ditambahkan disini
    });

    // ================================================================
    // ITEM DETAILS MANAGEMENT
    // ================================================================

    Route::middleware('permission:items,read')->group(function () {
        Route::get('/item-details', [ItemDetailController::class, 'index'])->name('item-details.index');
        Route::get('/item-details/{itemDetail}', [ItemDetailController::class, 'show'])->name('item-details.show');
    });

    Route::middleware('permission:items,create')->group(function () {
        Route::get('/item-details/create', [ItemDetailController::class, 'create'])->name('item-details.create');
        Route::post('/item-details', [ItemDetailController::class, 'store'])->name('item-details.store');
        Route::post('/item-details/{itemDetail}/generate-qr', [ItemDetailController::class, 'generateQR'])->name('item-details.generate-qr');
    });

    Route::middleware('permission:items,update')->group(function () {
        Route::get('/item-details/{itemDetail}/edit', [ItemDetailController::class, 'edit'])->name('item-details.edit');
        Route::put('/item-details/{itemDetail}', [ItemDetailController::class, 'update'])->name('item-details.update');
        Route::patch('/item-details/{itemDetail}/status', [ItemDetailController::class, 'updateStatus'])->name('item-details.update-status');
        Route::patch('/item-details/{itemDetail}/attributes', [ItemDetailController::class, 'updateAttributes'])->name('item-details.update-attributes');
        Route::get('/item-details/{itemDetail}/print-qr', [ItemDetailController::class, 'printQR'])->name('item-details.print-qr');
        Route::get('/item-details/{itemDetail}/download-qr', [ItemDetailController::class, 'downloadQR'])->name('item-details.download-qr');
        Route::post('/item-details/bulk-print-labels', [ItemDetailController::class, 'bulkPrintLabels'])->name('item-details.bulk-print-labels');
        Route::post('/item-details/bulk-update-status', [ItemDetailController::class, 'bulkUpdateStatus'])->name('item-details.bulk-update-status');
        Route::post('/item-details/bulk-update-location', [ItemDetailController::class, 'bulkUpdateLocation'])->name('item-details.bulk-update-location');
        Route::post('/item-details/bulk-update-attributes', [ItemDetailController::class, 'bulkUpdateAttributes'])->name('item-details.bulk-update-attributes');
    });

    Route::middleware('permission:items,delete')->group(function () {
        Route::delete('/item-details/{itemDetail}', [ItemDetailController::class, 'destroy'])->name('item-details.destroy');
    });

    // Item Details QR Scanner
    Route::middleware('permission:qr_scanner,scan')->group(function () {
        Route::post('/item-details/scan-qr', [ItemDetailController::class, 'scanQR'])->name('item-details.scan-qr');
        Route::post('/item-details/scan', [ItemDetailController::class, 'apiScanQR'])->name('api.item-details.scan.qr');
        Route::get('/item-details/scan/{itemDetail}', [ItemDetailController::class, 'apiScanQR'])->name('api.item-details.scan');
        Route::patch('/item-details/{itemDetail}/transaction', [ItemDetailController::class, 'apiUpdateStatus'])->name('api.item-details.transaction.update');
    });

    // Item Details API Routes
    Route::prefix('item-details')->name('item-details.')->group(function () {
        Route::get('/ajax/attribute-templates/{categoryId}', [ItemDetailController::class, 'getAttributeTemplates'])->name('ajax.attribute-templates');
        Route::get('/ajax/locations', [ItemDetailController::class, 'getLocations'])->name('ajax.locations');
        Route::get('/{itemDetail}/transaction-options', [ItemDetailController::class, 'getTransactionOptions'])->name('transaction.options');
    });

    Route::prefix('api/item-details')->name('api.item-details.')->group(function () {
        Route::post('/bulk-update-status-from-stock', [ItemDetailController::class, 'bulkUpdateStatusFromStock'])->name('bulk-update-status-from-stock');
        Route::post('/{stock}/sync-item-details', [StockController::class, 'syncWithItemDetails'])->name('sync-item-details');
    });

    // ================================================================
    // LEVEL-SPECIFIC ROUTES
    // ================================================================

    // Admin & Logistik Only Routes
    Route::middleware(['auth', 'check.user.level:admin,logistik'])->group(function () {
        // Approval routes (advanced)
        Route::prefix('admin/approvals')->name('admin.approvals.')->group(function () {
            Route::get('/advanced-analytics', [ApprovalController::class, 'advancedAnalytics'])->name('advanced-analytics');
            Route::post('/system-override/{transaction}', [ApprovalController::class, 'systemOverride'])->name('system-override');
        });

        // Stock sync management (admin only functions)
        Route::prefix('admin/stocks')->name('admin.stocks.')->group(function () {
            Route::post('/force-sync-all', [StockController::class, 'forceSyncAll'])->name('force-sync-all');
            Route::get('/sync-logs', [StockController::class, 'getSyncLogs'])->name('sync-logs');
        });

        // Transaction system management
        Route::prefix('admin/transactions')->name('admin.transactions.')->group(function () {
            Route::get('/system-overview', [TransactionHistoryController::class, 'systemOverview'])->name('system-overview');
            Route::post('/cleanup-old-transactions', [TransactionHistoryController::class, 'cleanupOldTransactions'])->name('cleanup');
        });
    });

    // Teknisi Only Routes
    Route::middleware(['auth', 'check.user.level:teknisi'])->group(function () {
        // Quick access routes for field technicians
        Route::prefix('field')->name('field.')->group(function () {
            Route::get('/quick-scan', function () {
                return view('field.quick-scan');
            })->name('quick-scan');

            Route::get('/my-equipment', [RequestController::class, 'myItems'])->name('my-equipment');
            Route::post('/quick-return/{itemDetail}', [RequestController::class, 'quickReturn'])->name('quick-return');
        });
    });

    // Admin Only Routes
    Route::middleware(['auth', 'check.user.level:admin'])->group(function () {
        // System administration
        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/transaction-settings', function () {
                return view('system.transaction-settings');
            })->name('transaction-settings');

            Route::get('/qr-settings', function () {
                return view('system.qr-settings');
            })->name('qr-settings');
        });
    });

    // ================================================================
    // MOBILE/PWA ROUTES
    // ================================================================

    Route::prefix('mobile')->name('mobile.')->group(function () {
        Route::get('/scanner', function () {
            return view('mobile.scanner', ['layout' => 'mobile']);
        })->name('scanner');

        Route::get('/my-requests', function () {
            return redirect()->route('requests.index');
        })->name('my-requests');

        Route::get('/quick-actions', function () {
            return view('mobile.quick-actions');
        })->name('quick-actions');
    });

    // ================================================================
    // PUBLIC API ROUTES (untuk semua authenticated users)
    // ================================================================

    // My Activities - semua user bisa lihat aktivitas sendiri
    Route::get('/my-activities', [ActivityLogController::class, 'myActivities'])->name('my-activities');

    // Sidebar Dynamic Content
    Route::get('/api/sidebar/transaction-counts', function () {
        $user = auth()->user();
        $levelName = strtolower($user->getUserLevel()->level_name ?? '');

        $counts = [];

        if (in_array($levelName, ['admin', 'logistik'])) {
            $counts['pending_approvals'] = Transaction::pending()->count();
            $counts['approved_today'] = Transaction::approved()
                ->whereDate('approved_date', today())->count();
        }

        if ($levelName === 'teknisi') {
            $counts['my_pending'] = Transaction::where('created_by', $user->user_id)
                ->where('status', Transaction::STATUS_PENDING)->count();
            $counts['my_items'] = \App\Models\TransactionDetail::whereHas('transaction', function ($query) use ($user) {
                $query->where('created_by', $user->user_id)
                    ->where('status', Transaction::STATUS_APPROVED)
                    ->where('transaction_type', Transaction::TYPE_OUT);
            })
                ->whereHas('itemDetail', function ($query) {
                    $query->where('status', 'used');
                })->count();
        }

        return response()->json([
            'success' => true,
            'counts' => $counts,
            'user_level' => $levelName
        ]);
    })->name('api.sidebar.transaction-counts');

    // ================================================================
    // WEBHOOK ROUTES (untuk external system integration)
    // ================================================================

    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        // External system notifications
        Route::post('/transaction-update', function (Request $request) {
            $request->validate([
                'reference_id' => 'required|string',
                'status' => 'required|string',
                'notes' => 'nullable|string'
            ]);

            $transaction = Transaction::where('reference_id', $request->reference_id)->first();

            if ($transaction) {
                $transaction->notes = $transaction->notes . "\n\nExternal update: " . $request->notes;
                $transaction->save();

                return response()->json(['success' => true]);
            }

            return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
        })->name('transaction-update');

        // QR Code validation from external scanners
        Route::post('/validate-qr', function (Request $request) {
            $request->validate(['qr_content' => 'required|json']);

            try {
                $qrData = json_decode($request->qr_content, true);
                $itemDetail = \App\Models\ItemDetail::where('item_detail_id', $qrData['item_detail_id'])->first();

                return response()->json([
                    'valid' => (bool) $itemDetail,
                    'item_name' => $itemDetail ? $itemDetail->item->item_name : null,
                    'status' => $itemDetail ? $itemDetail->status : null
                ]);
            } catch (\Exception $e) {
                return response()->json(['valid' => false, 'error' => $e->getMessage()]);
            }
        })->name('validate-qr');
    });
});

// ================================================================
// API ROUTES (untuk AJAX calls, QR Scanner, dll)
// ================================================================

Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
    // User API
    Route::get('/users/search', [UserController::class, 'search'])->name('users.search');
    Route::get('/user-levels/list', [UserLevelController::class, 'list'])->name('user-levels.list');
});

// ================================================================
// ERROR HANDLING ROUTES
// ================================================================

// 404 Fallback
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

// ================================================================
// DEVELOPMENT ROUTES (hanya untuk testing - hapus di production)
// ================================================================

if (app()->environment('local')) {
    // Test route untuk cek auth
    Route::get('/test-auth', function () {
        $user = auth()->user();
        return response()->json([
            'authenticated' => auth()->check(),
            'user' => $user ? [
                'id' => $user->user_id,
                'username' => $user->username,
                'level' => $user->getLevelName(),
                'is_admin' => $user->isAdmin(),
                'is_active' => $user->isActive(),
            ] : null
        ]);
    })->name('test.auth');
}
