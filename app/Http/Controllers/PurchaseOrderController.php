<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PoDetail;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseOrderController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth'); // FIX: Uncomment auth middleware
        // Nanti bisa ditambah permission middleware
        // $this->middleware('permission:purchase_orders.read')->only(['index', 'show']);
        // $this->middleware('permission:purchase_orders.create')->only(['create', 'store']);
        // $this->middleware('permission:purchase_orders.update')->only(['edit', 'update']);
    }

    // Tampilkan daftar PO
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'createdBy'])
            ->withCount('poDetails');

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->bySupplier($request->supplier_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = $request->filled('end_date')
                ? Carbon::parse($request->end_date)
                : now();

            $query->dateRange($startDate, $endDate);
        }

        // Search by PO number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q2) use ($search) {
                        $q2->where('supplier_name', 'like', "%{$search}%");
                    });
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'po_date');
        $sortDirection = $request->get('direction', 'desc');

        $allowedSorts = ['po_number', 'po_date', 'expected_date', 'total_amount', 'status'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $purchaseOrders = $query->paginate(15)->withQueryString();

        // Statistics
        $statistics = PurchaseOrder::getStatistics();

        // Suppliers untuk filter
        $suppliers = Supplier::active()
            ->whereHas('purchaseOrders')
            ->orderBy('supplier_name')
            ->get();

        // Overdue POs
        $overduePOs = PurchaseOrder::overdue()
            ->with('supplier')
            ->orderBy('expected_date')
            ->take(5)
            ->get();

        return view('purchase-orders.index', compact(
            'purchaseOrders',
            'statistics',
            'suppliers',
            'overduePOs',
            'sortField',
            'sortDirection'
        ));
    }

    // Tampilkan form create PO
    public function create(Request $request)
    {
        $supplierId = $request->get('supplier_id');
        $lowStockItems = $request->boolean('low_stock', false);

        // Get suppliers
        $suppliers = Supplier::active()->orderBy('supplier_name')->get();

        // Get items (filter by low stock if requested)
        $itemsQuery = Item::active()->with(['category', 'stock']);

        if ($lowStockItems) {
            $itemsQuery->whereHas('stock', function ($q) {
                $q->whereRaw('quantity_available <= items.min_stock');
            });
        }

        $items = $itemsQuery->orderBy('item_name')->get();

        // Generate PO number
        $poNumber = PurchaseOrder::generatePONumber();

        // Selected supplier
        $selectedSupplier = $supplierId ? Supplier::find($supplierId) : null;

        $availableItems = $items->map(function ($item) {
            $stockInfo = $item->getStockInfo();
            return [
                'item_id' => $item->item_id,
                'item_name' => $item->item_name,
                'item_code' => $item->item_code,
                'category_name' => optional($item->category)->category_name ?? 'No Category',
                'unit' => $item->unit,
                'available_stock' => $stockInfo['available'] ?? 0,
                'stock_status' => $stockInfo['status'] ?? 'unknown',
            ];
        })->values();

        $suppliersJson = $suppliers->map(function ($supplier) {
            return [
                'supplier_id' => $supplier->supplier_id,
                'supplier_name' => $supplier->supplier_name,
                'supplier_code' => $supplier->supplier_code,
                'contact_person' => $supplier->contact_person ?? null,
                'phone' => $supplier->phone ?? null,
                'address' => $supplier->address ?? null,
            ];
        })->values();

        return view('purchase-orders.create', compact(
            'suppliers',
            'items',
            'poNumber',
            'selectedSupplier',
            'lowStockItems',
            'suppliersJson',
            'availableItems'
        ));
    }

    // Store PO baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'po_number' => 'required|string|max:50|unique:purchase_orders,po_number',
            'supplier_id' => 'required|string|exists:suppliers,supplier_id',
            'po_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:po_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|string|exists:items,item_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ], [
            'po_number.required' => 'Nomor PO wajib diisi.',
            'po_number.unique' => 'Nomor PO sudah digunakan.',
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'po_date.required' => 'Tanggal PO wajib diisi.',
            'items.required' => 'Items wajib diisi.',
            'items.min' => 'Minimal 1 item harus dipilih.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Generate PO ID
            $poId = $this->generatePOId();

            // Create Purchase Order
            $po = PurchaseOrder::create([
                'po_id' => $poId,
                'po_number' => $request->po_number,
                'supplier_id' => $request->supplier_id,
                'po_date' => $request->po_date,
                'expected_date' => $request->expected_date,
                'status' => 'draft',
                'total_amount' => 0,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            // Create PO Details
            $totalAmount = 0;
            foreach ($request->items as $itemData) {
                $detailId = PoDetail::generateDetailId();
                $totalPrice = $itemData['quantity'] * $itemData['unit_price'];

                PoDetail::create([
                    'po_detail_id' => $detailId,
                    'po_id' => $po->po_id,
                    'item_id' => $itemData['item_id'],
                    'quantity_ordered' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $totalPrice,
                    'quantity_received' => 0,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $totalAmount += $totalPrice;
            }

            // Update total amount
            $po->update(['total_amount' => $totalAmount]);

            // Log activity
            ActivityLog::logActivity('purchase_orders', $po->po_id, 'create', null, $po->toArray());

            DB::commit();

            // FIX: Return redirect instead of JSON response for store method
            return redirect()->route('purchase-orders.show', $po)
                ->with('success', 'Purchase Order berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Gagal membuat PO: ' . $e->getMessage());
        }
    }

    // Tampilkan detail PO
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'createdBy.userLevel',
            'poDetails.item.category',
            'goodsReceived' => function ($query) {
                $query->latest()->take(5);
            }
        ]);

        // Summary info
        $summaryInfo = $purchaseOrder->getSummaryInfo();

        // Status info
        $statusInfo = $purchaseOrder->getStatusInfo();
        // dd($statusInfo);

        return view('purchase-orders.show', compact(
            'purchaseOrder',
            'summaryInfo',
            'statusInfo'
        ));
    }

    // Tampilkan form edit PO
    public function edit(PurchaseOrder $purchaseOrder)
    {

        if (!$purchaseOrder->canBeEdited()) {
            return back()->with('error', 'PO tidak dapat diedit karena statusnya bukan draft.');
        }

        $purchaseOrder->load(['poDetails.item']);

        $suppliers = Supplier::active()->orderBy('supplier_name')->get();
        $items = Item::active()->with(['category', 'stock'])->orderBy('item_name')->get();

        return view('purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'items'));
    }

    // Update PO
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeEdited()) {
            return back()->with('error', 'PO tidak dapat diedit karena statusnya bukan draft.');
        }

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|string|exists:suppliers,supplier_id',
            'po_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:po_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|string|exists:items,item_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $oldData = $purchaseOrder->toArray();

            // Update PO header
            $purchaseOrder->update([
                'supplier_id' => $request->supplier_id,
                'po_date' => $request->po_date,
                'expected_date' => $request->expected_date,
                'notes' => $request->notes,
            ]);

            // Delete existing details
            $purchaseOrder->poDetails()->delete();

            // Create new details
            $totalAmount = 0;
            foreach ($request->items as $itemData) {
                $detailId = PoDetail::generateDetailId();
                $totalPrice = $itemData['quantity'] * $itemData['unit_price'];

                PoDetail::create([
                    'po_detail_id' => $detailId,
                    'po_id' => $purchaseOrder->po_id,
                    'item_id' => $itemData['item_id'],
                    'quantity_ordered' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $totalPrice,
                    'quantity_received' => 0,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $totalAmount += $totalPrice;
            }

            // Update total amount
            $purchaseOrder->update(['total_amount' => $totalAmount]);

            // Log activity
            ActivityLog::logActivity('purchase_orders', $purchaseOrder->po_id, 'update', $oldData, $purchaseOrder->fresh()->toArray());

            DB::commit();

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase Order berhasil diupdate!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate PO: ' . $e->getMessage());
        }
    }

    // Update status PO
    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,sent,partial,received,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $oldStatus = $purchaseOrder->status;
            $newStatus = $request->status;

            // Validate status transition
            if (!$this->isValidStatusTransition($oldStatus, $newStatus)) {
                return back()->with('error', 'Perubahan status tidak valid.');
            }

            $oldData = $purchaseOrder->toArray();

            $purchaseOrder->update([
                'status' => $newStatus,
                'notes' => $request->notes ? $purchaseOrder->notes . "\n\n" . now()->format('Y-m-d H:i') . " - " . $request->notes : $purchaseOrder->notes,
            ]);

            // Log activity
            ActivityLog::logActivity('purchase_orders', $purchaseOrder->po_id, 'status_change', $oldData, [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'notes' => $request->notes
            ]);

            return back()->with('success', 'Status PO berhasil diubah!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    // Cancel PO
    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeCancelled()) {
            return back()->with('error', 'PO tidak dapat dibatalkan.');
        }

        try {
            $oldData = $purchaseOrder->toArray();

            $purchaseOrder->update([
                'status' => 'cancelled',
                'notes' => $purchaseOrder->notes . "\n\n" . now()->format('Y-m-d H:i') . " - Dibatalkan: " . ($request->reason ?? 'Tidak ada alasan'),
            ]);

            // Log activity
            ActivityLog::logActivity('purchase_orders', $purchaseOrder->po_id, 'cancel', $oldData, [
                'reason' => $request->reason ?? 'Tidak ada alasan'
            ]);

            return back()->with('success', 'PO berhasil dibatalkan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan PO: ' . $e->getMessage());
        }
    }
    // send PO
    public function send(Request $request, PurchaseOrder $purchaseOrder)
    {
        try {
            $oldData = $purchaseOrder->toArray();

            $purchaseOrder->update([
                'status' => 'sent',
                'notes' => $request->notes
            ]);

            // Log activity
            ActivityLog::logActivity('purchase_orders', $purchaseOrder->po_id, 'cancel', $oldData, [
                'reason' => $request->reason ?? 'Tidak ada alasan'
            ]);

            return back()->with('success', 'PO Di ACC!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal ACC PO: ' . $e->getMessage());
        }
    }

    // Print PO (placeholder untuk PDF generation)
    public function print(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'createdBy', 'poDetails.item']);

        // Nanti bisa diintegrasikan dengan library PDF seperti TCPDF atau DomPDF
        return view('purchase-orders.print', compact('purchaseOrder'));
    }

    // API endpoint untuk duplicate PO
    public function duplicate(PurchaseOrder $purchaseOrder)
    {
        try {
            DB::beginTransaction();

            $newPONumber = PurchaseOrder::generatePONumber();
            $newPOId = $this->generatePOId();

            // Create new PO
            $newPO = PurchaseOrder::create([
                'po_id' => $newPOId,
                'po_number' => $newPONumber,
                'supplier_id' => $purchaseOrder->supplier_id,
                'po_date' => now()->toDateString(),
                'expected_date' => null,
                'status' => 'sent',
                'total_amount' => $purchaseOrder->total_amount,
                'notes' => 'Duplikasi dari PO: ' . $purchaseOrder->po_number,
                'created_by' => Auth::id(),
            ]);

            // Duplicate details
            foreach ($purchaseOrder->poDetails as $detail) {
                PoDetail::create([
                    'po_detail_id' => PoDetail::generateDetailId(),
                    'po_id' => $newPO->po_id,
                    'item_id' => $detail->item_id,
                    'quantity_ordered' => $detail->quantity_ordered,
                    'unit_price' => $detail->unit_price,
                    'total_price' => $detail->total_price,
                    'quantity_received' => 0,
                    'notes' => $detail->notes,
                ]);
            }

            // Log activity
            ActivityLog::logActivity('purchase_orders', $newPO->po_id, 'duplicate', null, [
                'original_po' => $purchaseOrder->po_number,
                'new_po' => $newPO->po_number
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'PO berhasil diduplikasi!',
                'new_po_id' => $newPO->po_id,
                'new_po_number' => $newPO->po_number,
                'redirect_url' => route('purchase-orders.show', $newPO)
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menduplikasi PO: ' . $e->getMessage()
            ], 500);
        }
    }

    // API endpoint untuk get PO by supplier
    public function getBySupplier(Request $request, $supplierId)
    {
        $pos = PurchaseOrder::bySupplier($supplierId)
            ->active()
            ->select('po_id', 'po_number', 'po_date', 'status', 'total_amount')
            ->orderBy('po_date', 'desc')
            ->get();

        return response()->json($pos);
    }

    // Generate PO ID
    private function generatePOId(): string
    {
        $lastPO = PurchaseOrder::orderBy('po_id', 'desc')->first();
        $lastNumber = $lastPO ? (int) substr($lastPO->po_id, 2) : 0;
        $newNumber = $lastNumber + 1;
        return 'PO' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    // Validate status transition
    private function isValidStatusTransition(string $currentStatus, string $newStatus): bool
    {
        $validTransitions = [
            'draft' => ['sent', 'cancelled'],
            'sent' => ['partial', 'received', 'cancelled'],
            'partial' => ['received', 'cancelled'],
            'received' => [], // Final status
            'cancelled' => [], // Final status
        ];

        return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
    }
}
