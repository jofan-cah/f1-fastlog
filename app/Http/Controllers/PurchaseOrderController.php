<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PoDetail;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\ActivityLog;
use App\Constants\PurchaseOrderConstants;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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

    // Helper method untuk check user level permission
    private function canUserAccess($action, $purchaseOrder = null): bool
    {
        $userLevel = Auth::user()->user_level_id ?? null;

        // Admin bisa semua
        if ($userLevel === 'LVL001') {
            return true;
        }

        // Untuk action yang butuh PO context
        if ($purchaseOrder) {
            switch ($action) {
                case 'edit_logistic':
                    return $userLevel === 'LVL002' && $purchaseOrder->canBeEditedByLogistic();

                case 'process_f1':
                    return in_array($userLevel, ['LVL004', 'LVL005']) && $purchaseOrder->canBeProcessedByFinanceF1();

                case 'process_f2':
                    return $userLevel === 'LVL005' && $purchaseOrder->canBeProcessedByFinanceF2();

                case 'reject_f1':
                    return in_array($userLevel, ['LVL004', 'LVL005']) && $purchaseOrder->canBeRejectedByFinanceF1();

                case 'reject_f2':
                    return $userLevel === 'LVL005' && $purchaseOrder->canBeRejectedByFinanceF2();

                case 'return_from_reject':
                    return $userLevel === 'LVL001'; // Admin only untuk return
            }
        }

        // General permissions
        switch ($action) {
            case 'create':
                return $userLevel === 'LVL002'; // Logistik only
            case 'view':
                return in_array($userLevel, ['LVL001', 'LVL002', 'LVL004', 'LVL005']);
            default:
                return false;
        }
    }

    // Tampilkan daftar PO - Modified untuk workflow
    // Tampilkan daftar PO - Modified untuk workflow + filter active/completed
    public function index(Request $request)
    {
        if (!$this->canUserAccess('view')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $query = PurchaseOrder::with(['supplier', 'createdBy', 'logisticUser', 'financeF1User', 'financeF2User'])
            ->withCount('poDetails');

        // Filter berdasarkan user level - tampilkan sesuai permission
        $userLevel = Auth::user()->user_level_id ?? null;

        switch ($userLevel) {
            case 'LVL002': // Logistik - lihat yang draft atau rejected
                $query->whereIn('workflow_status', [
                    PurchaseOrderConstants::WORKFLOW_STATUS_DRAFT_LOGISTIC,
                    PurchaseOrderConstants::WORKFLOW_STATUS_REJECTED_F1,
                    PurchaseOrderConstants::WORKFLOW_STATUS_REJECTED_F2
                ]);
                break;
            case 'LVL004': // FinanceF1 - lihat yang pending F1
                $query->where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F1);
                break;
            case 'LVL005': // FinanceRasi - lihat yang pending F2 dan semua untuk oversight
                // FinanceRasi bisa lihat semua kecuali draft logistik
                $query->where('workflow_status', '!=', PurchaseOrderConstants::WORKFLOW_STATUS_DRAFT_LOGISTIC);
                break;
                // LVL001 (Admin) sudah bisa lihat semua tanpa filter
        }

        // NEW: Handle active/completed filter from sidebar
        $filter = $request->get('filter');
        if ($filter === 'active') {
            // PO yang masih dalam proses (belum selesai)
            $query->whereNotIn('workflow_status', [
                PurchaseOrderConstants::WORKFLOW_STATUS_RECEIVED,
                PurchaseOrderConstants::WORKFLOW_STATUS_CANCELLED
            ]);
        } elseif ($filter === 'completed') {
            // PO yang sudah selesai atau dibatalkan
            $query->whereIn('workflow_status', [
                PurchaseOrderConstants::WORKFLOW_STATUS_RECEIVED,
                PurchaseOrderConstants::WORKFLOW_STATUS_CANCELLED
            ]);
        }
        // Jika tidak ada filter atau 'all', tampilkan semua (default behavior)

        // Filter by workflow status (dari dropdown filter)
        if ($request->filled('workflow_status')) {
            $query->byWorkflowStatus($request->workflow_status);
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

        $allowedSorts = ['po_number', 'po_date', 'expected_date', 'total_amount', 'workflow_status'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $purchaseOrders = $query->paginate(15)->withQueryString();

        // Statistics - Updated untuk workflow + active/completed breakdown
        $statistics = $this->getFilteredStatistics($filter, $userLevel);

        // Suppliers untuk filter
        $suppliers = Supplier::active()
            ->whereHas('purchaseOrders')
            ->orderBy('supplier_name')
            ->get();

        // Overdue POs (hanya untuk active POs)
        $overduePOs = collect();
        if ($filter !== 'completed') {
            $overduePOs = PurchaseOrder::overdue()
                ->whereNotIn('workflow_status', [
                    PurchaseOrderConstants::WORKFLOW_STATUS_RECEIVED,
                    PurchaseOrderConstants::WORKFLOW_STATUS_CANCELLED
                ])
                ->with('supplier')
                ->orderBy('expected_date')
                ->take(5)
                ->get();
        }

        // Workflow statuses untuk filter dropdown
        $workflowStatuses = PurchaseOrderConstants::getWorkflowStatuses();

        return view('purchase-orders.index', compact(
            'purchaseOrders',
            'statistics',
            'suppliers',
            'overduePOs',
            'workflowStatuses',
            'sortField',
            'sortDirection',
            'filter' // Pass filter to view for UI state
        ));
    }

    // NEW: Get statistics based on filter
    private function getFilteredStatistics($filter, $userLevel)
    {
        $baseQuery = PurchaseOrder::query();

        // Apply user level filtering
        switch ($userLevel) {
            case 'LVL002':
                $baseQuery->whereIn('workflow_status', [
                    PurchaseOrderConstants::WORKFLOW_STATUS_DRAFT_LOGISTIC,
                    PurchaseOrderConstants::WORKFLOW_STATUS_REJECTED_F1,
                    PurchaseOrderConstants::WORKFLOW_STATUS_REJECTED_F2
                ]);
                break;
            case 'LVL004':
                $baseQuery->where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F1);
                break;
            case 'LVL005':
                $baseQuery->where('workflow_status', '!=', PurchaseOrderConstants::WORKFLOW_STATUS_DRAFT_LOGISTIC);
                break;
        }

        // Base statistics
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'draft_logistic' => (clone $baseQuery)->where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_DRAFT_LOGISTIC)->count(),
            'pending_finance_f1' => (clone $baseQuery)->where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F1)->count(),
            'pending_finance_f2' => (clone $baseQuery)->where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F2)->count(),
            'approved' => (clone $baseQuery)->where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_APPROVED)->count(),
            'rejected' => (clone $baseQuery)->whereIn('workflow_status', [
                PurchaseOrderConstants::WORKFLOW_STATUS_REJECTED_F1,
                PurchaseOrderConstants::WORKFLOW_STATUS_REJECTED_F2
            ])->count(),
            'sent' => (clone $baseQuery)->where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_SENT)->count(),
            'received' => (clone $baseQuery)->where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_RECEIVED)->count(),
            'overdue' => (clone $baseQuery)->overdue()->count(),
            'payment_overdue' => (clone $baseQuery)->paymentOverdue()->count(),
        ];

        // Add filter-specific stats
        if ($filter === 'active') {
            $stats['active_total'] = (clone $baseQuery)->whereNotIn('workflow_status', [
                PurchaseOrderConstants::WORKFLOW_STATUS_RECEIVED,
                PurchaseOrderConstants::WORKFLOW_STATUS_CANCELLED
            ])->count();
        } elseif ($filter === 'completed') {
            $stats['completed_total'] = (clone $baseQuery)->whereIn('workflow_status', [
                PurchaseOrderConstants::WORKFLOW_STATUS_RECEIVED,
                PurchaseOrderConstants::WORKFLOW_STATUS_CANCELLED
            ])->count();
            $stats['cancelled'] = (clone $baseQuery)->where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_CANCELLED)->count();
        }

        return $stats;
    }

    // Tampilkan form create PO - Logistik only
    public function create(Request $request)
    {
        if (!$this->canUserAccess('create')) {
            return redirect()->back()->with('error', 'Hanya Logistik yang dapat membuat PO baru.');
        }

        $supplierId = $request->get('supplier_id');
        $lowStockItems = $request->boolean('low_stock', false);

        // Get suppliers - Untuk workflow, supplier bisa dipilih di Finance F1 jadi optional di create
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

    // Store PO baru - Modified untuk workflow
    // public function store(Request $request)
    // {
    //     if (!$this->canUserAccess('create')) {
    //         return redirect()->back()->with('error', 'Hanya Logistik yang dapat membuat PO baru.');
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'po_number' => 'required|string|max:50|unique:purchase_orders,po_number',
    //         'supplier_id' => 'nullable|string|exists:suppliers,supplier_id', // Optional di create
    //         'po_date' => 'required|date',
    //         'expected_date' => 'nullable|date|after_or_equal:po_date',
    //         'notes' => 'nullable|string',
    //         'items' => 'required|array|min:1',
    //         'items.*.item_id' => 'required|string|exists:items,item_id',
    //         'items.*.quantity' => 'required|integer|min:1',
    //         'items.*.unit_price' => 'required|numeric|min:0',
    //         'items.*.notes' => 'nullable|string',
    //     ], [
    //         'po_number.required' => 'Nomor PO wajib diisi.',
    //         'po_number.unique' => 'Nomor PO sudah digunakan.',
    //         'po_date.required' => 'Tanggal PO wajib diisi.',
    //         'items.required' => 'Items wajib diisi.',
    //         'items.min' => 'Minimal 1 item harus dipilih.',
    //     ]);

    //     if ($validator->fails()) {
    //         return back()
    //             ->withErrors($validator)
    //             ->withInput();
    //     }

    //     try {
    //         DB::beginTransaction();

    //         // Generate PO ID
    //         $poId = $this->generatePOId();

    //         // Create Purchase Order dengan workflow status
    //         $po = PurchaseOrder::create([
    //             'po_id' => $poId,
    //             'po_number' => $request->po_number,
    //             'supplier_id' => $request->supplier_id, // Optional
    //             'po_date' => $request->po_date,
    //             'expected_date' => $request->expected_date,
    //             'status' => 'draft', // Backward compatibility
    //             'workflow_status' => PurchaseOrderConstants::WORKFLOW_STATUS_DRAFT_LOGISTIC,
    //             'total_amount' => 0,
    //             'notes' => $request->notes,
    //             'created_by' => Auth::user()->user_id,
    //         ]);

    //         // Create PO Details
    //         $totalAmount = 0;
    //         foreach ($request->items as $itemData) {
    //             $detailId = PoDetail::generateDetailId();
    //             $totalPrice = $itemData['quantity'] * $itemData['unit_price'];

    //             PoDetail::create([
    //                 'po_detail_id' => $detailId,
    //                 'po_id' => $po->po_id,
    //                 'item_id' => $itemData['item_id'],
    //                 'quantity_ordered' => $itemData['quantity'],
    //                 'unit_price' => $itemData['unit_price'],
    //                 'total_price' => $totalPrice,
    //                 'quantity_received' => 0,
    //                 'notes' => $itemData['notes'] ?? null,
    //             ]);

    //             $totalAmount += $totalPrice;
    //         }

    //         // Update total amount
    //         $po->update(['total_amount' => $totalAmount]);

    //         // Log activity
    //         ActivityLog::logActivity('purchase_orders', $po->po_id, 'create', null, $po->toArray());

    //         DB::commit();

    //         return redirect()->route('purchase-orders.show', $po)
    //             ->with('success', 'Purchase Order berhasil dibuat! Status: Draft Logistik');
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         return back()
    //             ->withInput()
    //             ->with('error', 'Gagal membuat PO: ' . $e->getMessage());
    //     }
    // }

    // Store PO baru - Modified: Hide price from frontend, default to 0
    public function store(Request $request)
    {
        if (!$this->canUserAccess('create')) {
            return redirect()->back()->with('error', 'Hanya Logistik yang dapat membuat PO baru.');
        }

        $validator = Validator::make($request->all(), [
            'po_number' => 'required|string|max:50|unique:purchase_orders,po_number',
            'supplier_id' => 'nullable|string|exists:suppliers,supplier_id',
            'po_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:po_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|string|exists:items,item_id',
            'items.*.quantity' => 'required|integer|min:1',
            // ✅ NEW: Validation untuk notes
            'items.*.notes_type' => 'nullable|string|in:stock_menipis,manual',
            'items.*.notes' => 'nullable|string|max:255',
        ], [
            'po_number.required' => 'Nomor PO wajib diisi.',
            'po_number.unique' => 'Nomor PO sudah digunakan.',
            'po_date.required' => 'Tanggal PO wajib diisi.',
            'items.required' => 'Items wajib diisi.',
            'items.min' => 'Minimal 1 item harus dipilih.',
            // ✅ NEW: Error messages untuk notes
            'items.*.notes_type.in' => 'Jenis catatan harus stock_menipis atau manual.',
            'items.*.notes.max' => 'Notes item maksimal 255 karakter.',
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

            // ✅ CHECK: Auto-approval untuk SUP001
            $supplierId = $request->supplier_id;
            $isAutoApproval = ($supplierId === 'SUP001');

            // Set workflow status berdasarkan supplier
            $workflowStatus = $isAutoApproval
                ? PurchaseOrderConstants::WORKFLOW_STATUS_APPROVED
                : PurchaseOrderConstants::WORKFLOW_STATUS_DRAFT_LOGISTIC;

            $status = $isAutoApproval ? 'sent' : 'draft';

            // Create Purchase Order
            $createData = [
                'po_id' => $poId,
                'po_number' => $request->po_number,
                'supplier_id' => $request->supplier_id,
                'po_date' => $request->po_date,
                'expected_date' => $request->expected_date,
                'status' => $status,
                'workflow_status' => $workflowStatus,
                'total_amount' => 0, // ✅ DEFAULT: Always 0 since no price from frontend
                'notes' => $request->notes,
                'created_by' => Auth::user()->user_id,
            ];

            // ✅ CONDITIONAL: Add workflow & payment data ONLY untuk SUP001
            if ($isAutoApproval) {
                $createData = array_merge($createData, [
                    // Auto-fill workflow data untuk SUP001
                    'logistic_user_id' => Auth::user()->user_id,
                    'logistic_approved_at' => now(),
                    'finance_f1_user_id' => Auth::user()->user_id,
                    'finance_f1_approved_at' => now(),
                    'finance_f1_notes' => 'Auto-approved: Trusted supplier SUP001',
                    'finance_f2_user_id' => Auth::user()->user_id,
                    'finance_f2_approved_at' => now(),
                    'finance_f2_notes' => 'Auto-approved: Fast-track for SUP001',

                    // Auto-setup payment untuk SUP001 (amount tetap 0)
                    'payment_method' => 'bank_transfer',
                    'payment_amount' => 0,
                    'bank_name' => 'Bank Mandiri',
                    'account_number' => '1234567890123',
                    'account_holder' => 'SUP001 Corporation',
                    'payment_due_date' => now()->addDays(30),
                    'payment_status' => PurchaseOrderConstants::PAYMENT_STATUS_PENDING,
                ]);
            }
            // ✅ IMPORTANT: For NON-SUP001 suppliers, payment fields akan NULL/default (normal draft flow)

            $po = PurchaseOrder::create($createData);

            // Create PO Details - ✅ PRICE ALWAYS 0 + NEW NOTES SUPPORT
            foreach ($request->items as $itemData) {
                $detailId = PoDetail::generateDetailId();

                // ✅ NEW: PROCESS NOTES berdasarkan type
                $finalNotes = '';
                if (isset($itemData['notes_type']) && !empty($itemData['notes_type'])) {
                    if ($itemData['notes_type'] === 'stock_menipis') {
                        $finalNotes = 'Stock menipis - perlu segera dipesan';
                    } elseif ($itemData['notes_type'] === 'manual' && !empty($itemData['notes'])) {
                        $finalNotes = trim($itemData['notes']);
                    }
                }

                PoDetail::create([
                    'po_detail_id' => $detailId,
                    'po_id' => $po->po_id,
                    'item_id' => $itemData['item_id'],
                    'quantity_ordered' => $itemData['quantity'],
                    'unit_price' => 0, // ✅ DEFAULT: Always 0 (no price from frontend)
                    'total_price' => 0, // ✅ DEFAULT: Always 0 (quantity * 0)
                    'quantity_received' => 0,
                    'notes' => $finalNotes, // ✅ NEW: Support for item-specific notes
                ]);
            }

            // ✅ NO NEED TO UPDATE: total_amount tetap 0

            // ✅ NEW: Calculate statistics untuk logging
            $totalItems = count($request->items);
            $itemsWithNotes = collect($request->items)->filter(function ($item) {
                return !empty($item['notes_type']);
            })->count();
            $stockMenipisItems = collect($request->items)->filter(function ($item) {
                return isset($item['notes_type']) && $item['notes_type'] === 'stock_menipis';
            })->count();
            $manualNotesItems = collect($request->items)->filter(function ($item) {
                return isset($item['notes_type']) && $item['notes_type'] === 'manual';
            })->count();

            // ✅ ENHANCED ACTIVITY LOGGING
            if ($isAutoApproval) {
                ActivityLog::logActivity('purchase_orders', $po->po_id, 'create_auto_approved', null, [
                    'supplier_id' => $supplierId,
                    'auto_approval_reason' => 'Trusted supplier SUP001 - automatic approval',
                    'workflow_status' => 'approved',
                    'total_amount' => 0, // No price tracking
                    'total_items' => $totalItems,
                    'items_with_notes' => $itemsWithNotes,
                    'stock_menipis_items' => $stockMenipisItems,
                    'manual_notes_items' => $manualNotesItems,
                    'note' => 'PO created without pricing - quantities only with notes support'
                ]);
            } else {
                ActivityLog::logActivity('purchase_orders', $po->po_id, 'create', null, array_merge($po->toArray(), [
                    'total_items' => $totalItems,
                    'items_with_notes' => $itemsWithNotes,
                    'stock_menipis_items' => $stockMenipisItems,
                    'manual_notes_items' => $manualNotesItems,
                    'note' => 'PO created without pricing - quantities only with notes support'
                ]));
            }

            DB::commit();

            // ✅ ENHANCED SUCCESS MESSAGE with notes info
            $successMessage = 'Purchase Order berhasil dibuat!';
            if ($isAutoApproval) {
                $successMessage .= ' 🚀 PO untuk SUP001 telah otomatis disetujui dan siap dikirim!';
            } else {
                $successMessage .= ' Status: Draft Logistic';
            }

            // Add notes info to success message
            if ($itemsWithNotes > 0) {
                $successMessage .= " (${itemsWithNotes} item dengan catatan)";
            }

            return redirect()->route('purchase-orders.show', $po)
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollback();

            // ✅ ENHANCED ERROR LOGGING
            Log::error('Failed to create Purchase Order', [
                'user_id' => Auth::user()->user_id,
                'error' => $e->getMessage(),
                'request_data' => $request->except(['_token']),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal membuat PO: ' . $e->getMessage());
        }
    }

    // Update PO - Modified: Hide price from frontend, default to 0, support notes
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$this->canUserAccess('edit_logistic', $purchaseOrder)) {
            return back()->with('error', 'Anda tidak dapat mengedit PO ini. PO harus dalam status Draft Logistic.');
        }

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'nullable|string|exists:suppliers,supplier_id',
            'po_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:po_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|string|exists:items,item_id',
            'items.*.quantity' => 'required|integer|min:1',
            // ✅ NEW: Notes validation
            'items.*.notes_type' => 'nullable|string|in:stock_menipis,manual',
            'items.*.notes' => 'nullable|string|max:255',
        ], [
            'supplier_id.exists' => 'Supplier yang dipilih tidak valid.',
            'po_date.required' => 'Tanggal PO wajib diisi.',
            'items.required' => 'Items wajib diisi.',
            'items.min' => 'Minimal 1 item harus dipilih.',
            'items.*.quantity.min' => 'Quantity harus minimal 1.',
            'items.*.notes_type.in' => 'Jenis catatan harus stock_menipis atau manual.',
            'items.*.notes.max' => 'Notes item maksimal 255 karakter.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $oldData = $purchaseOrder->toArray();
            $oldSupplierId = $purchaseOrder->supplier_id;
            $newSupplierId = $request->supplier_id;

            // ✅ AUTO-APPROVAL LOGIC (same as store)
            $wasAutoApproval = ($oldSupplierId === 'SUP001');
            $isAutoApproval = ($newSupplierId === 'SUP001');

            $newWorkflowStatus = $purchaseOrder->workflow_status;
            $newStatus = $purchaseOrder->status;

            if (!$wasAutoApproval && $isAutoApproval) {
                $newWorkflowStatus = PurchaseOrderConstants::WORKFLOW_STATUS_APPROVED;
                $newStatus = 'sent';
            } elseif ($wasAutoApproval && !$isAutoApproval) {
                $newWorkflowStatus = PurchaseOrderConstants::WORKFLOW_STATUS_DRAFT_LOGISTIC;
                $newStatus = 'draft';
            }

            // Update PO header
            $updateData = [
                'supplier_id' => $request->supplier_id,
                'po_date' => $request->po_date,
                'expected_date' => $request->expected_date,
                'notes' => $request->notes,
                'workflow_status' => $newWorkflowStatus,
                'status' => $newStatus,
                'total_amount' => 0, // ✅ ALWAYS 0: No pricing
            ];

            // ✅ Handle auto-approval workflow data (same logic but amount = 0)
            if (!$wasAutoApproval && $isAutoApproval) {
                $updateData = array_merge($updateData, [
                    'logistic_user_id' => Auth::user()->user_id,
                    'logistic_approved_at' => now(),
                    'finance_f1_user_id' => Auth::user()->user_id,
                    'finance_f1_approved_at' => now(),
                    'finance_f1_notes' => 'Auto-approved: Changed to trusted supplier SUP001',
                    'finance_f2_user_id' => Auth::user()->user_id,
                    'finance_f2_approved_at' => now(),
                    'finance_f2_notes' => 'Auto-approved: Fast-track for SUP001',
                    'payment_method' => 'bank_transfer',
                    'payment_amount' => 0, // ✅ ALWAYS 0
                    'bank_name' => 'Bank Mandiri',
                    'account_number' => '1234567890123',
                    'account_holder' => 'SUP001 Corporation',
                    'payment_due_date' => now()->addDays(30),
                    'payment_status' => PurchaseOrderConstants::PAYMENT_STATUS_PENDING,
                ]);
            } elseif ($wasAutoApproval && !$isAutoApproval) {
                $updateData = array_merge($updateData, [
                    'logistic_user_id' => null,
                    'logistic_approved_at' => null,
                    'finance_f1_user_id' => null,
                    'finance_f1_approved_at' => null,
                    'finance_f1_notes' => null,
                    'finance_f2_user_id' => null,
                    'finance_f2_approved_at' => null,
                    'finance_f2_notes' => null,
                    'payment_method' => null,
                    'payment_amount' => 0, // ✅ RESET TO 0
                    'bank_name' => null,
                    'account_number' => null,
                    'account_holder' => null,
                    'payment_due_date' => null,
                    'payment_status' => null,
                ]);
            }

            $purchaseOrder->update($updateData);

            // ✅ PRESERVE RECEIVED QUANTITIES: Get existing received quantities before deletion
            $existingReceived = [];
            foreach ($purchaseOrder->poDetails as $detail) {
                $existingReceived[$detail->item_id] = $detail->quantity_received;
            }

            // Delete existing details
            $purchaseOrder->poDetails()->delete();

            // ✅ Create new details with NOTES SUPPORT and preserve received quantities
            foreach ($request->items as $itemData) {
                $detailId = PoDetail::generateDetailId();

                // ✅ NEW: PROCESS NOTES berdasarkan type
                $finalNotes = '';
                if (isset($itemData['notes_type']) && !empty($itemData['notes_type'])) {
                    if ($itemData['notes_type'] === 'stock_menipis') {
                        $finalNotes = 'Stock menipis - perlu segera dipesan';
                    } elseif ($itemData['notes_type'] === 'manual' && !empty($itemData['notes'])) {
                        $finalNotes = trim($itemData['notes']);
                    }
                }

                // ✅ PRESERVE: Keep existing received quantity if item exists
                $quantityReceived = $existingReceived[$itemData['item_id']] ?? 0;

                // ✅ VALIDATION: Ensure new quantity is not less than received
                if ($itemData['quantity'] < $quantityReceived) {
                    throw new \Exception(
                        "Quantity untuk item {$itemData['item_id']} tidak boleh kurang dari yang sudah diterima ({$quantityReceived})"
                    );
                }

                PoDetail::create([
                    'po_detail_id' => $detailId,
                    'po_id' => $purchaseOrder->po_id,
                    'item_id' => $itemData['item_id'],
                    'quantity_ordered' => $itemData['quantity'],
                    'unit_price' => 0, // ✅ DEFAULT: Always 0
                    'total_price' => 0, // ✅ DEFAULT: Always 0
                    'quantity_received' => $quantityReceived, // ✅ PRESERVE: Keep existing received
                    'notes' => $finalNotes, // ✅ NEW: Support for item-specific notes
                ]);
            }

            // ✅ NEW: Calculate statistics untuk logging
            $totalItems = count($request->items);
            $itemsWithNotes = collect($request->items)->filter(function ($item) {
                return !empty($item['notes_type']);
            })->count();
            $stockMenipisItems = collect($request->items)->filter(function ($item) {
                return isset($item['notes_type']) && $item['notes_type'] === 'stock_menipis';
            })->count();
            $manualNotesItems = collect($request->items)->filter(function ($item) {
                return isset($item['notes_type']) && $item['notes_type'] === 'manual';
            })->count();

            // ✅ ENHANCED ACTIVITY LOGGING
            $activityData = [
                'old_supplier' => $oldSupplierId,
                'new_supplier' => $newSupplierId,
                'old_workflow_status' => $oldData['workflow_status'],
                'new_workflow_status' => $newWorkflowStatus,
                'total_amount' => 0, // No pricing
                'total_items' => $totalItems,
                'items_with_notes' => $itemsWithNotes,
                'stock_menipis_items' => $stockMenipisItems,
                'manual_notes_items' => $manualNotesItems,
                'note' => 'PO updated without pricing - quantities only with notes support'
            ];

            if (!$wasAutoApproval && $isAutoApproval) {
                ActivityLog::logActivity(
                    'purchase_orders',
                    $purchaseOrder->po_id,
                    'update_with_auto_approval',
                    $oldData,
                    array_merge($activityData, [
                        'auto_approval_reason' => 'Supplier changed to SUP001 - automatic approval triggered',
                        'action' => 'supplier_change_to_trusted'
                    ])
                );
            } elseif ($wasAutoApproval && !$isAutoApproval) {
                ActivityLog::logActivity(
                    'purchase_orders',
                    $purchaseOrder->po_id,
                    'update_remove_auto_approval',
                    $oldData,
                    array_merge($activityData, [
                        'auto_approval_removed_reason' => 'Supplier changed from SUP001 - reverted to manual workflow',
                        'action' => 'supplier_change_from_trusted'
                    ])
                );
            } else {
                ActivityLog::logActivity('purchase_orders', $purchaseOrder->po_id, 'update', $oldData, $activityData);
            }

            DB::commit();

            // ✅ ENHANCED CONDITIONAL SUCCESS MESSAGE with notes info
            $successMessage = '';
            if (!$wasAutoApproval && $isAutoApproval) {
                $successMessage = 'PO berhasil diupdate! 🚀 Supplier diubah ke SUP001 - PO otomatis disetujui!';
            } elseif ($wasAutoApproval && !$isAutoApproval) {
                $successMessage = 'PO berhasil diupdate! Supplier diubah dari SUP001 - PO kembali ke status Draft Logistic.';
            } else {
                $successMessage = 'Purchase Order berhasil diupdate!';
            }

            // Add notes info to success message
            if ($itemsWithNotes > 0) {
                $successMessage .= " (${itemsWithNotes} item dengan catatan)";
            }

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollback();

            // ✅ ENHANCED ERROR LOGGING
            \Log::error('Failed to update Purchase Order', [
                'po_id' => $purchaseOrder->po_id,
                'user_id' => Auth::user()->user_id,
                'error' => $e->getMessage(),
                'request_data' => $request->except(['_token', '_method']),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate PO: ' . $e->getMessage());
        }
    }

    // ✅ HELPER METHOD: Check if supplier should get auto-approval
    private function shouldAutoApprove($supplierId): bool
    {
        // List of trusted suppliers that get auto-approval
        $trustedSuppliers = [
            'SUP001', // Main trusted supplier
            // 'SUP002', // Bisa ditambah supplier lain
            // 'SUP005', // Internal company supplier
        ];

        return in_array($supplierId, $trustedSuppliers);
    }

    // ✅ ENHANCED: Get auto-approval configuration
    private function getAutoApprovalConfig($supplierId): array
    {
        $configs = [
            'SUP001' => [
                'payment_method' => 'bank_transfer',
                'bank_name' => 'Bank Mandiri',
                'account_number' => '1234567890123',
                'account_holder' => 'SUP001 Corporation',
                'payment_days' => 30,
                'notes_f1' => 'Auto-approved: Trusted supplier with verified payment terms',
                'notes_f2' => 'Auto-approved: Pre-approved supplier under corporate agreement'
            ],
            // Bisa ditambah config untuk supplier lain
        ];

        return $configs[$supplierId] ?? [];
    }

    // Tampilkan detail PO - Updated untuk workflow
    public function show(PurchaseOrder $purchaseOrder)
    {
        if (!$this->canUserAccess('view')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat PO ini.');
        }

        $purchaseOrder->load([
            'supplier',
            'createdBy.userLevel',
            'logisticUser',
            'financeF1User',
            'financeF2User',
            'poDetails.item.category',
            'goodsReceived' => function ($query) {
                $query->latest()->take(5);
            }
        ]);

        // Summary info
        $summaryInfo = $purchaseOrder->getSummaryInfo();

        // Workflow status info
        $workflowStatusInfo = $purchaseOrder->getWorkflowStatusInfo();

        // Payment status info (jika ada)
        $paymentStatusInfo = $purchaseOrder->payment_status ? $purchaseOrder->getPaymentStatusInfo() : null;

        // User permissions untuk button visibility
        $userLevel = Auth::user()->user_level_id ?? null;
        $permissions = [
            'can_edit_logistic' => $this->canUserAccess('edit_logistic', $purchaseOrder),
            'can_process_f1' => $this->canUserAccess('process_f1', $purchaseOrder),
            'can_process_f2' => $this->canUserAccess('process_f2', $purchaseOrder),
            'can_reject_f1' => $this->canUserAccess('reject_f1', $purchaseOrder),
            'can_reject_f2' => $this->canUserAccess('reject_f2', $purchaseOrder),
            'can_return_from_reject' => $this->canUserAccess('return_from_reject', $purchaseOrder),
            'can_cancel' => $userLevel === 'LVL001' && $purchaseOrder->canBeCancelled(),
        ];

        return view('purchase-orders.show', compact(
            'purchaseOrder',
            'summaryInfo',
            'workflowStatusInfo',
            'paymentStatusInfo',
            'permissions'
        ));
    }

    // Tampilkan form edit PO - Updated untuk workflow
    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (!$this->canUserAccess('edit_logistic', $purchaseOrder)) {
            return back()->with('error', 'Anda tidak dapat mengedit PO ini. PO harus dalam status Draft Logistik.');
        }

        $purchaseOrder->load(['poDetails.item']);

        $suppliers = Supplier::active()->orderBy('supplier_name')->get();
        $items = Item::active()->with(['category', 'stock'])->orderBy('item_name')->get();

        return view('purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'items'));
    }



    // NEW: Submit to Finance F1 (Logistik action)
    public function submitToFinanceF1(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$this->canUserAccess('edit_logistic', $purchaseOrder)) {
            return back()->with('error', 'Anda tidak dapat mensubmit PO ini.');
        }

        try {
            $result = $purchaseOrder->submitToFinanceF1(Auth::user()->user_id);

            if ($result) {
                ActivityLog::logActivity(
                    'purchase_orders',
                    $purchaseOrder->po_id,
                    'submit_to_f1',
                    ['old_status' => PurchaseOrderConstants::WORKFLOW_STATUS_DRAFT_LOGISTIC],
                    ['new_status' => PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F1]
                );

                return back()->with('success', 'PO berhasil disubmit ke Finance F1!');
            }

            return back()->with('error', 'Gagal submit PO ke Finance F1.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // NEW: Process Finance F1 (Finance F1 action)
    public function processFinanceF1(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$this->canUserAccess('process_f1', $purchaseOrder)) {
            return back()->with('error', 'Anda tidak dapat memproses PO ini di level Finance F1.');
        }

        $validator = Validator::make($request->all(), [
            // Basic F1 requirements
            'supplier_id' => 'required|string|exists:suppliers,supplier_id',
            'payment_method' => 'required|string|in:' . implode(',', array_keys(PurchaseOrderConstants::getPaymentMethods())),
            'payment_amount' => 'required|numeric|min:0',
            'finance_f1_notes' => 'nullable|string|max:1000',

            // Payment details validation - conditional based on payment method
            'virtual_account_number' => 'required_if:payment_method,' . PurchaseOrderConstants::PAYMENT_METHOD_VIRTUAL_ACCOUNT . '|nullable|string',
            'bank_name' => 'required_if:payment_method,' . PurchaseOrderConstants::PAYMENT_METHOD_VIRTUAL_ACCOUNT . ',' . PurchaseOrderConstants::PAYMENT_METHOD_BANK_TRANSFER . ',' . PurchaseOrderConstants::PAYMENT_METHOD_CHECK . ',' . PurchaseOrderConstants::PAYMENT_METHOD_CREDIT_CARD . '|nullable|string',
            'account_number' => 'required_if:payment_method,' . PurchaseOrderConstants::PAYMENT_METHOD_BANK_TRANSFER . '|nullable|string',
            'account_holder' => 'required_if:payment_method,' . PurchaseOrderConstants::PAYMENT_METHOD_BANK_TRANSFER . '|nullable|string',
            'payment_due_date' => 'nullable|date|after:today',
        ], [
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_amount.required' => 'Jumlah pembayaran wajib diisi.',
            'payment_amount.min' => 'Jumlah pembayaran tidak boleh negatif.',
            'virtual_account_number.required_if' => 'Nomor Virtual Account wajib diisi untuk metode Virtual Account.',
            'bank_name.required_if' => 'Nama Bank wajib diisi untuk metode pembayaran ini.',
            'account_number.required_if' => 'Nomor Rekening wajib diisi untuk Bank Transfer.',
            'account_holder.required_if' => 'Nama Pemegang Rekening wajib diisi untuk Bank Transfer.',
            'payment_due_date.after' => 'Tanggal jatuh tempo harus setelah hari ini.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $result = $purchaseOrder->processFinanceF1(Auth::user()->user_id, [
                // Basic info
                'supplier_id' => $request->supplier_id,
                'notes' => $request->finance_f1_notes,

                // Payment details - ALL handled by F1 now
                'payment_method' => $request->payment_method,
                'payment_amount' => $request->payment_amount,
                'virtual_account_number' => $request->virtual_account_number,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder' => $request->account_holder,
                'payment_due_date' => $request->payment_due_date,
            ]);

            if ($result) {
                ActivityLog::logActivity(
                    'purchase_orders',
                    $purchaseOrder->po_id,
                    'process_f1',
                    ['old_status' => PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F1],
                    [
                        'new_status' => PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F2,
                        'supplier_data' => ['supplier_id' => $request->supplier_id],
                        'payment_data' => $request->except(['_token', 'supplier_id', 'finance_f1_notes'])
                    ]
                );

                return back()->with('success', 'PO berhasil diproses oleh Finance F1! Payment details sudah di-setup. Menunggu approval FINANCE RBP.');
            }

            return back()->with('error', 'Gagal memproses PO di Finance F1.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // NEW: Approve FINANCE RBP (FINANCE RBP action)
    public function approveFinanceF2(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$this->canUserAccess('process_f2', $purchaseOrder)) {
            return back()->with('error', 'Anda tidak dapat approve PO ini di level FINANCE RBP.');
        }

        $validator = Validator::make($request->all(), [
            'finance_f2_notes' => 'nullable|string|max:1000',
            'final_approval' => 'required|boolean', // Just to make sure it's intentional approval
        ], [
            'final_approval.required' => 'Konfirmasi approval diperlukan.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $result = $purchaseOrder->approveFinanceF2(Auth::user()->user_id, [
                'notes' => $request->finance_f2_notes,
            ]);

            if ($result) {
                // Update backward compatibility status
                $purchaseOrder->update(['status' => 'sent']);

                ActivityLog::logActivity(
                    'purchase_orders',
                    $purchaseOrder->po_id,
                    'approve_f2',
                    ['old_status' => PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F2],
                    [
                        'new_status' => PurchaseOrderConstants::WORKFLOW_STATUS_APPROVED,
                        'approved_by' => Auth::user()->user_id,
                        'notes' => $request->finance_f2_notes
                    ]
                );

                return back()->with('success', 'PO berhasil di-approve FINANCE RBP! PO siap dikirim ke supplier.');
            }

            return back()->with('error', 'Gagal approve PO di FINANCE RBP.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // NEW: Reject by Finance F1
    public function rejectFinanceF1(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$this->canUserAccess('reject_f1', $purchaseOrder)) {
            return back()->with('error', 'Anda tidak dapat reject PO ini di level Finance F1.');
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|min:5|max:1000',
        ]);


        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        //  dd($request->all());
        try {
            $result = $purchaseOrder->rejectByFinanceF1(Auth::user()->user_id, $request->rejection_reason);

            if ($result) {
                ActivityLog::logActivity(
                    'purchase_orders',
                    $purchaseOrder->po_id,
                    'reject_f1',
                    ['old_status' => PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F1],
                    ['new_status' => PurchaseOrderConstants::WORKFLOW_STATUS_REJECTED_F1, 'reason' => $request->rejection_reason]
                );

                return back()->with('success', 'PO berhasil di-reject oleh Finance F1.');
            }

            return back()->with('error', 'Gagal reject PO.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // NEW: Reject by FINANCE RBP
    public function rejectFinanceF2(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$this->canUserAccess('reject_f2', $purchaseOrder)) {
            return back()->with('error', 'Anda tidak dapat reject PO ini di level FINANCE RBP.');
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|min:3|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $result = $purchaseOrder->rejectByFinanceF2(Auth::user()->user_id, $request->rejection_reason);

            if ($result) {
                ActivityLog::logActivity(
                    'purchase_orders',
                    $purchaseOrder->po_id,
                    'reject_f2',
                    ['old_status' => PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F2],
                    ['new_status' => PurchaseOrderConstants::WORKFLOW_STATUS_REJECTED_F2, 'reason' => $request->rejection_reason]
                );

                return back()->with('success', 'PO berhasil di-reject oleh FINANCE RBP.');
            }

            return back()->with('error', 'Gagal reject PO.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // NEW: Return from Reject (Manual button - Admin only)
    public function returnFromReject(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$this->canUserAccess('return_from_reject', $purchaseOrder)) {
            return back()->with('error', 'Hanya Admin yang dapat mengembalikan PO dari status reject.');
        }

        try {
            $oldStatus = $purchaseOrder->workflow_status;
            $result = $purchaseOrder->returnToLogistic();
            // dd($result);

            if ($result) {
                ActivityLog::logActivity(
                    'purchase_orders',
                    $purchaseOrder->po_id,
                    'return_from_reject',
                    ['old_status' => $oldStatus],
                    ['new_status' => PurchaseOrderConstants::WORKFLOW_STATUS_DRAFT_LOGISTIC, 'returned_by' => Auth::user()->user_id]
                );

                return back()->with('success', 'PO berhasil dikembalikan ke status Draft Logistik.');
            }

            return back()->with('error', 'Gagal mengembalikan PO.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // KEPT: Update status PO - Modified untuk backward compatibility
    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Hanya admin yang bisa manual update status (backward compatibility)
        if (Auth::user()->user_level_id !== 'LVL001') {
            return back()->with('error', 'Hanya Admin yang dapat mengubah status secara manual.');
        }

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

    // KEPT: Cancel PO - Modified untuk workflow
    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Hanya admin yang bisa cancel
        if (Auth::user()->user_level_id !== 'LVL001') {
            return back()->with('error', 'Hanya Admin yang dapat membatalkan PO.');
        }

        if (!$purchaseOrder->canBeCancelled()) {
            return back()->with('error', 'PO tidak dapat dibatalkan.');
        }

        try {
            $oldData = $purchaseOrder->toArray();

            $purchaseOrder->update([
                'status' => 'cancelled',
                'workflow_status' => PurchaseOrderConstants::WORKFLOW_STATUS_CANCELLED,
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

    // KEPT: Send PO - Modified untuk workflow compatibility
    public function send(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Hanya untuk PO yang sudah approved atau admin override
        $userLevel = Auth::user()->user_level_id;

        if ($userLevel !== 'LVL001' && $purchaseOrder->workflow_status !== PurchaseOrderConstants::WORKFLOW_STATUS_APPROVED) {
            return back()->with('error', 'PO harus sudah di-approve FINANCE RBP terlebih dahulu.');
        }

        try {
            $oldData = $purchaseOrder->toArray();

            $purchaseOrder->update([
                'status' => 'sent',
                'workflow_status' => PurchaseOrderConstants::WORKFLOW_STATUS_SENT,
                'notes' => $request->notes
            ]);

            // Log activity
            ActivityLog::logActivity('purchase_orders', $oldData->po_id, 'send', $oldData, [
                'sent_by' => Auth::user()->user_id,
                'notes' => $request->notes
            ]);

            return back()->with('success', 'PO berhasil dikirim ke supplier!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim PO: ' . $e->getMessage());
        }
    }

    // KEPT: Print PO (unchanged)
    public function print(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'createdBy', 'poDetails.item']);

        // Nanti bisa diintegrasikan dengan library PDF seperti TCPDF atau DomPDF
        return view('purchase-orders.print', compact('purchaseOrder'));
    }


    public function downloadPDF(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'createdBy',
            'poDetails.item.category'
        ]);

        // Buat PDF
        $pdf = PDF::loadView('purchase-orders.pdf', compact('purchaseOrder'));

        // Set paper size dan orientasi
        $pdf->setPaper('A4', 'portrait');

        // Set options untuk kualitas PDF
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial'
        ]);

        // Generate filename
        $filename = 'PO_' . $purchaseOrder->po_number . '_' . now()->format('Y-m-d') . '.pdf';

        // Download file
        return $pdf->download($filename);
    }

    /**
     * View PDF di browser (tanpa download)
     */
    public function viewPDF(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'createdBy',
            'poDetails.item.category',
            'poDetails.item.itemDetails' // Load untuk hitung stock
        ]);

        // Calculate stock info untuk setiap item
        foreach ($purchaseOrder->poDetails as $detail) {
            $item = $detail->item;
            $itemDetails = $item->itemDetails;

            // Simple calculation sesuai header
            $item->qty_stock = $itemDetails->where('status', 'stock')->count();        // Qty Stock (di gudang)
            $item->qty_ready = $itemDetails->where('status', 'available')->count();    // Qty Siap Pakai
        }

        $pdf = Pdf::loadView('purchase-orders.pdf', compact('purchaseOrder'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('PO_' . $purchaseOrder->po_number . '.pdf');
    }

    // KEPT: API endpoint untuk duplicate PO (unchanged untuk backward compatibility)
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
                'status' => 'draft',
                'workflow_status' => PurchaseOrderConstants::WORKFLOW_STATUS_DRAFT_LOGISTIC,
                'total_amount' => $purchaseOrder->total_amount,
                'notes' => 'Duplikasi dari PO: ' . $purchaseOrder->po_number,
                'created_by' => Auth::user()->user_id,
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

    // KEPT: API endpoint untuk get PO by supplier (unchanged)
    public function getBySupplier(Request $request, $supplierId)
    {
        $pos = PurchaseOrder::bySupplier($supplierId)
            ->active()
            ->select('po_id', 'po_number', 'po_date', 'status', 'workflow_status', 'total_amount')
            ->orderBy('po_date', 'desc')
            ->get();

        return response()->json($pos);
    }

    // NEW: API endpoint untuk get payment methods based on available options
    public function getAvailablePaymentMethods(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->available_payment_options) {
            return response()->json([]);
        }

        $availableMethods = [];
        foreach ($purchaseOrder->available_payment_options as $method) {
            if (isset(PurchaseOrderConstants::getPaymentMethods()[$method])) {
                $availableMethods[$method] = PurchaseOrderConstants::getPaymentMethods()[$method];
            }
        }

        return response()->json($availableMethods);
    }

    // NEW: API endpoint untuk workflow statistics
    public function getWorkflowStatistics()
    {
        $stats = PurchaseOrder::getWorkflowStatistics();
        return response()->json($stats);
    }

    // KEPT: Generate PO ID (unchanged)
    private function generatePOId(): string
    {
        $lastPO = PurchaseOrder::orderBy('po_id', 'desc')->first();
        $lastNumber = $lastPO ? (int) substr($lastPO->po_id, 2) : 0;
        $newNumber = $lastNumber + 1;
        return 'PO' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    // KEPT: Validate status transition (unchanged untuk backward compatibility)
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
