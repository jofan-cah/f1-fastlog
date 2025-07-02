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

        // Filter by stock status
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
            }
        }

        // Search by item name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('item', function($q) use ($search) {
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

        // Stock summary
        $summary = Stock::getStockSummary();

        // Categories untuk filter
        $categories = Category::active()
            ->whereHas('items.stock')
            ->withCount(['items' => function($q) {
                $q->whereHas('stock');
            }])
            ->orderBy('category_name')
            ->get();

        // Low stock alerts
        $lowStockAlerts = Stock::lowStock()
            ->with('item')
            ->orderBy('quantity_available')
            ->take(5)
            ->get();

        return view('stocks.index', compact(
            'stocks', 'summary', 'categories', 'lowStockAlerts',
            'sortField', 'sortDirection'
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
            ->map(function($stock) {
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
