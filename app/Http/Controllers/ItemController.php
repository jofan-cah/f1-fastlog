<?php

// ================================================================
// 2. app/Http/Controllers/ItemController.php
// ================================================================

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ItemController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
        // Nanti bisa ditambah permission middleware
        // $this->middleware('permission:items.read')->only(['index', 'show']);
        // $this->middleware('permission:items.create')->only(['create', 'store']);
        // $this->middleware('permission:items.update')->only(['edit', 'update']);
        // $this->middleware('permission:items.delete')->only(['destroy']);
    }


    public function indexViewCode(Request $request)
    {
        $query = Item::with(['category', 'stock']);

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('item_code', 'like', "%{$searchTerm}%")
                  ->orWhere('item_name', 'like', "%{$searchTerm}%")
                  ->orWhereHas('category', function($subQ) use ($searchTerm) {
                      $subQ->where('code_category', 'like', "%{$searchTerm}%")
                           ->orWhere('category_name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $items = $query->orderBy('item_name')->paginate(20)->withQueryString();

        // Get categories for filter
        $categories = Category::active()->orderBy('category_name')->get();

        return view('items.indexCode', compact('items', 'categories'));
    }

    /**
     * Export items to Excel
     */
    public function exportExcel(Request $request)
    {
        // Get filtered data (same as index but without pagination)
        $query = Item::with(['category', 'stock']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('item_code', 'like', "%{$searchTerm}%")
                  ->orWhere('item_name', 'like', "%{$searchTerm}%")
                  ->orWhereHas('category', function($subQ) use ($searchTerm) {
                      $subQ->where('code_category', 'like', "%{$searchTerm}%")
                           ->orWhere('category_name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $items = $query->orderBy('item_name')->get();

        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('Inventory System')
            ->setTitle('Data Items dan Categories')
            ->setDescription('Export data items dengan category codes');

        // Header
        $headers = [
            'A1' => 'No',
            'B1' => 'Item ID',
            'C1' => 'Item Code',
            'D1' => 'Item Name',
            'E1' => 'Category Code',
            'F1' => 'Category Name',
            'G1' => 'Unit',
            'H1' => 'Min Stock',
            'I1' => 'Current Stock',
            'J1' => 'Stock Status',
            'K1' => 'Status',
            'L1' => 'Description'
        ];

        // Set headers
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style headers
        $headerRange = 'A1:L1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);

        // Data rows
        $row = 2;
        foreach ($items as $index => $item) {
            $stockInfo = $item->getStockInfo();

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->item_id);
            $sheet->setCellValue('C' . $row, $item->item_code);
            $sheet->setCellValue('D' . $row, $item->item_name);
            $sheet->setCellValue('E' . $row, $item->category->code_category ?? '-');
            $sheet->setCellValue('F' . $row, $item->category->category_name ?? '-');
            $sheet->setCellValue('G' . $row, $item->unit);
            $sheet->setCellValue('H' . $row, $item->min_stock);
            $sheet->setCellValue('I' . $row, $stockInfo['available']);
            $sheet->setCellValue('J' . $row, $stockInfo['status_text']);
            $sheet->setCellValue('K' . $row, $item->is_active ? 'Aktif' : 'Nonaktif');
            $sheet->setCellValue('L' . $row, $item->description ?? '-');

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders to all data
        $dataRange = 'A1:L' . ($row - 1);
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);

        // Generate filename
        $filename = 'items_export_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Save and download
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    // Tampilkan daftar items
    public function index(Request $request)
    {
        $query = Item::with(['category', 'stock']);

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Category filter
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Stock filter
        if ($request->filled('stock_filter')) {
            switch ($request->stock_filter) {
                case 'low':
                    $query->lowStock();
                    break;
                case 'empty':
                    $query->withoutStock();
                    break;
                case 'available':
                    $query->whereHas('stock', function ($q) {
                        $q->where('quantity_available', '>', 0);
                    });
                    break;
            }
        }

        // Sorting
        $sortField = $request->get('sort', 'item_name');
        $sortDirection = $request->get('direction', 'asc');

        $allowedSorts = ['item_code', 'item_name', 'category_id', 'min_stock', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            if ($sortField === 'category_id') {
                $query->join('categories', 'items.category_id', '=', 'categories.category_id')
                    ->orderBy('categories.category_name', $sortDirection)
                    ->select('items.*');
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
        }

        $items = $query->paginate(15)->withQueryString();

        // Stats untuk dashboard
        $stats = [
            'total' => Item::count(),
            'active' => Item::active()->count(),
            'low_stock' => Item::lowStock()->count(),
            'without_stock' => Item::withoutStock()->count(),
        ];

        // Categories untuk filter dropdown
        $categories = Category::active()
            ->whereHas('items')
            ->withCount('items')
            ->orderBy('category_name')
            ->get();

        return view('items.index', compact('items', 'stats', 'categories', 'sortField', 'sortDirection'));
    }

    // Tampilkan form create item
    public function create()
    {
        $nextCode = Item::generateItemCode();
        // Di ItemController create() dan edit()
        $categories = Category::active()
            ->orderBy('category_name')
            ->get()
            ->map(function ($category) {
                return [
                    'value' => $category->category_id,
                    'label' => $category->category_name
                ];
            });
        $units = Item::getCommonUnits();

        return view('items.create', compact('nextCode', 'categories', 'units'));
    }

    // Store item baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_code' => 'required|string|max:50|unique:items,item_code',
            'item_name' => 'required|string|max:200',
            'category_id' => 'required|string|exists:categories,category_id',
            'unit' => 'required|string|max:20',
            'min_stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'generate_qr' => 'boolean',
        ], [
            'item_code.required' => 'Kode barang wajib diisi.',
            'item_code.unique' => 'Kode barang sudah digunakan.',
            'item_name.required' => 'Nama barang wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid.',
            'unit.required' => 'Satuan wajib diisi.',
            'min_stock.required' => 'Minimum stok wajib diisi.',
            'min_stock.min' => 'Minimum stok tidak boleh negatif.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Generate item ID
            $itemId = $this->generateItemId();

            // Create item
            $item = Item::create([
                'item_id' => $itemId,
                'item_code' => strtoupper($request->item_code),
                'item_name' => $request->item_name,
                'category_id' => $request->category_id,
                'unit' => $request->unit,
                'min_stock' => $request->min_stock,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Generate QR Code jika diminta
            if ($request->boolean('generate_qr', false)) {
                $this->generateQRCode($item);
            }

            // Create initial stock record
            \App\Models\Stock::create([
                'stock_id' => $this->generateStockId(),
                'item_id' => $item->item_id,
                'quantity_available' => 0,
                'quantity_used' => 0,
                'total_quantity' => 0,
            ]);

            // Log activity

            ActivityLog::logActivity('items', $item->item_id, 'create', null, $item->toArray());
            return redirect()->route('items.index')
                ->with('success', 'Barang berhasil ditambahkan!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menambahkan barang: ' . $e->getMessage());
        }
    }

    // Tampilkan detail item
    public function show(Item $item)
    {
        // Load relationships
        $item->load(['category', 'stock', 'itemDetails' => function ($query) {
            $query->latest()->take(10);
        }]);

        // Get stock info
        $stockInfo = $item->getStockInfo();

        // Recent transactions
        $recentTransactions = $item->transactions()
            ->with('createdBy')
            ->latest()
            ->take(10)
            ->get();

        return view('items.show', compact('item', 'stockInfo', 'recentTransactions'));
    }

    // Tampilkan form edit item
    public function edit(Item $item)
    {
        $categories = Category::active()
            ->orderBy('category_name')
            ->get()
            ->map(function ($category) {
                return [
                    'value' => $category->category_id,
                    'label' => $category->category_name
                ];
            });
        $units = Item::getCommonUnits();
        $nextCode = Item::generateItemCode();

        return view('items.edit', compact('item', 'categories', 'units', 'nextCode'));
    }

    // Update item
    public function update(Request $request, Item $item)
    {
        $validator = Validator::make($request->all(), [
            'item_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('items', 'item_code')->ignore($item->item_id, 'item_id')
            ],
            'item_name' => 'required|string|max:200',
            'category_id' => 'required|string|exists:categories,category_id',
            'unit' => 'required|string|max:20',
            'min_stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'generate_qr' => 'boolean',
        ], [
            'item_code.required' => 'Kode barang wajib diisi.',
            'item_code.unique' => 'Kode barang sudah digunakan.',
            'item_name.required' => 'Nama barang wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid.',
            'unit.required' => 'Satuan wajib diisi.',
            'min_stock.required' => 'Minimum stok wajib diisi.',
            'min_stock.min' => 'Minimum stok tidak boleh negatif.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $oldData = $item->toArray();

            $item->update([
                'item_code' => strtoupper($request->item_code),
                'item_name' => $request->item_name,
                'category_id' => $request->category_id,
                'unit' => $request->unit,
                'min_stock' => $request->min_stock,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Generate QR Code jika diminta dan belum ada
            if ($request->boolean('generate_qr', false) && !$item->hasQRCode()) {
                $this->generateQRCode($item);
            }

            // Log activity
            $this->logActivity('items', $item->item_id, 'update', $oldData, $item->fresh()->toArray());

            return redirect()->route('items.index')
                ->with('success', 'Barang berhasil diupdate!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate barang: ' . $e->getMessage());
        }
    }

    // Delete item
    public function destroy(Item $item)
    {
        try {
            // Cek apakah item memiliki stok
            if ($item->hasStock()) {
                return back()->with('error', 'Barang tidak dapat dihapus karena masih memiliki stok!');
            }

            // Cek apakah item memiliki transaksi
            if ($item->transactions()->exists() || $item->poDetails()->exists()) {
                return back()->with('error', 'Barang tidak dapat dihapus karena memiliki transaksi!');
            }

            $oldData = $item->toArray();
            $itemId = $item->item_id;

            // Delete QR code file if exists
            if ($item->hasQRCode()) {
                Storage::delete("public/qr-codes/{$item->qr_code}");
            }

            // Delete stock record
            $item->stock?->delete();

            // Delete item
            $item->delete();

            // Log activity
            $this->logActivity('items', $itemId, 'delete', $oldData, null);

            return back()->with('success', 'Barang berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus barang: ' . $e->getMessage());
        }
    }

    // Toggle item status
    public function toggleStatus(Item $item)
    {
        try {
            $oldData = $item->toArray();
            $item->update(['is_active' => !$item->is_active]);

            $status = $item->is_active ? 'diaktifkan' : 'dinonaktifkan';
            $action = $item->is_active ? 'activate' : 'deactivate';

            $this->logActivity('items', $item->item_id, $action, $oldData, $item->fresh()->toArray());

            return back()->with('success', "Barang berhasil {$status}!");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah status barang: ' . $e->getMessage());
        }
    }

    // Generate QR Code untuk item
    public function generateQR(Item $item)
    {
        try {
            $this->generateQRCode($item);

            $this->logActivity('items', $item->item_id, 'generate_qr', null, ['qr_code' => $item->qr_code]);

            return back()->with('success', 'QR Code berhasil digenerate!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate QR Code: ' . $e->getMessage());
        }
    }

    private function generateQRCode(Item $item): void
    {
        try {
            $qrPath = storage_path('app/public/qr-codes');
            if (!file_exists($qrPath)) {
                mkdir($qrPath, 0755, true);
            }

            $qrContent = $item->generateQRContent();
            $fileName = "item_{$item->item_id}_" . time() . ".svg";

            // Use SVG format (doesn't need ImageMagick)
            $svgContent = QrCode::format('svg')
                ->size(300)
                ->margin(2)
                ->generate($qrContent);

            file_put_contents($qrPath . '/' . $fileName, $svgContent);
            $item->update(['qr_code' => $fileName]);
        } catch (\Exception $e) {
            Log::error('QR Code generation failed: ' . $e->getMessage());
            throw new \Exception('Gagal generate QR Code: ' . $e->getMessage());
        }
    }

    // Download QR Code
    public function downloadQR(Item $item)
    {
        if (!$item->hasQRCode()) {
            return back()->with('error', 'QR Code belum ada untuk item ini!');
        }

        $filePath = storage_path("app/public/qr-codes/{$item->qr_code}");
        $fileName = "QR-{$item->item_code}.png";

        return response()->download($filePath, $fileName);
    }

    // API endpoint untuk search items
    public function search(Request $request)
    {
        $term = $request->get('q', '');
        $categoryId = $request->get('category_id');
        $limit = $request->get('limit', 10);

        $query = Item::active()
            ->with('category')
            ->search($term);

        if ($categoryId) {
            $query->byCategory($categoryId);
        }

        $items = $query->select('item_id', 'item_code', 'item_name', 'unit', 'category_id')
            ->limit($limit)
            ->get();

        return response()->json($items);
    }

    // API endpoint untuk get items by category
    public function getByCategory(Request $request, $categoryId)
    {
        $items = Item::active()
            ->byCategory($categoryId)
            ->select('item_id', 'item_code', 'item_name', 'unit')
            ->orderBy('item_name')
            ->get();

        return response()->json($items);
    }

    // // Generate QR Code untuk item
    // private function generateQRCode(Item $item): void
    // {
    //     // Ensure directory exists
    //     $qrPath = storage_path('app/public/qr-codes');
    //     if (!file_exists($qrPath)) {
    //         mkdir($qrPath, 0755, true);
    //     }

    //     // Generate QR code content
    //     $qrContent = $item->generateQRContent();

    //     // Generate filename
    //     $fileName = "item_{$item->item_id}_" . time() . ".png";

    //     // Generate QR code
    //     QrCode::format('png')
    //         ->size(300)
    //         ->margin(2)
    //         ->generate($qrContent, $qrPath . '/' . $fileName);

    //     // Update item with QR code filename
    //     $item->update(['qr_code' => $fileName]);
    // }

    // Generate item ID
    private function generateItemId(): string
    {
        $lastItem = Item::orderBy('item_id', 'desc')->first();
        $lastNumber = $lastItem ? (int) substr($lastItem->item_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        return 'ITM' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    // Generate stock ID
    private function generateStockId(): string
    {
        $lastStock = \App\Models\Stock::orderBy('stock_id', 'desc')->first();
        $lastNumber = $lastStock ? (int) substr($lastStock->stock_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        return 'STK' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
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
