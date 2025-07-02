<?php


// ================================================================
// 2. app/Http/Controllers/SupplierController.php
// ================================================================

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
        // Nanti bisa ditambah permission middleware
        // $this->middleware('permission:suppliers.read')->only(['index', 'show']);
        // $this->middleware('permission:suppliers.create')->only(['create', 'store']);
        // $this->middleware('permission:suppliers.update')->only(['edit', 'update']);
        // $this->middleware('permission:suppliers.delete')->only(['destroy']);
    }

    // Tampilkan daftar suppliers
    public function index(Request $request)
    {
        $query = Supplier::query();

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Sorting
        $sortField = $request->get('sort', 'supplier_name');
        $sortDirection = $request->get('direction', 'asc');

        $allowedSorts = ['supplier_code', 'supplier_name', 'contact_person', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $suppliers = $query->paginate(15)->withQueryString();

        // Stats untuk dashboard
        $stats = [
            'total' => Supplier::count(),
            'active' => Supplier::active()->count(),
            'inactive' => Supplier::where('is_active', false)->count(),
        ];

        return view('suppliers.index', compact('suppliers', 'stats', 'sortField', 'sortDirection'));
    }

    // Tampilkan form create supplier
    public function create()
    {
        $nextCode = Supplier::generateSupplierCode();
        return view('suppliers.create', compact('nextCode'));
    }

    // Store supplier baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_code' => 'required|string|max:20|unique:suppliers,supplier_code',
            'supplier_name' => 'required|string|max:100',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100|unique:suppliers,email',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ], [
            'supplier_code.required' => 'Kode supplier wajib diisi.',
            'supplier_code.unique' => 'Kode supplier sudah digunakan.',
            'supplier_name.required' => 'Nama supplier wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan supplier lain.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Generate supplier ID
            $supplierId = $this->generateSupplierId();

            // Clean phone number
            $phone = $this->cleanPhoneNumber($request->phone);

            $supplier = Supplier::create([
                'supplier_id' => $supplierId,
                'supplier_code' => strtoupper($request->supplier_code),
                'supplier_name' => $request->supplier_name,
                'contact_person' => $request->contact_person,
                'phone' => $phone,
                'email' => $request->email,
                'address' => $request->address,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Log activity
            $this->logActivity('suppliers', $supplier->supplier_id, 'create', null, $supplier->toArray());

            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier berhasil ditambahkan!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menambahkan supplier: ' . $e->getMessage());
        }
    }

    // Tampilkan detail supplier
    public function show(Supplier $supplier)
    {
        // Load relationships untuk detail view
        $supplier->load(['purchaseOrders' => function($query) {
            $query->latest()->take(10);
        }]);

        // Stats supplier
        $stats = [
            'total_po' => $supplier->getTotalPurchaseOrders(),
            'active_po' => $supplier->getActivePurchaseOrders(),
            'contact_info' => $supplier->getContactSummary(),
        ];

        return view('suppliers.show', compact('supplier', 'stats'));
    }

    // Tampilkan form edit supplier
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    // Update supplier
    public function update(Request $request, Supplier $supplier)
    {
        $validator = Validator::make($request->all(), [
            'supplier_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('suppliers', 'supplier_code')->ignore($supplier->supplier_id, 'supplier_id')
            ],
            'supplier_name' => 'required|string|max:100',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('suppliers', 'email')->ignore($supplier->supplier_id, 'supplier_id')
            ],
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ], [
            'supplier_code.required' => 'Kode supplier wajib diisi.',
            'supplier_code.unique' => 'Kode supplier sudah digunakan.',
            'supplier_name.required' => 'Nama supplier wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan supplier lain.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $oldData = $supplier->toArray();

            // Clean phone number
            $phone = $this->cleanPhoneNumber($request->phone);

            $supplier->update([
                'supplier_code' => strtoupper($request->supplier_code),
                'supplier_name' => $request->supplier_name,
                'contact_person' => $request->contact_person,
                'phone' => $phone,
                'email' => $request->email,
                'address' => $request->address,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Log activity
            $this->logActivity('suppliers', $supplier->supplier_id, 'update', $oldData, $supplier->fresh()->toArray());

            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier berhasil diupdate!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate supplier: ' . $e->getMessage());
        }
    }

    // Delete supplier
    public function destroy(Supplier $supplier)
    {
        try {
            // Cek apakah supplier memiliki transaksi
            if ($supplier->hasTransactions()) {
                return back()->with('error', 'Supplier tidak dapat dihapus karena memiliki transaksi Purchase Order!');
            }

            $oldData = $supplier->toArray();
            $supplierId = $supplier->supplier_id;
            $supplier->delete();

            // Log activity
            $this->logActivity('suppliers', $supplierId, 'delete', $oldData, null);

            return back()->with('success', 'Supplier berhasil dihapus!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus supplier: ' . $e->getMessage());
        }
    }

    // Toggle supplier status
    public function toggleStatus(Supplier $supplier)
    {
        try {
            $oldData = $supplier->toArray();
            $supplier->update(['is_active' => !$supplier->is_active]);

            $status = $supplier->is_active ? 'diaktifkan' : 'dinonaktifkan';
            $action = $supplier->is_active ? 'activate' : 'deactivate';

            $this->logActivity('suppliers', $supplier->supplier_id, $action, $oldData, $supplier->fresh()->toArray());

            return back()->with('success', "Supplier berhasil {$status}!");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah status supplier: ' . $e->getMessage());
        }
    }

    // API endpoint untuk search suppliers (untuk dropdown/autocomplete)
    public function search(Request $request)
    {
        $term = $request->get('q', '');
        $limit = $request->get('limit', 10);

        $suppliers = Supplier::active()
            ->search($term)
            ->select('supplier_id', 'supplier_code', 'supplier_name', 'contact_person')
            ->limit($limit)
            ->get();

        return response()->json($suppliers);
    }

    // API endpoint untuk get supplier list (untuk dropdown)
    public function list(Request $request)
    {
        $suppliers = Supplier::active()
            ->select('supplier_id', 'supplier_code', 'supplier_name')
            ->orderBy('supplier_name')
            ->get()
            ->map(function($supplier) {
                return [
                    'value' => $supplier->supplier_id,
                    'label' => $supplier->supplier_code . ' - ' . $supplier->supplier_name
                ];
            });

        return response()->json($suppliers);
    }

    // Generate supplier ID
    private function generateSupplierId(): string
    {
        $lastSupplier = Supplier::orderBy('supplier_id', 'desc')->first();
        $lastNumber = $lastSupplier ? (int) substr($lastSupplier->supplier_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        return 'SUP' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    // Clean phone number
    private function cleanPhoneNumber($phone): ?string
    {
        if (!$phone) return null;

        // Remove all non-digit characters except + and spaces
        $cleaned = preg_replace('/[^\d\+\s\-\(\)]/', '', $phone);

        // Remove extra spaces
        return trim($cleaned);
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
