<?php


// ================================================================
// 2. app/Http/Controllers/StockController.php
// ================================================================

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
        // Nanti bisa ditambah permission middleware
        // $this->middleware('permission:stocks.read')->only(['index', 'show']);
        // $this->middleware('permission:stocks.adjust')->only(['adjust', 'adjustment']);
    }

    // Tampilkan overview stock
    public function index(Request $request)
    {
        $query = Stock::with(['item.category']);

        // Filter by category
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Filter by stock status - FIXED: tambah 'sufficient'
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'low':
                    $query->lowStock();
                    break;
                case 'out':
                    $query->outOfStock();
                    break;
                case 'available':
                    $query->available();
                    break;
                case 'sufficient':  // ✅ FIX 1: Tambah case sufficient
                    $query->sufficientStock();
                    break;
            }
        }

        // Search by item name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'last_updated');
        $sortDirection = $request->get('direction', 'desc');

        $allowedSorts = ['quantity_available', 'quantity_used', 'total_quantity', 'last_updated'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            // Sort by item name
            $query->join('items', 'stocks.item_id', '=', 'items.item_id')
                ->orderBy('items.item_name', $sortDirection)
                ->select('stocks.*');
        }

        $stocks = $query->paginate(15)->withQueryString();

        // ✅ FIX 2: Ganti dengan fixed method
        $summary = Stock::getStockSummary(); // Ini akan pakai method yang sudah diperbaiki

        // Categories untuk filter
        $categories = Category::active()
            ->whereHas('items.stock')
            ->withCount(['items' => function ($q) {
                $q->whereHas('stock');
            }])
            ->orderBy('category_name')
            ->get();

        // ✅ FIX 3: Enhanced low stock alerts dengan limit safety
        $lowStockAlerts = Stock::lowStock()
            ->with('item')
            ->where('quantity_available', '>', 0) // Pastikan ada stock
            ->orderBy('quantity_available')
            ->take(5)
            ->get();

        return view('stocks.index', compact(
            'stocks',
            'summary',
            'categories',
            'lowStockAlerts',
            'sortField',
            'sortDirection'
        ));
    }

    // Tampilkan detail stock item
    public function show(Stock $stock)
    {
        $stock->load(['item.category']);

        // Stock status info
        $statusInfo = $stock->getStockStatus();

        // Recent movements (dari log nanti)
        $recentMovements = []; // Placeholder untuk StockMovement model

        return view('stocks.show', compact('stock', 'statusInfo', 'recentMovements'));
    }

    // Tampilkan form stock adjustment
    public function adjust(Request $request)
    {
        $stockId = $request->get('stock_id');
        $itemId = $request->get('item_id');

        if ($stockId) {
            $stock = Stock::with('item')->findOrFail($stockId);
            $item = $stock->item;
        } elseif ($itemId) {
            $item = Item::with('stock')->findOrFail($itemId);
            $stock = $item->stock;
        } else {
            // Bulk adjustment
            $items = Item::with('stock')->active()->orderBy('item_name')->get();
            return view('stocks.bulk-adjust', compact('items'));
        }

        return view('stocks.adjust', compact('stock', 'item'));
    }

    // Process stock adjustment
    public function adjustment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stock_id' => 'required|string|exists:stocks,stock_id',
            'adjustment_type' => 'required|in:add,reduce,adjust,return',
            'quantity' => 'required|integer|min:1',
            'new_available' => 'nullable|integer|min:0',
            'new_used' => 'nullable|integer|min:0',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ], [
            'stock_id.required' => 'Stock ID wajib diisi.',
            'stock_id.exists' => 'Stock tidak ditemukan.',
            'adjustment_type.required' => 'Tipe adjustment wajib dipilih.',
            'quantity.required' => 'Jumlah wajib diisi.',
            'quantity.min' => 'Jumlah minimal 1.',
            'reason.required' => 'Alasan adjustment wajib diisi.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $stock = Stock::findOrFail($request->stock_id);
            $userId = Auth::id();

            DB::beginTransaction();

            $success = false;
            $message = '';

            switch ($request->adjustment_type) {
                case 'add':
                    $success = $stock->addStock($request->quantity, $request->reason, $userId);
                    $message = "Stok berhasil ditambah sebanyak {$request->quantity} {$stock->item->unit}";
                    break;

                case 'reduce':
                    $success = $stock->reduceStock($request->quantity, $request->reason, $userId);
                    $message = "Stok berhasil dikurangi sebanyak {$request->quantity} {$stock->item->unit}";
                    break;

                case 'adjust':
                    $success = $stock->adjustStock(
                        $request->new_available,
                        $request->new_used,
                        $request->reason,
                        $userId
                    );
                    $message = "Stok berhasil disesuaikan";
                    break;

                case 'return':
                    $success = $stock->returnStock($request->quantity, $request->reason, $userId);
                    $message = "Stok berhasil dikembalikan sebanyak {$request->quantity} {$stock->item->unit}";
                    break;
            }

            if (!$success) {
                throw new \Exception('Gagal melakukan adjustment stock');
            }

            // Log activity
            $this->logActivity('stocks', $stock->stock_id, 'adjustment', null, [
                'adjustment_type' => $request->adjustment_type,
                'quantity' => $request->quantity,
                'reason' => $request->reason,
                'notes' => $request->notes
            ]);

            DB::commit();

            return redirect()->route('stocks.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Gagal melakukan adjustment: ' . $e->getMessage());
        }
    }

    // Bulk stock adjustment
    public function bulkAdjustment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'adjustments' => 'required|array|min:1',
            'adjustments.*.stock_id' => 'required|string|exists:stocks,stock_id',
            'adjustments.*.new_available' => 'required|integer|min:0',
            'adjustments.*.new_used' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
        ], [
            'adjustments.required' => 'Data adjustment wajib diisi.',
            'adjustments.min' => 'Minimal 1 item yang di-adjust.',
            'reason.required' => 'Alasan adjustment wajib diisi.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $userId = Auth::id();
            $successCount = 0;

            foreach ($request->adjustments as $adjustment) {
                $stock = Stock::find($adjustment['stock_id']);
                if ($stock) {
                    $success = $stock->adjustStock(
                        $adjustment['new_available'],
                        $adjustment['new_used'],
                        $request->reason,
                        $userId
                    );

                    if ($success) {
                        $successCount++;
                    }
                }
            }

            // Log activity
            $this->logActivity('stocks', 'bulk', 'bulk_adjustment', null, [
                'total_items' => count($request->adjustments),
                'success_count' => $successCount,
                'reason' => $request->reason
            ]);

            DB::commit();

            return redirect()->route('stocks.index')
                ->with('success', "Berhasil melakukan bulk adjustment pada {$successCount} item");
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Gagal melakukan bulk adjustment: ' . $e->getMessage());
        }
    }

    // API endpoint untuk check stock
    public function checkStock(Request $request, $itemId)
    {
        $stock = Stock::with('item')
            ->where('item_id', $itemId)
            ->first();

        if (!$stock) {
            return response()->json([
                'error' => 'Stock tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'stock_id' => $stock->stock_id,
            'item_id' => $stock->item_id,
            'item_code' => $stock->item->item_code,
            'item_name' => $stock->item->item_name,
            'unit' => $stock->item->unit,
            'quantity_available' => $stock->quantity_available,
            'quantity_used' => $stock->quantity_used,
            'total_quantity' => $stock->total_quantity,
            'min_stock' => $stock->item->min_stock,
            'status' => $stock->getStockStatus(),
            'is_low_stock' => $stock->isLowStock(),
            'is_out_of_stock' => $stock->isOutOfStock(),
            'last_updated' => $stock->last_updated?->format('Y-m-d H:i:s')
        ]);
    }

    // API endpoint untuk get low stock items
    public function getLowStockItems(Request $request)
    {
        $limit = $request->get('limit', 10);

        $lowStockItems = Stock::lowStock()
            ->with('item.category')
            ->orderBy('quantity_available')
            ->take($limit)
            ->get()
            ->map(function ($stock) {
                return [
                    'stock_id' => $stock->stock_id,
                    'item_code' => $stock->item->item_code,
                    'item_name' => $stock->item->item_name,
                    'category_name' => $stock->item->category->category_name,
                    'quantity_available' => $stock->quantity_available,
                    'min_stock' => $stock->item->min_stock,
                    'unit' => $stock->item->unit,
                    'status' => $stock->getStockStatus()
                ];
            });

        return response()->json($lowStockItems);
    }
    // **NEW: Show edit form**
    public function edit(Stock $stock)
    {
        $stock->load(['item.category', 'item.itemDetails']);

        // Get current item details breakdown
        $itemDetailsBreakdown = $this->getItemDetailsBreakdown($stock);

        // Get sync status
        $syncStatus = $stock->validateConsistency();
        // dd($syncStatus['actual_from_item_details']);
        // dd($stock);

        return view('stocks.edit', compact('stock', 'itemDetailsBreakdown', 'syncStatus'));
    }





    // ================================================================
    // DEBUG HELPER untuk StockController.php
    // Tambahkan method ini di StockController
    // ================================================================


/**
 * FIXED: Process specific items based on user selection
 */
public function syncItemDetailsWithStockDebug(Stock $stock, Request $request)
{
    try {
        $item = $stock->item;
        if (!$item) {
            throw new \Exception('Item not found');
        }

        // ✅ FIXED: Get changed items from request
        $changedItemsJson = $request->input('changed_items');
        $changedItems = $changedItemsJson ? json_decode($changedItemsJson, true) : [];

        Log::info('🎯 Processing specific item changes', [
            'stock_id' => $stock->stock_id,
            'changed_items_count' => count($changedItems),
            'changed_items' => $changedItems
        ]);

        // Get fresh item details
        $itemDetails = DB::table('item_details')
            ->where('item_id', $item->item_id)
            ->get();

        $targetStock = (int) ($request->quantity_available ?? $stock->quantity_available);
        $targetAvailable = (int) ($request->quantity_used ?? $stock->quantity_used);

        $currentStock = $itemDetails->where('status', 'stock')->count();
        $currentAvailable = $itemDetails->where('status', 'available')->count();

        Log::info('📊 Sync calculation with user selection', [
            'stock_id' => $stock->stock_id,
            'current_status' => [
                'stock' => $currentStock,
                'available' => $currentAvailable
            ],
            'targets' => [
                'target_stock' => $targetStock,
                'target_available' => $targetAvailable
            ],
            'user_selection' => [
                'has_changed_items' => !empty($changedItems),
                'will_use_specific_items' => !empty($changedItems)
            ]
        ]);

        $updated = 0;
        $changes = [];

        if (!empty($changedItems)) {
            // ✅ FIXED: Process specific items selected by user
            Log::info('🎯 Processing user-selected items');

            foreach ($changedItems as $changeRequest) {
                $itemDetailId = $changeRequest['item_detail_id'];
                $serialNumber = $changeRequest['serial_number'];
                $oldStatus = $changeRequest['old_status'];
                $newStatus = $changeRequest['new_status'];

                // Verify item exists and has correct current status
                $itemDetail = $itemDetails->where('item_detail_id', $itemDetailId)->first();

                if (!$itemDetail) {
                    Log::warning("⚠️ Item detail not found: {$itemDetailId}");
                    continue;
                }

                if ($itemDetail->status !== $oldStatus) {
                    Log::warning("⚠️ Status mismatch for {$serialNumber}: expected {$oldStatus}, got {$itemDetail->status}");
                    // Continue anyway, but log the discrepancy
                }

                // Apply the specific change
                $updateResult = DB::table('item_details')
                    ->where('item_detail_id', $itemDetailId)
                    ->update([
                        'status' => $newStatus,
                        'location' => $newStatus === 'stock' ? 'Warehouse - Stock' : 'Office - Ready',
                        'notes' => "User-selected change: {$oldStatus} → {$newStatus}. Reason: " . ($request->reason ?? 'Manual adjustment'),
                        'updated_at' => now()
                    ]);

                if ($updateResult) {
                    $updated++;
                    $changes[] = "{$serialNumber}: {$oldStatus} → {$newStatus} (user-selected)";

                    Log::info("✅ Updated specific item", [
                        'serial_number' => $serialNumber,
                        'item_detail_id' => $itemDetailId,
                        'status_change' => "{$oldStatus} → {$newStatus}"
                    ]);
                } else {
                    Log::warning("❌ Failed to update item: {$serialNumber}");
                }
            }

        } else {
            // ✅ FALLBACK: Auto-select items if no specific selection (original logic)
            Log::info('🔄 No specific selection, using auto-selection');

            $needMoveToStock = $targetStock - $currentStock;
            $needMoveToAvailable = $targetAvailable - $currentAvailable;

            // Move items from 'available' to 'stock' status
            if ($needMoveToStock > 0) {
                $itemsToMove = $itemDetails->where('status', 'available')->take($needMoveToStock);

                Log::info("📦 Auto-moving {$itemsToMove->count()} items from available to stock", [
                    'items' => $itemsToMove->pluck('serial_number')->toArray()
                ]);

                foreach ($itemsToMove as $itemDetail) {
                    DB::table('item_details')
                        ->where('item_detail_id', $itemDetail->item_detail_id)
                        ->update([
                            'status' => 'stock',
                            'location' => 'Warehouse - Stock',
                            'notes' => 'Auto-moved to stock: ' . ($request->reason ?? 'Sync'),
                            'updated_at' => now()
                        ]);

                    $updated++;
                    $changes[] = "{$itemDetail->serial_number}: available → stock (auto-selected)";
                }
            }

            // Move items from 'stock' to 'available' status
            if ($needMoveToAvailable > 0) {
                $itemsToMove = $itemDetails->where('status', 'stock')->take($needMoveToAvailable);

                Log::info("🏢 Auto-moving {$itemsToMove->count()} items from stock to available", [
                    'items' => $itemsToMove->pluck('serial_number')->toArray()
                ]);

                foreach ($itemsToMove as $itemDetail) {
                    DB::table('item_details')
                        ->where('item_detail_id', $itemDetail->item_detail_id)
                        ->update([
                            'status' => 'available',
                            'location' => 'Office - Ready',
                            'notes' => 'Auto-moved to available: ' . ($request->reason ?? 'Sync'),
                            'updated_at' => now()
                        ]);

                    $updated++;
                    $changes[] = "{$itemDetail->serial_number}: stock → available (auto-selected)";
                }
            }
        }

        // ✅ VERIFICATION: Check final state
        $verifyItemDetails = DB::table('item_details')
            ->where('item_id', $item->item_id)
            ->get();

        $verifyStock = $verifyItemDetails->where('status', 'stock')->count();
        $verifyAvailable = $verifyItemDetails->where('status', 'available')->count();

        $stockMatches = ((int) $targetStock) === ((int) $verifyStock);
        $availableMatches = ((int) $targetAvailable) === ((int) $verifyAvailable);

        Log::info('✅ Item details sync completed', [
            'stock_id' => $stock->stock_id,
            'processing_method' => !empty($changedItems) ? 'user-specific' : 'auto-selection',
            'updated_items' => $updated,
            'changes' => $changes,
            'verification' => [
                'target_stock' => $targetStock,
                'actual_stock' => $verifyStock,
                'target_available' => $targetAvailable,
                'actual_available' => $verifyAvailable,
                'stock_matches' => $stockMatches,
                'available_matches' => $availableMatches,
                'success' => $stockMatches && $availableMatches
            ]
        ]);

        // ✅ VALIDATION: Check if targets were met
        if (!$stockMatches || !$availableMatches) {
            Log::warning('⚠️ Target quantities not met after sync', [
                'stock_id' => $stock->stock_id,
                'expected_vs_actual' => [
                    'stock' => "{$targetStock} vs {$verifyStock}",
                    'available' => "{$targetAvailable} vs {$verifyAvailable}"
                ],
                'possible_cause' => !empty($changedItems) ? 'User selection insufficient for targets' : 'Auto-selection logic issue'
            ]);
        }

        return $updated;
    } catch (\Exception $e) {
        Log::error('❌ Item details sync failed', [
            'stock_id' => $stock->stock_id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}

/**
 * NEW: Debug method untuk validate user selection vs target
 */
public function validateUserSelection(Request $request)
{
    try {
        $changedItemsJson = $request->input('changed_items');
        $changedItems = $changedItemsJson ? json_decode($changedItemsJson, true) : [];

        $targetStock = (int) $request->quantity_available;
        $targetAvailable = (int) $request->quantity_used;

        // Count expected changes
        $toStockCount = 0;
        $toAvailableCount = 0;

        foreach ($changedItems as $change) {
            if ($change['old_status'] === 'available' && $change['new_status'] === 'stock') {
                $toStockCount++;
            } elseif ($change['old_status'] === 'stock' && $change['new_status'] === 'available') {
                $toAvailableCount++;
            }
        }

        $analysis = [
            'user_selection' => [
                'total_changes' => count($changedItems),
                'to_stock_count' => $toStockCount,
                'to_available_count' => $toAvailableCount,
                'changes_detail' => $changedItems
            ],
            'targets' => [
                'target_stock' => $targetStock,
                'target_available' => $targetAvailable
            ],
            'validation' => [
                'selection_sufficient' => true, // Basic check, can be enhanced
                'changes_align_with_targets' => true // Basic check, can be enhanced
            ]
        ];

        Log::info('🔍 User selection validation', $analysis);

        return response()->json([
            'success' => true,
            'analysis' => $analysis
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * FIXED: Update method dengan proper integer casting
 */
public function update(Request $request, Stock $stock)
{
    // ✅ DEBUG: Log incoming request dengan type checking
    Log::info('📥 Stock Edit Form Submission', [
        'stock_id' => $stock->stock_id,
        'item_code' => $stock->item->item_code,
        'request_data' => $request->all(),
        'request_types' => [
            'quantity_available_type' => gettype($request->quantity_available),
            'quantity_used_type' => gettype($request->quantity_used),
            'quantity_available_value' => $request->quantity_available,
            'quantity_used_value' => $request->quantity_used
        ],
        'user_id' => Auth::id()
    ]);

    $validator = Validator::make($request->all(), [
        'adjustment_type' => 'required|in:manual,sync_auto',
        'quantity_available' => 'required|integer|min:0',
        'quantity_used' => 'required|integer|min:0',
        'reason' => 'required|string|max:255',
        'notes' => 'nullable|string',
        'sync_item_details' => 'boolean',
    ]);

    if ($validator->fails()) {
        Log::warning('❌ Validation failed', [
            'stock_id' => $stock->stock_id,
            'errors' => $validator->errors()->toArray()
        ]);
        return back()->withErrors($validator)->withInput();
    }

    try {
        DB::beginTransaction();

        $preUpdateState = [
            'quantity_available' => $stock->quantity_available,
            'quantity_used' => $stock->quantity_used,
            'total_quantity' => $stock->total_quantity,
        ];

        // ✅ FIXED: Explicit integer casting untuk request values
        $newQuantityAvailable = (int) $request->quantity_available;
        $newQuantityUsed = (int) $request->quantity_used;

        // Get current item details state
        $currentItemDetails = DB::table('item_details')
            ->where('item_id', $stock->item->item_id)
            ->select('item_detail_id', 'serial_number', 'status')
            ->get();

        $currentStatus = [
            'stock_count' => $currentItemDetails->where('status', 'stock')->count(),
            'available_count' => $currentItemDetails->where('status', 'available')->count(),
            'total_trackable' => $currentItemDetails->whereIn('status', ['stock', 'available'])->count()
        ];

        Log::info('📊 Pre-update analysis', [
            'stock_id' => $stock->stock_id,
            'stock_table' => $preUpdateState,
            'item_details_actual' => $currentStatus,
            'request_targets' => [
                'quantity_available' => $newQuantityAvailable,    // ✅ FIXED: Now integer
                'quantity_used' => $newQuantityUsed               // ✅ FIXED: Now integer
            ],
            'target_types' => [
                'quantity_available_type' => gettype($newQuantityAvailable),
                'quantity_used_type' => gettype($newQuantityUsed)
            ],
            'consistency_check' => [
                'available_matches' => $stock->quantity_available === $currentStatus['stock_count'],
                'used_matches' => $stock->quantity_used === $currentStatus['available_count']
            ]
        ]);

        if ($request->adjustment_type === 'sync_auto') {
            $syncResult = $stock->syncWithItemDetails();

            if (!$syncResult['success']) {
                throw new \Exception('Gagal sync: ' . $syncResult['message']);
            }

            $message = 'Stock berhasil disinkronkan dengan item details.';
        } else {
            // ✅ FIXED: Use properly casted integer values
            Log::info('🔧 Manual adjustment started', [
                'stock_id' => $stock->stock_id,
                'from' => $preUpdateState,
                'to' => [
                    'quantity_available' => $newQuantityAvailable,
                    'quantity_used' => $newQuantityUsed
                ]
            ]);

            $success = $stock->adjustStock(
                $newQuantityAvailable,   // ✅ FIXED: Integer
                $newQuantityUsed,        // ✅ FIXED: Integer
                $request->reason,
                Auth::id()
            );

            if (!$success) {
                throw new \Exception('Gagal melakukan adjustment stock');
            }

            $message = 'Stock berhasil diupdate manual.';

            if ($request->boolean('sync_item_details')) {
                Log::info('🔄 Starting item details sync', [
                    'stock_id' => $stock->stock_id,
                    'target_stock' => $newQuantityAvailable,     // ✅ FIXED: Integer
                    'target_available' => $newQuantityUsed       // ✅ FIXED: Integer
                ]);

                $syncCount = $this->syncItemDetailsWithStockDebug($stock, $request);
                $message .= " Item details disinkronkan ($syncCount items).";

                Log::info('✅ Item details sync completed', [
                    'stock_id' => $stock->stock_id,
                    'synced_count' => $syncCount
                ]);
            }
        }

        // Verify final state
        $stock->refresh();
        $finalState = [
            'quantity_available' => $stock->quantity_available,
            'quantity_used' => $stock->quantity_used,
            'total_quantity' => $stock->total_quantity,
        ];

        // Verify item details after changes
        $finalItemDetails = DB::table('item_details')
            ->where('item_id', $stock->item->item_id)
            ->select('item_detail_id', 'serial_number', 'status')
            ->get();

        $finalStatus = [
            'stock_count' => $finalItemDetails->where('status', 'stock')->count(),
            'available_count' => $finalItemDetails->where('status', 'available')->count(),
            'total_trackable' => $finalItemDetails->whereIn('status', ['stock', 'available'])->count()
        ];

        // ✅ FIXED: Proper type casting for final verification
        $finalConsistencyCheck = [
            'available_matches' => ((int) $finalState['quantity_available']) === ((int) $finalStatus['stock_count']),
            'used_matches' => ((int) $finalState['quantity_used']) === ((int) $finalStatus['available_count'])
        ];

        Log::info('📊 Post-update verification', [
            'stock_id' => $stock->stock_id,
            'stock_table_final' => $finalState,
            'item_details_final' => $finalStatus,
            'changes_applied' => [
                'available_diff' => $finalState['quantity_available'] - $preUpdateState['quantity_available'],
                'used_diff' => $finalState['quantity_used'] - $preUpdateState['quantity_used'],
                'total_diff' => $finalState['total_quantity'] - $preUpdateState['total_quantity']
            ],
            'consistency_final' => $finalConsistencyCheck  // ✅ FIXED
        ]);

        // Log activity
        $this->logActivity('stocks', $stock->stock_id, 'edit_update', $preUpdateState, [
            'quantity_available' => $stock->quantity_available,
            'quantity_used' => $stock->quantity_used,
            'total_quantity' => $stock->total_quantity,
            'type' => $request->adjustment_type,
            'reason' => substr($request->reason, 0, 100)
        ]);

        DB::commit();

        Log::info('✅ Stock update completed successfully', [
            'stock_id' => $stock->stock_id,
            'message' => $message
        ]);

        return redirect()->route('stocks.show', $stock)
            ->with('success', $message);

    } catch (\Exception $e) {
        DB::rollback();

        Log::error('❌ Stock update failed', [
            'stock_id' => $stock->stock_id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all()
        ]);

        return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
    }
}

    /**
     * DEBUG: Endpoint untuk debug form submission data
     */
    public function debugFormSubmission(Request $request)
    {
        Log::info('🔍 Debug form submission', [
            'all_request_data' => $request->all(),
            'headers' => $request->headers->all(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'user_id' => Auth::id(),
            'timestamp' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Debug data logged',
            'data' => $request->all()
        ]);
    }

    /**
     * FIXED: syncItemDetailsWithStock - Line 487 error fix
     */
    public function syncItemDetailsWithStock(Stock $stock, Request $request)
    {
        try {
            $item = $stock->item;
            if (!$item) {
                throw new \Exception('Item not found');
            }

            // ✅ FIXED: Fresh query langsung dari DB
            $itemDetails = DB::table('item_details')
                ->where('item_id', $item->item_id)
                ->get();

            $targetStock = $request->quantity_available ?? $stock->quantity_available;
            $targetAvailable = $request->quantity_used ?? $stock->quantity_used;

            $currentStock = $itemDetails->where('status', 'stock')->count();
            $currentAvailable = $itemDetails->where('status', 'available')->count();

            $updated = 0;
            $changes = [];

            // Move dari available ke stock
            $needMoveToStock = $targetStock - $currentStock;
            if ($needMoveToStock > 0) {
                $itemsToMove = $itemDetails->where('status', 'available')->take($needMoveToStock);

                foreach ($itemsToMove as $itemDetail) {
                    DB::table('item_details')
                        ->where('item_detail_id', $itemDetail->item_detail_id)
                        ->update([
                            'status' => 'stock',
                            'location' => 'Warehouse - Stock',
                            'notes' => 'Moved to stock: ' . ($request->reason ?? 'Sync'),
                            'updated_at' => now()
                        ]);

                    $updated++;
                    $changes[] = "{$itemDetail->serial_number}: available → stock";
                }
            }

            // Move dari stock ke available
            $needMoveToAvailable = $targetAvailable - $currentAvailable;
            if ($needMoveToAvailable > 0) {
                $itemsToMove = $itemDetails->where('status', 'stock')->take($needMoveToAvailable);

                foreach ($itemsToMove as $itemDetail) {
                    DB::table('item_details')
                        ->where('item_detail_id', $itemDetail->item_detail_id)
                        ->update([
                            'status' => 'available',
                            'location' => 'Office - Ready',
                            'notes' => 'Moved to available: ' . ($request->reason ?? 'Sync'),
                            'updated_at' => now()
                        ]);

                    $updated++;
                    $changes[] = "{$itemDetail->serial_number}: stock → available";
                }
            }

            Log::info('Item details synced with stock', [
                'stock_id' => $stock->stock_id,
                'updated_items' => $updated,
                'target_stock' => $targetStock,
                'target_available' => $targetAvailable,
                'changes' => $changes
            ]);

            return $updated;
        } catch (\Exception $e) {
            Log::error('Sync item details failed', [
                'stock_id' => $stock->stock_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * FIXED: logActivity method - Fix DB column length error
     */
    private function logActivity($tableName, $recordId, $action, $oldData, $newData)
    {
        try {
            // ✅ FIXED: Limit semua field untuk avoid DB truncation error
            $shortAction = substr($action, 0, 50); // Max 50 chars

            // Limit data size
            $limitedOldData = $this->limitLogDataSize($oldData);
            $limitedNewData = $this->limitLogDataSize($newData);

            $lastLog = \App\Models\ActivityLog::orderBy('log_id', 'desc')->first();
            $lastNumber = $lastLog ? (int) substr($lastLog->log_id, 3) : 0;
            $newNumber = $lastNumber + 1;
            $logId = 'LOG' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);

            \App\Models\ActivityLog::create([
                'log_id' => $logId,
                'user_id' => Auth::id(),
                'table_name' => substr($tableName, 0, 50),
                'record_id' => substr($recordId, 0, 50),
                'action' => $shortAction,
                'old_values' => $limitedOldData,
                'new_values' => $limitedNewData,
                'ip_address' => request()->ip(),
                'user_agent' => substr(request()->userAgent() ?? '', 0, 255),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log activity', [
                'error' => $e->getMessage(),
                'table' => $tableName,
                'record_id' => $recordId,
                'action' => $action
            ]);
        }
    }

    /**
     * NEW: Helper method untuk limit log data size
     */
    private function limitLogDataSize($data, $maxLength = 1000)
    {
        if (!is_array($data)) {
            return is_string($data) ? substr($data, 0, $maxLength) : $data;
        }

        $jsonData = json_encode($data);

        if (strlen($jsonData) <= $maxLength) {
            return $data;
        }

        // Create summary if too long
        $summary = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $summary[$key] = substr($value, 0, 100) . (strlen($value) > 100 ? '...' : '');
            } elseif (is_array($value)) {
                $summary[$key] = '[Array: ' . count($value) . ' items]';
            } else {
                $summary[$key] = $value;
            }
        }

        return $summary;
    }

    /**
     * FIXED: syncWithItemDetails endpoint
     */
    public function syncWithItemDetails(Stock $stock)
    {
        try {
            DB::beginTransaction();

            $syncResult = $stock->syncWithItemDetails();

            if (!$syncResult['success']) {
                throw new \Exception($syncResult['message']);
            }

            // ✅ FIXED: Short action name untuk activity log
            $this->logActivity(
                'stocks',
                $stock->stock_id,
                'sync',
                $syncResult['old_values'],
                $syncResult['new_values']
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock berhasil disinkronkan',
                'changes' => $syncResult['changes'],
                'data' => $syncResult
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal sync: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * NEW: Debug endpoint untuk troubleshooting
     */
    public function debugStock(Stock $stock)
    {
        try {
            $debugInfo = $stock->debugSyncIssues();

            return response()->json([
                'success' => true,
                'debug_info' => $debugInfo,
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Enhanced sync validation endpoint
     */
    public function validateStockConsistency(Stock $stock)
    {
        try {
            $validation = $stock->validateConsistencyFixed();

            return response()->json([
                'success' => true,
                'validation' => $validation,
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch sync multiple stocks
     */
    public function batchSyncStocks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stock_ids' => 'required|array|min:1',
            'stock_ids.*' => 'required|string|exists:stocks,stock_id',
            'force_sync' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $results = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($request->stock_ids as $stockId) {
                try {
                    $stock = Stock::findOrFail($stockId);

                    // Check consistency first unless force sync
                    if (!$request->boolean('force_sync')) {
                        $validation = $stock->validateConsistencyFixed();
                        if ($validation['consistent']) {
                            $results[] = [
                                'stock_id' => $stockId,
                                'status' => 'skipped',
                                'message' => 'Already consistent'
                            ];
                            continue;
                        }
                    }

                    $syncResult = $stock->syncWithItemDetails();

                    if ($syncResult['success']) {
                        $successCount++;
                        $results[] = [
                            'stock_id' => $stockId,
                            'status' => 'success',
                            'changes' => $syncResult['changes']
                        ];
                    } else {
                        $errorCount++;
                        $results[] = [
                            'stock_id' => $stockId,
                            'status' => 'error',
                            'message' => $syncResult['message']
                        ];
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $results[] = [
                        'stock_id' => $stockId,
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_processed' => count($request->stock_ids),
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'skipped_count' => count($results) - $successCount - $errorCount
                ],
                'results' => $results
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Batch sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed sync report for all stocks
     */
    public function getSyncReport(Request $request)
    {
        try {
            $includeConsistent = $request->boolean('include_consistent', false);
            $limit = $request->get('limit', 50);

            $query = Stock::with('item');

            if ($limit > 0) {
                $stocks = $query->take($limit)->get();
            } else {
                $stocks = $query->get();
            }

            $report = [
                'total_stocks' => $stocks->count(),
                'consistent_count' => 0,
                'inconsistent_count' => 0,
                'error_count' => 0,
                'items' => []
            ];

            foreach ($stocks as $stock) {
                try {
                    $validation = $stock->validateConsistencyFixed();

                    $item = [
                        'stock_id' => $stock->stock_id,
                        'item_code' => $stock->item->item_code ?? 'N/A',
                        'item_name' => $stock->item->item_name ?? 'Unknown',
                        'consistent' => $validation['consistent'],
                        'last_updated' => $stock->last_updated
                    ];

                    if (!$validation['consistent']) {
                        $item['discrepancies'] = $validation['discrepancies'];
                        $item['recommendations'] = $validation['recommendations'] ?? [];
                        $report['inconsistent_count']++;
                    } else {
                        $report['consistent_count']++;
                    }

                    // Include in report based on filter
                    if (!$validation['consistent'] || $includeConsistent) {
                        $report['items'][] = $item;
                    }
                } catch (\Exception $e) {
                    $report['error_count']++;
                    $report['items'][] = [
                        'stock_id' => $stock->stock_id,
                        'item_code' => $stock->item->item_code ?? 'N/A',
                        'item_name' => $stock->item->item_name ?? 'Unknown',
                        'consistent' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }

            $report['consistency_rate'] = $stocks->count() > 0
                ? round(($report['consistent_count'] / $stocks->count()) * 100, 2)
                : 0;

            return response()->json([
                'success' => true,
                'report' => $report,
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate sync report: ' . $e->getMessage()
            ], 500);
        }
    }



    // **NEW: Get item details breakdown**
    private function getItemDetailsBreakdown(Stock $stock): array
    {
        $item = $stock->item;
        $itemDetails = $item->itemDetails;

        $breakdown = [
            'total_items' => $itemDetails->count(),
            'by_status' => [
                'stock' => $itemDetails->where('status', 'stock')->count(),
                'available' => $itemDetails->where('status', 'available')->count(),
                'used' => $itemDetails->where('status', 'used')->count(),
                'damaged' => $itemDetails->where('status', 'damaged')->count(),
                'maintenance' => $itemDetails->where('status', 'maintenance')->count(),
                'reserved' => $itemDetails->where('status', 'reserved')->count(),
            ],
            'summary' => [
                'in_warehouse' => $itemDetails->where('status', 'stock')->count(),
                'ready_to_use' => $itemDetails->where('status', 'available')->count(),
                'in_use' => $itemDetails->whereIn('status', ['used', 'reserved'])->count(),
                'not_available' => $itemDetails->whereIn('status', ['damaged', 'maintenance'])->count(),
            ]
        ];

        // Add comparison with stock table
        $breakdown['comparison'] = [
            'stock_table' => [
                'available' => $stock->quantity_available,
                'used' => $stock->quantity_used,
                'total' => $stock->total_quantity
            ],
            'item_details' => [
                'stock_status' => $breakdown['by_status']['stock'],
                'available_status' => $breakdown['by_status']['available'],
                'total_trackable' => $breakdown['by_status']['stock'] + $breakdown['by_status']['available']
            ],
            'is_synced' => (
                $stock->quantity_available == $breakdown['by_status']['stock'] &&
                $stock->quantity_used == $breakdown['by_status']['available']
            )
        ];

        return $breakdown;
    }

    // **NEW: Bulk sync all stocks**
    public function bulkSync(Request $request)
    {
        try {
            DB::beginTransaction();

            $syncResult = Stock::syncAllStocks();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $syncResult['message'],
                'data' => $syncResult
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal bulk sync: ' . $e->getMessage()
            ], 500);
        }
    }

    // **NEW: Get inconsistencies report**
    public function getInconsistencies(Request $request)
    {
        try {
            $report = Stock::getInconsistenciesReport();

            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan report: ' . $e->getMessage()
            ], 500);
        }
    }

    // **NEW: Auto-fix inconsistencies**
    public function autoFixInconsistencies(Request $request)
    {
        try {
            DB::beginTransaction();

            $result = Stock::autoFixAllInconsistencies();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal auto-fix: ' . $e->getMessage()
            ], 500);
        }
    }
}
