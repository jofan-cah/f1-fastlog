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

        return view('stocks.edit', compact('stock', 'itemDetailsBreakdown', 'syncStatus'));
    }

    // **NEW: Update stock dengan sync item details**
    public function update(Request $request, Stock $stock)
    {
        $validator = Validator::make($request->all(), [
            'adjustment_type' => 'required|in:manual,sync_auto',
            'quantity_available' => 'required|integer|min:0',
            'quantity_used' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'sync_item_details' => 'boolean',
        ], [
            'adjustment_type.required' => 'Tipe adjustment wajib dipilih.',
            'quantity_available.required' => 'Quantity available wajib diisi.',
            'quantity_used.required' => 'Quantity used wajib diisi.',
            'reason.required' => 'Alasan wajib diisi.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $oldData = [
                'quantity_available' => $stock->quantity_available,
                'quantity_used' => $stock->quantity_used,
                'total_quantity' => $stock->total_quantity,
            ];

            if ($request->adjustment_type === 'sync_auto') {
                // Auto sync with item details
                $syncResult = $stock->syncWithItemDetails();

                if (!$syncResult['success']) {
                    throw new \Exception('Gagal sync dengan item details: ' . $syncResult['message']);
                }

                $message = 'Stock berhasil disinkronkan dengan item details.';
            } else {
                // Manual adjustment
                $success = $stock->adjustStock(
                    $request->quantity_available,
                    $request->quantity_used,
                    $request->reason,
                    Auth::id()
                );

                if (!$success) {
                    throw new \Exception('Gagal melakukan adjustment stock');
                }

                $message = 'Stock berhasil diupdate manual.';

                // Jika diminta sync item details setelah manual update
                if ($request->boolean('sync_item_details')) {
                    $this->syncItemDetailsWithStock($stock, $request);
                    $message .= ' Item details juga telah disinkronkan.';
                }
            }

            // Log activity
            $this->logActivity('stocks', $stock->stock_id, 'update', $oldData, [
                'quantity_available' => $stock->quantity_available,
                'quantity_used' => $stock->quantity_used,
                'total_quantity' => $stock->total_quantity,
                'adjustment_type' => $request->adjustment_type,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'sync_applied' => $request->adjustment_type === 'sync_auto' || $request->boolean('sync_item_details')
            ]);

            DB::commit();

            return redirect()->route('stocks.show', $stock)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate stock: ' . $e->getMessage());
        }
    }

    // **NEW: Sync stock dengan item details**
    public function syncWithItemDetails(Stock $stock)
    {
        try {
            DB::beginTransaction();

            $syncResult = $stock->syncWithItemDetails();

            if (!$syncResult['success']) {
                throw new \Exception($syncResult['message']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock berhasil disinkronkan dengan item details',
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

    // **NEW: Sync item details status dengan stock**
    public function syncItemDetailsWithStock(Stock $stock, Request $request)
    {
        try {
            // Ambil item details yang perlu disesuaikan
            $item = $stock->item;
            $itemDetails = $item->itemDetails;

            $targetAvailable = $request->quantity_available ?? $stock->quantity_available;
            $targetUsed = $request->quantity_used ?? $stock->quantity_used;

            // Hitung current status dari item details
            $currentStock = $itemDetails->where('status', 'stock')->count();
            $currentAvailable = $itemDetails->where('status', 'available')->count();

            // Calculate yang perlu diubah
            $needToMoveToStock = $targetAvailable - $currentStock;
            $needToMoveToAvailable = $targetUsed - $currentAvailable;

            $updated = 0;

            // Move items to stock status
            if ($needToMoveToStock > 0) {
                $itemsToMoveToStock = $itemDetails
                    ->where('status', 'available')
                    ->take($needToMoveToStock);

                foreach ($itemsToMoveToStock as $itemDetail) {
                    $itemDetail->update([
                        'status' => 'stock',
                        'location' => 'Warehouse - Stock',
                        'notes' => 'Moved to stock via sync: ' . ($request->reason ?? 'Stock sync')
                    ]);
                    $updated++;
                }
            }

            // Move items to available status
            if ($needToMoveToAvailable > 0) {
                $itemsToMoveToAvailable = $itemDetails
                    ->where('status', 'stock')
                    ->take($needToMoveToAvailable);

                foreach ($itemsToMoveToAvailable as $itemDetail) {
                    $itemDetail->update([
                        'status' => 'available',
                        'location' => 'Office - Ready',
                        'notes' => 'Moved to available via sync: ' . ($request->reason ?? 'Stock sync')
                    ]);
                    $updated++;
                }
            }

            Log::info('Item details synced with stock', [
                'stock_id' => $stock->stock_id,
                'updated_items' => $updated,
                'target_stock' => $targetAvailable,
                'target_available' => $targetUsed
            ]);

            return $updated;
        } catch (\Exception $e) {
            Log::error('Failed to sync item details with stock: ' . $e->getMessage());
            throw $e;
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





    // Helper method untuk log activity
    private function logActivity($tableName, $recordId, $action, $oldData, $newData)
    {
        try {
            $lastLog = \App\Models\ActivityLog::orderBy('log_id', 'desc')->first();
            $lastNumber = $lastLog ? (int) substr($lastLog->log_id, 3) : 0;
            $newNumber = $lastNumber + 1;
            $logId = 'LOG' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);

            \App\Models\ActivityLog::create([
                'log_id' => $logId,
                'user_id' => Auth::id(),
                'table_name' => $tableName,
                'record_id' => $recordId,
                'action' => $action,
                'old_values' => $oldData,
                'new_values' => $newData,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }
}
