<?php

// ================================================================
// routes/web.php - Authentication & Main Routes
// ================================================================

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\GoodsReceivedController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemDetailController;
use App\Http\Controllers\PoDetailController;
use App\Http\Controllers\PurchaseOrderController;
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

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStats'])->name('stats');
        Route::get('/summary', [DashboardController::class, 'getSummary'])->name('summary');
    });
    // ================================================================
    // USER MANAGEMENT ROUTES
    // ================================================================

    // User Level Management
    Route::prefix('user-levels')->name('user-levels.')->group(function () {
        Route::get('/', [UserLevelController::class, 'index'])->name('index');
        Route::get('/create', [UserLevelController::class, 'create'])->name('create');
        Route::post('/', [UserLevelController::class, 'store'])->name('store');
        Route::get('/{userLevel}', [UserLevelController::class, 'show'])->name('show');
        Route::get('/{userLevel}/edit', [UserLevelController::class, 'edit'])->name('edit');
        Route::put('/{userLevel}', [UserLevelController::class, 'update'])->name('update');
        Route::delete('/{userLevel}', [UserLevelController::class, 'destroy'])->name('destroy');
    });

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');

        // Additional User Actions
        Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
    });

    // ================================================================
    // FUTURE ROUTES (untuk tahap selanjutnya)
    // ================================================================

    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::get('/{category}', [CategoryController::class, 'show'])->name('show');

        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');

        // Additional action routes - YANG MISSING
        Route::patch('/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('toggle-status');

        // API routes untuk AJAX calls
        Route::get('/api/children/{parent?}', [CategoryController::class, 'getChildren'])->name('children');
    });


    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::get('/create', [SupplierController::class, 'create'])->name('create');
        Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');


        // YANG KURANG - Additional actions
        Route::patch('/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('toggle-status');
    });

    Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
        Route::get('/suppliers/search', [SupplierController::class, 'search'])->name('suppliers.search');
        Route::get('/suppliers/list', [SupplierController::class, 'list'])->name('suppliers.list');
    });


    // Items
    Route::prefix('items')->name('items.')->group(function () {
        Route::get('/', [ItemController::class, 'index'])->name('index');
        Route::get('/create', [ItemController::class, 'create'])->name('create');
        Route::post('/', [ItemController::class, 'store'])->name('store');
        Route::get('/{item}', [ItemController::class, 'show'])->name('show');
        Route::get('/{item}/edit', [ItemController::class, 'edit'])->name('edit');
        Route::put('/{item}', [ItemController::class, 'update'])->name('update');
        Route::delete('/{item}', [ItemController::class, 'destroy'])->name('destroy');
        Route::patch('/{item}/toggle-status', [ItemController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{item}/generate-qr', [ItemController::class, 'generateQR'])->name('generate-qr');
        Route::get('/{item}/download-qr', [ItemController::class, 'downloadQR'])->name('download-qr');
    });

    // API Routes
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/items/search', [ItemController::class, 'search'])->name('items.search');
        Route::get('/items/by-category/{category}', [ItemController::class, 'getByCategory'])->name('items.by-category');
    });

    // Stock Management
    Route::middleware(['auth'])->prefix('stocks')->name('stocks.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/{stock}', [StockController::class, 'show'])->name('show');
        Route::get('/adjust/form', [StockController::class, 'adjust'])->name('adjust');
        Route::post('/adjustment', [StockController::class, 'adjustment'])->name('adjustment');
        Route::post('/bulk-adjustment', [StockController::class, 'bulkAdjustment'])->name('bulk-adjustment');
    });

    // Stock API
    Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
        Route::get('/stocks/check/{item}', [StockController::class, 'checkStock'])->name('stocks.check');
        Route::get('/stocks/low-stock', [StockController::class, 'getLowStockItems'])->name('stocks.low-stock');
    });


    // Activity Logs (Admin Only)
    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::get('/dashboard', [ActivityLogController::class, 'dashboard'])->name('dashboard');
        Route::get('/export', [ActivityLogController::class, 'export'])->name('export');
        Route::get('/{activityLog}', [ActivityLogController::class, 'show'])->name('show');
    });

    // My Activities (All Users)
    Route::middleware(['auth'])->group(function () {
        Route::get('/my-activities', [ActivityLogController::class, 'myActivities'])->name('my-activities');
    });

    // Real-time API
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/activity-logs/realtime', [ActivityLogController::class, 'realtimeStats'])->name('activity-logs.realtime');
    });

    // Purchase Orders
    Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseOrderController::class, 'create'])->name('create');
        Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
        Route::get('/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('show');
        Route::get('/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('edit');
        Route::put('/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('update');
        Route::get('/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])->name('print');
        Route::post('/{purchaseOrder}/duplicate', [PurchaseOrderController::class, 'duplicate'])->name('duplicate');
        Route::patch('/{purchaseOrder}/update-status', [PurchaseOrderController::class, 'updateStatus'])->name('update-status');
        Route::post('/{purchaseOrder}/send', [PurchaseOrderController::class, 'send'])->name('update-send');
        Route::post('/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('cancel');
    });

    // PO Details API
    Route::prefix('api/po-details')->name('api.po-details.')->group(function () {
        Route::post('/{purchaseOrder}/add-item', [PoDetailController::class, 'addItem'])->name('add-item');
        Route::put('/{poDetail}', [PoDetailController::class, 'update'])->name('update');
        Route::delete('/{poDetail}', [PoDetailController::class, 'destroy'])->name('destroy');
        Route::get('/{purchaseOrder}/details', [PoDetailController::class, 'getDetails'])->name('get-details');
    });

    // Purchase Order API
    Route::prefix('api/purchase-orders')->name('api.purchase-orders.')->group(function () {
        Route::get('/by-supplier/{supplier}', [PurchaseOrderController::class, 'getBySupplier'])->name('by-supplier');
    });


    Route::prefix('api/goods-received')->name('api.goods-received.')->group(function () {
        Route::get('/po-details/{po}', [GoodsReceivedController::class, 'getPODetails'])->name('po-details');
    });
    // Goods Received
    Route::middleware(['auth'])->prefix('goods-received')->name('goods-received.')->group(function () {
        Route::get('/', [GoodsReceivedController::class, 'index'])->name('index');
        Route::get('/create', [GoodsReceivedController::class, 'create'])->name('create');
        Route::post('/', [GoodsReceivedController::class, 'store'])->name('store');
        Route::get('/{goodsReceived}', [GoodsReceivedController::class, 'show'])->name('show');
        Route::get('/{goodsReceived}/edit', [GoodsReceivedController::class, 'edit'])->name('edit');
        Route::put('/{goodsReceived}', [GoodsReceivedController::class, 'update'])->name('update');
    });



    // Item Details Management Routes
    Route::prefix('item-details')->name('item-details.')->group(function () {
        // Basic CRUD
        Route::get('/', [ItemDetailController::class, 'index'])->name('index');
        Route::get('/create', [ItemDetailController::class, 'create'])->name('create');
        Route::post('/', [ItemDetailController::class, 'store'])->name('store');
        Route::get('/{itemDetail}', [ItemDetailController::class, 'show'])->name('show');
        Route::get('/{itemDetail}/edit', [ItemDetailController::class, 'edit'])->name('edit');
        Route::put('/{itemDetail}', [ItemDetailController::class, 'update'])->name('update');
        Route::delete('/{itemDetail}', [ItemDetailController::class, 'destroy'])->name('destroy');

        // Status Management
        Route::patch('/{itemDetail}/status', [ItemDetailController::class, 'updateStatus'])->name('update-status');

        // Custom Attributes Management
        Route::patch('/{itemDetail}/attributes', [ItemDetailController::class, 'updateAttributes'])->name('update-attributes');

        // QR Code Management
        Route::post('/scan-qr', [ItemDetailController::class, 'scanQR'])->name('scan-qr');
        Route::post('/{itemDetail}/generate-qr', [ItemDetailController::class, 'generateQR'])->name('generate-qr');
        Route::get('/{itemDetail}/print-qr', [ItemDetailController::class, 'printQR'])->name('print-qr');
        Route::get('/{itemDetail}/download-qr', [ItemDetailController::class, 'downloadQR'])->name('download-qr');

        // Bulk Actions
        Route::post('/bulk-print-labels', [ItemDetailController::class, 'bulkPrintLabels'])->name('bulk-print-labels');
        Route::post('/bulk-update-status', [ItemDetailController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
        Route::post('/bulk-update-location', [ItemDetailController::class, 'bulkUpdateLocation'])->name('bulk-update-location');
        Route::post('/bulk-update-attributes', [ItemDetailController::class, 'bulkUpdateAttributes'])->name('bulk-update-attributes');

        // AJAX endpoints for dynamic functionality
        Route::get('/ajax/attribute-templates/{categoryId}', [ItemDetailController::class, 'getAttributeTemplates'])->name('ajax.attribute-templates');
        Route::get('/ajax/locations', [ItemDetailController::class, 'getLocations'])->name('ajax.locations');
    });


    Route::prefix('item-details')->name('api.item-details.')->group(function () {
        // Scan QR Code - return JSON untuk transaksi
        Route::post('/scan', [ItemDetailController::class, 'apiScanQR'])->name('scan.qr');
        Route::get('/scan/{itemDetail}', [ItemDetailController::class, 'apiScanQR'])->name('scan');

        // Update status via QR transaction
        Route::patch('/{itemDetail}/transaction', [ItemDetailController::class, 'apiUpdateStatus'])->name('transaction.update');

        // Get transaction options for specific item
        Route::get('/{itemDetail}/transaction-options', [ItemDetailController::class, 'getTransactionOptions'])->name('transaction.options');
    });


      Route::middleware(['auth', 'check.user.level:admin,logistik'])->group(function () {

        // Approval routes (sudah ada di atas, bisa dipindah kesini jika mau strict)
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
            Route::get('/quick-scan', function() {
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
            Route::get('/transaction-settings', function() {
                return view('system.transaction-settings');
            })->name('transaction-settings');

            Route::get('/qr-settings', function() {
                return view('system.qr-settings');
            })->name('qr-settings');
        });
    });

    // ================================================================
    // DYNAMIC SIDEBAR ROUTES (untuk load sidebar content)
    // ================================================================

    Route::get('/api/sidebar/transaction-counts', function() {
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
            $counts['my_items'] = \App\Models\TransactionDetail::whereHas('transaction', function($query) use ($user) {
                    $query->where('created_by', $user->user_id)
                          ->where('status', Transaction::STATUS_APPROVED)
                          ->where('transaction_type', Transaction::TYPE_OUT);
                })
                ->whereHas('itemDetail', function($query) {
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
    // MOBILE/PWA ROUTES (jika diperlukan untuk mobile app)
    // ================================================================

    Route::prefix('mobile')->name('mobile.')->group(function () {
        Route::get('/scanner', function() {
            return view('mobile.scanner', ['layout' => 'mobile']);
        })->name('scanner');

        Route::get('/my-requests', function() {
            return redirect()->route('requests.index');
        })->name('my-requests');

        Route::get('/quick-actions', function() {
            return view('mobile.quick-actions');
        })->name('quick-actions');
    });

    // ================================================================
    // WEBHOOK ROUTES (untuk external system integration)
    // ================================================================

    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        // External system notifications
        Route::post('/transaction-update', function(Request $request) {
            // Handle external system transaction updates
            // This could be from ERP, CMMS, etc.

            $request->validate([
                'reference_id' => 'required|string',
                'status' => 'required|string',
                'notes' => 'nullable|string'
            ]);

            // Find transaction by reference_id
            $transaction = Transaction::where('reference_id', $request->reference_id)->first();

            if ($transaction) {
                $transaction->notes = $transaction->notes . "\n\nExternal update: " . $request->notes;
                $transaction->save();

                return response()->json(['success' => true]);
            }

            return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
        })->name('transaction-update');

        // QR Code validation from external scanners
        Route::post('/validate-qr', function(Request $request) {
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

       // TRANSACTION MANAGEMENT ROUTES
    // ================================================================

    // Main Transaction Management
    Route::prefix('transactions')->name('transactions.')->group(function () {
        // Basic CRUD
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('/create', [TransactionController::class, 'create'])->name('create');
        Route::post('/', [TransactionController::class, 'store'])->name('store');
        Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
        Route::get('/{transaction}/edit', [TransactionController::class, 'edit'])->name('edit');
        Route::put('/{transaction}', [TransactionController::class, 'update'])->name('update');

        // Transaction Actions
        Route::post('/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('cancel');
        Route::get('/history', [TransactionController::class, 'history'])->name('history');
        Route::get('/export', [TransactionController::class, 'export'])->name('export');
    });

    // Transaction API Routes
    Route::prefix('api/transactions')->name('api.transactions.')->group(function () {
        // QR Integration
        Route::post('/create-from-qr', [TransactionController::class, 'createFromQR'])->name('create-from-qr');
        Route::post('/scan-qr', [TransactionController::class, 'scanQR'])->name('scan-qr');

        // Helper APIs
        Route::get('/available-items', [TransactionController::class, 'getAvailableItems'])->name('available-items');
        Route::get('/types', function() {
            return response()->json(Transaction::getUserAllowedTypes());
        })->name('types');
    });

    // ================================================================
    // APPROVAL MANAGEMENT ROUTES (Admin & Logistik Only)
    // ================================================================

    Route::prefix('approvals')->name('approvals.')->group(function () {
        // Approval Dashboard
        Route::get('/', [ApprovalController::class, 'index'])->name('index');
        Route::get('/{transaction}', [ApprovalController::class, 'show'])->name('show');

        // Single Approval Actions
        Route::post('/{transaction}/approve', [ApprovalController::class, 'approve'])->name('approve');
        Route::post('/{transaction}/reject', [ApprovalController::class, 'reject'])->name('reject');
        Route::post('/{transaction}/quick-approve', [ApprovalController::class, 'quickApprove'])->name('quick-approve');

        // Bulk Actions
        Route::post('/bulk-approve', [ApprovalController::class, 'bulkApprove'])->name('bulk-approve');
        Route::post('/bulk-reject', [ApprovalController::class, 'bulkReject'])->name('bulk-reject');

        // Approval History & Analytics
        Route::get('/history', [ApprovalController::class, 'history'])->name('history');
        Route::get('/analytics', [ApprovalController::class, 'analytics'])->name('analytics');
    });

    // Approval API Routes
    Route::prefix('api/approvals')->name('api.approvals.')->group(function () {
        Route::get('/summary', [ApprovalController::class, 'getSummary'])->name('summary');
        Route::get('/{transaction}/details', [ApprovalController::class, 'getTransactionDetails'])->name('details');
    });

    // ================================================================
    // REQUEST MANAGEMENT ROUTES (Teknisi)
    // ================================================================

    Route::prefix('requests')->name('requests.')->group(function () {
        // Request Dashboard
        Route::get('/', [RequestController::class, 'index'])->name('index');
        Route::get('/create', [RequestController::class, 'create'])->name('create');
        Route::post('/', [RequestController::class, 'store'])->name('store');
        Route::get('/{transaction}', [RequestController::class, 'show'])->name('show');
        Route::post('/{transaction}/cancel', [RequestController::class, 'cancel'])->name('cancel');

        // Request History & My Items
        Route::get('/history', [RequestController::class, 'history'])->name('history');
        Route::get('/my-items', [RequestController::class, 'myItems'])->name('my-items');
        Route::post('/return-item', [RequestController::class, 'returnItem'])->name('return-item');
    });

    // Enhanced search routes
    Route::prefix('api/requests')->name('api.requests.')->group(function () {
        // Existing routes...
        Route::get('/quick-request', [RequestController::class, 'quickRequest'])->name('quick-request');
        Route::get('/items-by-category', [RequestController::class, 'getItemsByCategory'])->name('items-by-category');
        Route::get('/search-items', [RequestController::class, 'searchItems'])->name('search-items');

        // Additional enhanced routes
        Route::get('/categories', [RequestController::class, 'getCategories'])->name('categories');
        Route::get('/item-suggestions', [RequestController::class, 'getItemSuggestions'])->name('item-suggestions');
    });
    // ================================================================
    // TRANSACTION HISTORY & REPORTS ROUTES
    // ================================================================

    Route::prefix('transaction-history')->name('transaction-history.')->group(function () {
        // History Dashboard
        Route::get('/', [TransactionHistoryController::class, 'index'])->name('index');
        Route::get('/{transaction}', [TransactionHistoryController::class, 'show'])->name('show');

        // Reports & Analytics
        Route::get('/analytics', [TransactionHistoryController::class, 'analytics'])->name('analytics');
        Route::post('/report', [TransactionHistoryController::class, 'report'])->name('report');
        Route::get('/alerts', [TransactionHistoryController::class, 'alerts'])->name('alerts');
        Route::get('/stock-movement', [TransactionHistoryController::class, 'stockMovement'])->name('stock-movement');
    });

    // Transaction History API Routes
    Route::prefix('api/transaction-history')->name('api.transaction-history.')->group(function () {
        Route::get('/item-timeline', [TransactionHistoryController::class, 'itemTimeline'])->name('item-timeline');
    });

    // ================================================================
    // STOCK SYNC ROUTES (Enhancement to existing stock routes)
    // ================================================================

    // Add to existing stocks routes group
    Route::prefix('stocks')->name('stocks.')->group(function () {
        // Existing routes remain unchanged...

        // New sync routes
        Route::post('/sync-all', [StockController::class, 'syncAll'])->name('sync-all');
        Route::post('/{stock}/sync', [StockController::class, 'syncStock'])->name('sync');
        Route::get('/inconsistencies', [StockController::class, 'inconsistencies'])->name('inconsistencies');
        Route::post('/auto-fix-inconsistencies', [StockController::class, 'autoFixInconsistencies'])->name('auto-fix-inconsistencies');
    });

    // Stock Sync API Routes
    Route::prefix('api/stocks')->name('api.stocks.')->group(function () {
        // Existing API routes remain unchanged...

        // New sync API routes
        Route::get('/{stock}/validate', [StockController::class, 'validateConsistency'])->name('validate');
        Route::get('/{stock}/movement-summary', [StockController::class, 'getMovementSummary'])->name('movement-summary');
        Route::get('/dashboard-summary', [StockController::class, 'getDashboardSummary'])->name('dashboard-summary');
    });

    // ================================================================
    // QR SCANNER ROUTES (Enhancement)
    // ================================================================

    Route::prefix('qr')->name('qr.')->group(function () {
        // Transaction QR Scanner
        Route::get('/transaction-scanner', function() {
            return view('qr.transaction-scanner');
        })->name('transaction-scanner');

        // Item QR Scanner
        Route::get('/item-scanner', function() {
            return view('qr.item-scanner');
        })->name('item-scanner');
    });

    // QR API Routes
    Route::prefix('api/qr')->name('api.qr.')->group(function () {
        // Transaction QR Processing
        Route::post('/scan-for-transaction', [TransactionController::class, 'scanQR'])->name('scan-for-transaction');
        Route::post('/validate-transaction-qr', function(Request $request) {
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


    // Goods Received API


    /*
    // Data Master Routes




    Route::prefix('items')->name('items.')->group(function () {
        Route::get('/', [ItemController::class, 'index'])->name('index');
        Route::get('/create', [ItemController::class, 'create'])->name('create');
        Route::post('/', [ItemController::class, 'store'])->name('store');
        Route::get('/{item}', [ItemController::class, 'show'])->name('show');
        Route::get('/{item}/edit', [ItemController::class, 'edit'])->name('edit');
        Route::put('/{item}', [ItemController::class, 'update'])->name('update');
        Route::delete('/{item}', [ItemController::class, 'destroy'])->name('destroy');
    });

    // Purchase & Receiving Routes
    Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseOrderController::class, 'create'])->name('create');
        Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
        Route::get('/{po}', [PurchaseOrderController::class, 'show'])->name('show');
        Route::get('/{po}/edit', [PurchaseOrderController::class, 'edit'])->name('edit');
        Route::put('/{po}', [PurchaseOrderController::class, 'update'])->name('update');
        Route::post('/{po}/receive', [PurchaseOrderController::class, 'receive'])->name('receive');
    });

    // Inventory Routes
    Route::prefix('stocks')->name('stocks.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/adjust', [StockController::class, 'adjust'])->name('adjust');
        Route::post('/adjustment', [StockController::class, 'adjustment'])->name('adjustment');
    });

    // Transaction Routes
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('/create', [TransactionController::class, 'create'])->name('create');
        Route::post('/', [TransactionController::class, 'store'])->name('store');
        Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
        Route::post('/{transaction}/approve', [TransactionController::class, 'approve'])->name('approve');
        Route::post('/{transaction}/reject', [TransactionController::class, 'reject'])->name('reject');
    });

    // Report Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/stocks', [ReportController::class, 'stocks'])->name('stocks');
        Route::get('/transactions', [ReportController::class, 'transactions'])->name('transactions');
        Route::get('/purchase-orders', [ReportController::class, 'purchaseOrders'])->name('purchase-orders');
    });

    // QR Code Routes
    Route::prefix('qr')->name('qr.')->group(function () {
        Route::get('/scanner', [QRCodeController::class, 'scanner'])->name('scanner');
        Route::post('/scan', [QRCodeController::class, 'scan'])->name('scan');
        Route::get('/generate/{item}', [QRCodeController::class, 'generate'])->name('generate');
    });

});

// ================================================================
// API ROUTES (untuk AJAX calls, QR Scanner, dll)
// ================================================================

Route::middleware('auth')->prefix('api')->name('api.')->group(function () {

    // User API
    Route::get('/users/search', [UserController::class, 'search'])->name('users.search');
    Route::get('/user-levels/list', [UserLevelController::class, 'list'])->name('user-levels.list');

    // Future API endpoints
    /*
    Route::get('/items/search', [ItemController::class, 'search'])->name('items.search');
    Route::get('/suppliers/list', [SupplierController::class, 'list'])->name('suppliers.list');
    Route::post('/qr/scan', [QRCodeController::class, 'apiScan'])->name('qr.scan');
    Route::get('/stocks/check/{item}', [StockController::class, 'checkStock'])->name('stocks.check');
    */
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
