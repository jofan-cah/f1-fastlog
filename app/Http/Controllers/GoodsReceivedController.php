<?php

// ================================================================
// 3. app/Http/Controllers/GoodsReceivedController.php
// ================================================================

namespace App\Http\Controllers;

use App\Models\GoodsReceived;
use App\Models\GoodsReceivedDetail;
use App\Models\ItemDetail;
use App\Models\PurchaseOrder;
use App\Models\PoDetail;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\ActivityLog;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GoodsReceivedController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
        // Nanti bisa ditambah permission middleware
    }

    // Tampilkan daftar goods received
    public function index(Request $request)
    {
        $query = GoodsReceived::with(['purchaseOrder', 'supplier', 'receivedBy'])
            ->withCount('grDetails');

        // Filter by PO
        if ($request->filled('po_id')) {
            $query->byPO($request->po_id);
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->bySupplier($request->supplier_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = $request->filled('end_date')
                ? Carbon::parse($request->end_date)
                : now();

            $query->dateRange($startDate, $endDate);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receive_number', 'like', "%{$search}%")
                    ->orWhereHas('purchaseOrder', function ($q2) use ($search) {
                        $q2->where('po_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('supplier', function ($q2) use ($search) {
                        $q2->where('supplier_name', 'like', "%{$search}%");
                    });
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'receive_date');
        $sortDirection = $request->get('direction', 'desc');

        $allowedSorts = ['receive_number', 'receive_date', 'status'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $goodsReceived = $query->paginate(15)->withQueryString();

        // Statistics
        $statistics = GoodsReceived::getStatistics();

        // Filter options
        $suppliers = Supplier::active()
            ->whereHas('goodsReceived')
            ->orderBy('supplier_name')
            ->get();

        $purchaseOrders = PurchaseOrder::whereIn('status', ['sent', 'partial'])
            ->orderBy('po_number')
            ->get();

        return view('goods-received.index', compact(
            'goodsReceived',
            'statistics',
            'suppliers',
            'purchaseOrders',
            'sortField',
            'sortDirection'
        ));
    }

    // Tampilkan form create goods received
    public function create(Request $request)
    {
        $poId = $request->get('po_id');
        $receiptType = $request->get('receipt_type', 'po_based'); // NEW

        // Existing code untuk PO-based
        $availablePOs = PurchaseOrder::whereIn('status', ['sent', 'partial'])
            ->with(['supplier', 'poDetails.item'])
            ->orderBy('po_number')
            ->get();

        $selectedPO = $poId ? PurchaseOrder::with(['supplier', 'poDetails.item'])->find($poId) : null;

        // UPDATED: Generate receive number berdasarkan type
        $receiveNumber = GoodsReceived::generateReceiveNumber($receiptType);

        // NEW: Data untuk direct receipts
        $suppliers = Supplier::active()->orderBy('supplier_name')->get();
        $items = Item::with('category')->active()->orderBy('item_name')->get();

        return view('goods-received.create', compact(
            'availablePOs',
            'selectedPO',
            'receiveNumber',
            'receiptType',    // NEW
            'suppliers',      // NEW
            'items'          // NEW
        ));
    }


    //     public function getSerialNumberTemplate(Request $request)
    // {
    //     try {
    //         $itemId = $request->get('item_id');
    //         $quantity = (int) $request->get('quantity', 1);

    //         // Validation
    //         if (!$itemId || $quantity <= 0) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Item ID dan quantity wajib diisi'
    //             ], 400);
    //         }

    //         if ($quantity > 100) { // Limit untuk safety
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Maksimal 100 serial numbers per request'
    //             ], 400);
    //         }

    //         // Validate item exists
    //         $item = Item::find($itemId);
    //         if (!$item) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Item tidak ditemukan'
    //             ], 404);
    //         }

    //         // Generate unique F1 serial numbers
    //         $serialNumbers = $this->generateF1SerialNumbers($quantity);

    //         // Validate all generated serial numbers are unique
    //         $validation = $this->validateGeneratedSerialNumbers($serialNumbers);

    //         if (!$validation['all_valid']) {
    //             // Regenerate if conflicts found
    //             Log::warning('Serial number conflicts detected, regenerating', [
    //                 'conflicts' => $validation['conflicts'],
    //                 'item_id' => $itemId
    //             ]);

    //             $serialNumbers = $this->generateF1SerialNumbers($quantity, $validation['conflicts']);
    //         }

    //         Log::info('F1 Serial numbers generated successfully', [
    //             'item_id' => $itemId,
    //             'item_code' => $item->item_code,
    //             'quantity_requested' => $quantity,
    //             'quantity_generated' => count($serialNumbers),
    //             'generated_serials' => $serialNumbers,
    //             'format' => 'F1{6_random_chars}'
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'serial_numbers' => $serialNumbers,
    //                 'format' => 'F1{6 random alphanumeric}',
    //                 'pattern' => 'F1XXXXXX',
    //                 'total_generated' => count($serialNumbers),
    //                 'item_info' => [
    //                     'item_id' => $item->item_id,
    //                     'item_code' => $item->item_code,
    //                     'item_name' => $item->item_name
    //                 ],
    //                 'validation' => $validation
    //             ],
    //             'message' => "Generated {$quantity} unique F1 serial numbers"
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Failed to generate F1 serial numbers', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //             'item_id' => $request->get('item_id'),
    //             'quantity' => $request->get('quantity')
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal generate F1 serial numbers: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Generate F1 format serial numbers with collision avoidance
//  */
    // private function generateF1SerialNumbers(int $quantity, array $excludeList = []): array
    // {
    //     $serialNumbers = [];
    //     $maxAttempts = $quantity * 20; // Increased attempts untuk safety
    //     $attempts = 0;

    //     // Get existing F1 serials from database untuk avoid collision
    //     $existingF1Serials = ItemDetail::where('serial_number', 'LIKE', 'F1%')
    //         ->pluck('serial_number')
    //         ->toArray();

    //     $allExcludes = array_merge($excludeList, $existingF1Serials);

    //     Log::debug('Starting F1 serial generation', [
    //         'quantity_needed' => $quantity,
    //         'existing_f1_count' => count($existingF1Serials),
    //         'exclude_list_count' => count($excludeList)
    //     ]);

    //     while (count($serialNumbers) < $quantity && $attempts < $maxAttempts) {
    //         $attempts++;

    //         // Generate F1 + 6 random alphanumeric characters
    //         $randomPart = $this->generateRandomAlphanumeric(6);
    //         $serialNumber = 'F1' . $randomPart;

    //         // Check uniqueness
    //         if (!in_array($serialNumber, $serialNumbers) &&
    //             !in_array($serialNumber, $allExcludes)) {

    //             $serialNumbers[] = $serialNumber;

    //             if (count($serialNumbers) % 10 == 0) {
    //                 Log::debug("Generated {$serialNumbers[count($serialNumbers)-1]} ({$attempts} attempts)");
    //             }
    //         }
    //     }

    //     if (count($serialNumbers) < $quantity) {
    //         Log::warning('Could not generate enough unique F1 serial numbers', [
    //             'requested' => $quantity,
    //             'generated' => count($serialNumbers),
    //             'total_attempts' => $attempts,
    //             'existing_f1_serials' => count($existingF1Serials)
    //         ]);
    //     }

    //     Log::info('F1 serial generation completed', [
    //         'requested' => $quantity,
    //         'generated' => count($serialNumbers),
    //         'attempts_used' => $attempts,
    //         'success_rate' => round((count($serialNumbers) / $quantity) * 100, 2) . '%'
    //     ]);

    //     return $serialNumbers;
    // }

    /**
     * Generate random alphanumeric string (excluding confusing characters)
     */
    // private function generateRandomAlphanumeric(int $length): string
    // {
    //     // Exclude confusing characters: 0, O, I, 1, l, B, 8, 6, G, 5, S
    //     $characters = 'ACDEFHJKLMNPQRTUVWXYZ23479';
    //     $result = '';

    //     for ($i = 0; $i < $length; $i++) {
    //         $result .= $characters[random_int(0, strlen($characters) - 1)];
    //     }

    //     return $result;
    // }

    /**
     * Validate generated serial numbers for database conflicts
     */
    private function validateGeneratedSerialNumbers(array $serialNumbers): array
    {
        $conflicts = [];
        $validCount = 0;

        // Batch check untuk efficiency
        $existingSerials = ItemDetail::whereIn('serial_number', $serialNumbers)
            ->pluck('serial_number')
            ->toArray();

        foreach ($serialNumbers as $sn) {
            if (in_array($sn, $existingSerials)) {
                $conflicts[] = $sn;
            } else {
                $validCount++;
            }
        }

        return [
            'all_valid' => empty($conflicts),
            'valid_count' => $validCount,
            'conflict_count' => count($conflicts),
            'conflicts' => $conflicts,
            'total_checked' => count($serialNumbers)
        ];
    }

    /**
     * Enhanced validate serial number dengan F1 format checking
     */
    // public function validateSerialNumber(Request $request)
    // {
    //     try {
    //         $serialNumber = trim($request->get('serial_number'));

    //         // Basic validation
    //         if (empty($serialNumber)) {
    //             return response()->json([
    //                 'valid' => false,
    //                 'message' => 'Serial number tidak boleh kosong',
    //                 'error_type' => 'empty'
    //             ]);
    //         }

    //         // F1 Format validation
    //         $formatValidation = $this->validateF1Format($serialNumber);
    //         if (!$formatValidation['valid']) {
    //             return response()->json([
    //                 'valid' => false,
    //                 'message' => $formatValidation['message'],
    //                 'error_type' => 'format',
    //                 'expected_format' => 'F1XXXXXX (F1 + 6 karakter alphanumeric)',
    //                 'examples' => ['F1A2C3D4', 'F1XYZ789', 'F1MNP234']
    //             ]);
    //         }

    //         // Database duplicate check
    //         $existingItem = ItemDetail::where('serial_number', $serialNumber)
    //             ->with('item')
    //             ->first();

    //         if ($existingItem) {
    //             return response()->json([
    //                 'valid' => false,
    //                 'message' => 'Serial number sudah digunakan',
    //                 'error_type' => 'duplicate',
    //                 'existing_info' => [
    //                     'item_code' => $existingItem->item->item_code ?? 'N/A',
    //                     'item_name' => $existingItem->item->item_name ?? 'N/A',
    //                     'status' => $existingItem->status ?? 'N/A',
    //                     'location' => $existingItem->location ?? 'N/A',
    //                     'created_at' => $existingItem->created_at?->format('d/m/Y H:i')
    //                 ]
    //             ]);
    //         }

    //         return response()->json([
    //             'valid' => true,
    //             'message' => 'Serial number tersedia dan format valid',
    //             'format_info' => [
    //                 'format' => 'F1 Format',
    //                 'prefix' => substr($serialNumber, 0, 2),
    //                 'random_part' => substr($serialNumber, 2),
    //                 'length' => strlen($serialNumber),
    //                 'pattern_match' => true
    //             ]
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Serial number validation error', [
    //             'serial_number' => $request->get('serial_number'),
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return response()->json([
    //             'valid' => false,
    //             'message' => 'Error validasi: ' . $e->getMessage(),
    //             'error_type' => 'system_error'
    //         ], 500);
    //     }
    // }

    /**
     * Validate F1 format specifically
     */
    private function validateF1Format(string $serialNumber): array
    {
        // Must start with F1
        if (!str_starts_with($serialNumber, 'F1')) {
            return [
                'valid' => false,
                'message' => 'Serial number harus diawali dengan "F1"'
            ];
        }

        // Must be exactly 8 characters (F1 + 6 random)
        if (strlen($serialNumber) !== 8) {
            return [
                'valid' => false,
                'message' => 'Serial number harus 8 karakter (F1 + 6 karakter random)'
            ];
        }

        // Random part must be alphanumeric and uppercase
        $randomPart = substr($serialNumber, 2);
        if (!ctype_alnum($randomPart) || $randomPart !== strtoupper($randomPart)) {
            return [
                'valid' => false,
                'message' => 'Karakter setelah F1 harus huruf besar atau angka'
            ];
        }

        // Check for confusing characters
        $confusingChars = ['0', 'O', 'I', '1', 'l', 'B', '8', '6', 'G', '5', 'S'];
        foreach ($confusingChars as $char) {
            if (str_contains($randomPart, $char)) {
                return [
                    'valid' => false,
                    'message' => "Hindari karakter yang membingungkan: {$char}. Gunakan karakter lain."
                ];
            }
        }

        return [
            'valid' => true,
            'message' => 'Format F1 valid'
        ];
    }

    /**
     * Bulk validate multiple serial numbers
     */
    public function bulkValidateSerialNumbers(Request $request)
    {
        try {
            $serialNumbers = $request->input('serial_numbers', []);

            if (empty($serialNumbers)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada serial numbers untuk divalidasi'
                ], 400);
            }

            if (count($serialNumbers) > 1000) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maksimal 100 serial numbers per batch'
                ], 400);
            }

            $results = [];
            $summary = [
                'total' => count($serialNumbers),
                'valid' => 0,
                'invalid' => 0,
                'duplicates_in_list' => 0,
                'duplicates_in_db' => 0,
                'format_errors' => 0
            ];

            // Check for duplicates within the submitted list
            $serialCounts = array_count_values($serialNumbers);

            // Batch check existing serials in database
            $existingSerials = ItemDetail::whereIn('serial_number', $serialNumbers)
                ->pluck('serial_number')
                ->toArray();

            foreach ($serialNumbers as $index => $serialNumber) {
                $serialNumber = trim($serialNumber);

                // Check for duplicates in the submitted list
                if ($serialCounts[$serialNumber] > 1) {
                    $results[$index] = [
                        'serial_number' => $serialNumber,
                        'valid' => false,
                        'message' => 'Duplikat dalam list yang disubmit',
                        'error_type' => 'duplicate_in_list'
                    ];
                    $summary['duplicates_in_list']++;
                    $summary['invalid']++;
                    continue;
                }

                // Validate F1 format
                $formatValidation = $this->validateF1Format($serialNumber);
                if (!$formatValidation['valid']) {
                    $results[$index] = [
                        'serial_number' => $serialNumber,
                        'valid' => false,
                        'message' => $formatValidation['message'],
                        'error_type' => 'format'
                    ];
                    $summary['format_errors']++;
                    $summary['invalid']++;
                    continue;
                }

                // Check database (from batch query)
                if (in_array($serialNumber, $existingSerials)) {
                    $results[$index] = [
                        'serial_number' => $serialNumber,
                        'valid' => false,
                        'message' => 'Serial number sudah ada di database',
                        'error_type' => 'duplicate_in_db'
                    ];
                    $summary['duplicates_in_db']++;
                    $summary['invalid']++;
                } else {
                    $results[$index] = [
                        'serial_number' => $serialNumber,
                        'valid' => true,
                        'message' => 'Valid',
                        'error_type' => null
                    ];
                    $summary['valid']++;
                }
            }

            Log::info('Bulk serial number validation completed', [
                'total_submitted' => count($serialNumbers),
                'summary' => $summary,
                'all_valid' => $summary['invalid'] === 0
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'results' => $results,
                    'summary' => $summary,
                    'all_valid' => $summary['invalid'] === 0
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk serial number validation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Bulk validation error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get serial number statistics and insights
     */
    public function getSerialNumberStats()
    {
        try {
            $stats = [
                'total_serial_numbers' => ItemDetail::count(),
                'f1_format_count' => ItemDetail::where('serial_number', 'LIKE', 'F1%')->count(),
                'format_distribution' => [],
                'recent_f1_additions' => ItemDetail::where('serial_number', 'LIKE', 'F1%')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->select('serial_number', 'created_at')
                    ->get()
                    ->toArray(),
                'usage_by_status' => ItemDetail::where('serial_number', 'LIKE', 'F1%')
                    ->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->get()
                    ->pluck('count', 'status')
                    ->toArray()
            ];

            // Format distribution analysis
            $formatPatterns = ItemDetail::selectRaw('
            CASE
                WHEN serial_number LIKE "F1%" THEN "F1_Format"
                WHEN LENGTH(serial_number) = 8 THEN "8_Chars_Other"
                WHEN LENGTH(serial_number) = 10 THEN "10_Chars"
                WHEN LENGTH(serial_number) = 12 THEN "12_Chars"
                ELSE "Other_Format"
            END as format_type,
            COUNT(*) as count
        ')
                ->groupBy('format_type')
                ->get()
                ->pluck('count', 'format_type')
                ->toArray();

            $stats['format_distribution'] = $formatPatterns;

            // Calculate F1 format percentage
            $stats['f1_percentage'] = $stats['total_serial_numbers'] > 0
                ? round(($stats['f1_format_count'] / $stats['total_serial_numbers']) * 100, 2)
                : 0;

            // Next available F1 serial (for debugging)
            $stats['next_available_f1'] = $this->generateF1SerialNumbers(1)[0] ?? 'Error generating';

            Log::info('Serial number statistics generated', [
                'total_serials' => $stats['total_serial_numbers'],
                'f1_count' => $stats['f1_format_count'],
                'f1_percentage' => $stats['f1_percentage']
            ]);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Serial number stats error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Stats error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test F1 serial generation (untuk debugging)
     */
    public function testF1Generation(Request $request)
    {
        try {
            $quantity = (int) $request->get('quantity', 5);

            if ($quantity > 20) {
                return response()->json([
                    'success' => false,
                    'message' => 'Test maksimal 20 serial numbers'
                ], 400);
            }

            $start = microtime(true);
            $serialNumbers = $this->generateF1SerialNumbers($quantity);
            $end = microtime(true);

            $validation = $this->validateGeneratedSerialNumbers($serialNumbers);

            return response()->json([
                'success' => true,
                'data' => [
                    'generated_serials' => $serialNumbers,
                    'quantity_requested' => $quantity,
                    'quantity_generated' => count($serialNumbers),
                    'generation_time_ms' => round(($end - $start) * 1000, 2),
                    'validation' => $validation,
                    'examples' => array_slice($serialNumbers, 0, 3)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test error: ' . $e->getMessage()
            ], 500);
        }
    }

    // Store goods received baru
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'receive_number' => 'required|string|max:50|unique:goods_receiveds,receive_number',
    //         'po_id' => 'required|string|exists:purchase_orders,po_id',
    //         'receive_date' => 'required|date',
    //         'notes' => 'nullable|string',
    //         'items' => 'required|array|min:1',
    //         'items.*.item_id' => 'required|string|exists:items,item_id',
    //         'items.*.quantity_received' => 'required|integer|min:1',
    //         'items.*.quantity_to_stock' => 'required|integer|min:0',
    //         'items.*.quantity_to_ready' => 'required|integer|min:0',
    //         'items.*.unit_price' => 'required|numeric|min:0',
    //         'items.*.batch_number' => 'nullable|string',
    //         'items.*.expiry_date' => 'nullable|date',
    //         'items.*.notes' => 'nullable|string',
    //     ], [
    //         'receive_number.required' => 'Nomor penerimaan wajib diisi.',
    //         'receive_number.unique' => 'Nomor penerimaan sudah digunakan.',
    //         'po_id.required' => 'PO wajib dipilih.',
    //         'receive_date.required' => 'Tanggal penerimaan wajib diisi.',
    //         'items.required' => 'Items wajib diisi.',
    //         'items.min' => 'Minimal 1 item harus dipilih.',
    //     ]);

    //     // Custom validation untuk split quantities
    //     $validator->after(function ($validator) use ($request) {
    //         if ($request->has('items')) {
    //             foreach ($request->items as $index => $item) {
    //                 $totalSplit = $item['quantity_to_stock'] + $item['quantity_to_ready'];
    //                 if ($totalSplit !== (int)$item['quantity_received']) {
    //                     $validator->errors()->add(
    //                         "items.{$index}.quantity_split",
    //                         'Total quantity to stock + ready harus sama dengan quantity received.'
    //                     );
    //                 }
    //             }
    //         }
    //     });

    //     // if ($validator->fails()) {
    //     //     return back()
    //     //         ->withErrors($validator)
    //     //         ->withInput();
    //     // }

    //     try {
    //         DB::beginTransaction();

    //         $po = PurchaseOrder::find($request->po_id);

    //         // Generate GR ID
    //         $grId = $this->generateGRId();

    //         // Create Goods Received
    //         $gr = GoodsReceived::create([
    //             'gr_id' => $grId,
    //             'receive_number' => $request->receive_number,
    //             'po_id' => $request->po_id,
    //             'supplier_id' => $po->supplier_id,
    //             'receive_date' => $request->receive_date,
    //             'status' => 'partial', // Will be updated based on completion
    //             'notes' => $request->notes,
    //             'received_by' => Auth::id(),
    //         ]);

    //         // Create GR Details and ItemDetails
    //         foreach ($request->items as $itemData) {
    //             $grDetail = GoodsReceivedDetail::create([
    //                 'gr_detail_id' => GoodsReceivedDetail::generateDetailId(),
    //                 'gr_id' => $gr->gr_id,
    //                 'item_id' => $itemData['item_id'],
    //                 'quantity_received' => $itemData['quantity_received'],
    //                 'quantity_to_stock' => $itemData['quantity_to_stock'],
    //                 'quantity_to_ready' => $itemData['quantity_to_ready'],
    //                 'unit_price' => $itemData['unit_price'],
    //                 'batch_number' => $itemData['batch_number'],
    //                 'expiry_date' => $itemData['expiry_date'],
    //                 'notes' => $itemData['notes'],
    //             ]);


    //             // **NEW: Generate ItemDetail records HANYA untuk quantity_to_ready**
    //             $this->generateItemDetailsForReady($grDetail, $itemData);

    //             // Update PO detail quantity received
    //             $grDetail->updatePODetail();
    //         }

    //         // Process stock updates
    //         $gr->processStockUpdates();

    //         // Update PO status
    //         $gr->updatePOStatus();

    //         // Update GR status based on PO completion
    //         if ($gr->isCompleteReceive()) {
    //             $gr->update(['status' => 'complete']);
    //         }

    //         // Log activity
    //         ActivityLog::logActivity('goods_receiveds', $gr->gr_id, 'create', null, $gr->toArray());

    //         DB::commit();

    //         return redirect()->route('goods-received.show', $gr)
    //             ->with('success', 'Penerimaan barang berhasil dicatat dan item details telah digenerate!');
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         return back()
    //             ->withInput()
    //             ->with('error', 'Gagal mencatat penerimaan: ' . $e->getMessage());
    //     }
    // }

    // Update method store() di GoodsReceivedController.php
    // Tambahkan bagian ini setelah generateItemDetailsForReceived

    public function store(Request $request)
    {
        // ================================================================
        // STEP 1: Detect receipt type (NEW - minimal addition)
        // ================================================================
        $receiptType = $request->get('receipt_type', 'po_based');
        $isPOBased = $receiptType === 'po_based' && $request->filled('po_id');

        // ================================================================
        // STEP 2: Update validation rules (UPDATED - conditional rules)
        // ================================================================
        $validator = Validator::make($request->all(), [
            'receive_number' => 'required|string|max:50|unique:goods_receiveds,receive_number',
            'po_id' => $isPOBased ? 'required|string|exists:purchase_orders,po_id' : 'nullable',
            'supplier_id' => !$isPOBased ? 'required|string|exists:suppliers,supplier_id' : 'nullable',
            'receive_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|string|exists:items,item_id',
            'items.*.quantity_received' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.notes' => 'nullable|string',
            'items.*.serial_numbers' => 'nullable|array',
            'items.*.serial_numbers.*' => 'nullable|string|max:100',
        ], [
            'receive_number.required' => 'Nomor penerimaan wajib diisi.',
            'receive_number.unique' => 'Nomor penerimaan sudah digunakan.',
            'po_id.required' => 'PO wajib dipilih untuk penerimaan berdasarkan PO.',
            'supplier_id.required' => 'Supplier wajib dipilih untuk penerimaan langsung.',
            'receive_date.required' => 'Tanggal penerimaan wajib diisi.',
            'items.required' => 'Items wajib diisi.',
            'items.min' => 'Minimal 1 item harus dipilih.',
            'items.*.quantity_received.required' => 'Quantity yang diterima wajib diisi.',
            'items.*.quantity_received.min' => 'Quantity minimal 1.',
        ]);

        // ================================================================
        // STEP 3: Existing custom validation (UNCHANGED)
        // ================================================================
        $validator->after(function ($validator) use ($request) {
            if ($request->has('items')) {
                foreach ($request->items as $index => $item) {
                    $quantityReceived = (int)$item['quantity_received'];
                    $serialNumbers = $item['serial_numbers'] ?? [];

                    // Filter serial numbers yang tidak kosong
                    $nonEmptySerials = array_filter($serialNumbers, function ($sn) {
                        return !empty(trim($sn));
                    });

                    // Jika ada serial number yang diinput, harus sesuai dengan quantity
                    if (!empty($nonEmptySerials) && count($nonEmptySerials) !== $quantityReceived) {
                        $validator->errors()->add(
                            "items.{$index}.serial_numbers",
                            "Jumlah serial number harus sesuai dengan quantity yang diterima ({$quantityReceived} unit)."
                        );
                    }

                    // Check untuk serial number yang duplikat
                    if (count($nonEmptySerials) !== count(array_unique($nonEmptySerials))) {
                        $validator->errors()->add(
                            "items.{$index}.serial_numbers",
                            "Serial number tidak boleh duplikat."
                        );
                    }

                    // Check serial number yang sudah ada di database
                    foreach ($nonEmptySerials as $serialIndex => $serialNumber) {
                        $exists = ItemDetail::where('serial_number', trim($serialNumber))->exists();
                        if ($exists) {
                            $validator->errors()->add(
                                "items.{$index}.serial_numbers.{$serialIndex}",
                                "Serial number '{$serialNumber}' sudah digunakan."
                            );
                        }
                    }
                }
            }
        });

        try {
            DB::beginTransaction();

            // ================================================================
            // STEP 4: Handle supplier_id (UPDATED - conditional logic)
            // ================================================================
            if ($isPOBased) {
                $po = PurchaseOrder::find($request->po_id);
                $supplierId = $po->supplier_id;
            } else {
                $po = null;
                $supplierId = $request->supplier_id; // NEW: direct supplier selection
            }

            // Generate GR ID (UNCHANGED)
            $grId = $this->generateGRId();

            // ================================================================
            // STEP 5: Create Goods Received (UPDATED - po_id nullable, add receipt_type)
            // ================================================================
            $gr = GoodsReceived::create([
                'gr_id' => $grId,
                'receive_number' => $request->receive_number,
                'po_id' => $isPOBased ? $request->po_id : null,          // UPDATED: nullable
                'supplier_id' => $supplierId,
                'receive_date' => $request->receive_date,
                'status' => 'partial', // Will be updated based on completion
                'notes' => $request->notes,
                'received_by' => Auth::id(),
                'receipt_type' => $receiptType,                          // NEW: track receipt type
            ]);

            $totalItemsGenerated = 0;
            $totalQRGenerated = 0;
            $qrGenerationResults = [];

            // ================================================================
            // STEP 6: Create GR Details (UPDATED - po_detail_id nullable, add total_price)
            // ================================================================
            foreach ($request->items as $itemData) {
                $grDetail = GoodsReceivedDetail::create([
                    'gr_detail_id' => GoodsReceivedDetail::generateDetailId(),
                    'gr_id' => $gr->gr_id,
                    'item_id' => $itemData['item_id'],
                    'po_detail_id' => $isPOBased ? ($itemData['po_detail_id'] ?? null) : null, // UPDATED: nullable
                    'quantity_received' => $itemData['quantity_received'],
                    'quantity_to_stock' => $itemData['quantity_received'], // Semua ke stock
                    'quantity_to_ready' => 0, // Tidak ada yang langsung ready
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemData['quantity_received'] * $itemData['unit_price'], // NEW: calculated field
                    'batch_number' => $itemData['batch_number'],
                    'expiry_date' => $itemData['expiry_date'],
                    'notes' => $itemData['notes'],
                ]);

                // ================================================================
                // STEP 7: Generate ItemDetail records (UNCHANGED)
                // ================================================================
                $itemsGenerated = $this->generateItemDetailsForReceived(
                    $grDetail,
                    $itemData,
                    array_map('strtoupper', $itemData['serial_numbers'] ?? [])
                );

                $totalItemsGenerated += $itemsGenerated;

                // ================================================================
                // STEP 8: Auto-generate QR codes (UNCHANGED - existing logic)
                // ================================================================
                $qrResult = $grDetail->autoGenerateQRCodes();
                $qrGenerationResults[] = [
                    'gr_detail_id' => $grDetail->gr_detail_id,
                    'item_code' => Item::find($itemData['item_id'])->item_code ?? 'N/A',
                    'qr_result' => $qrResult
                ];

                if ($qrResult['success']) {
                    $totalQRGenerated += $qrResult['generated_count'];
                }

                // ================================================================
                // STEP 9: Update PO detail (UPDATED - only for PO-based)
                // ================================================================
                if ($isPOBased && method_exists($grDetail, 'updatePODetail')) {
                    $grDetail->updatePODetail();
                }
            }

            // ================================================================
            // STEP 10: Process stock updates (UNCHANGED)
            // ================================================================
            $gr->processStockUpdates();

            // ================================================================
            // STEP 11: Update status (UPDATED - different logic for direct receipts)
            // ================================================================
            if ($isPOBased) {
                // PO-based: update PO status and check completion
                $gr->updatePOStatus();

                // Update GR status based on PO completion
                if ($gr->isCompleteReceive()) {
                    $gr->update(['status' => 'complete']);
                }
            } else {
                // Direct receipts: always complete immediately
                $gr->update(['status' => 'complete']);
            }

            // ================================================================
            // STEP 12: Log activity (UPDATED - add receipt_type info)
            // ================================================================
            ActivityLog::logActivity('goods_receiveds', $gr->gr_id, 'create', null, array_merge($gr->toArray(), [
                'total_item_details_generated' => $totalItemsGenerated,
                'total_qr_codes_generated' => $totalQRGenerated,
                'qr_generation_results' => $qrGenerationResults,
                'receipt_type' => $receiptType  // NEW: track receipt type
            ]));

            DB::commit();

            // ================================================================
            // STEP 13: Success response (UPDATED - receipt type aware message)
            // ================================================================
            $receiptTypeText = $isPOBased ? 'berdasarkan PO' : 'langsung';
            $successMessage = "Penerimaan barang {$receiptTypeText} berhasil dicatat! ";
            $successMessage .= "Total {$totalItemsGenerated} item details telah dibuat ";

            if ($totalQRGenerated > 0) {
                $successMessage .= "dengan {$totalQRGenerated} QR codes yang berhasil digenerate.";
            } else {
                $successMessage .= "namun QR codes gagal digenerate. Anda bisa generate ulang nanti.";
            }

            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'data' => [
                    'gr_id' => $gr->gr_id,
                    'receive_number' => $gr->receive_number,
                    'receipt_type' => $gr->receipt_type,  // NEW: include receipt type
                    'total_item_details' => $totalItemsGenerated,
                    'total_qr_generated' => $totalQRGenerated,
                    'qr_generation_summary' => [
                        'total_expected' => $totalItemsGenerated,
                        'total_generated' => $totalQRGenerated,
                        'success_rate' => $totalItemsGenerated > 0 ? round(($totalQRGenerated / $totalItemsGenerated) * 100, 2) : 0
                    ],
                    'redirect_url' => route('goods-received.show', $gr)
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat penerimaan: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ================================================================
    // TAMBAHAN: Method untuk regenerate QR codes manual
    // ================================================================

    /**
     * API endpoint untuk regenerate QR codes untuk GR tertentu
     */
    public function regenerateQRCodes(GoodsReceived $goodsReceived)
    {
        try {
            $goodsReceived->load(['grDetails.itemDetails']);

            $totalExpected = 0;
            $totalGenerated = 0;
            $results = [];

            foreach ($goodsReceived->grDetails as $grDetail) {
                $stats = $grDetail->getQRGenerationStats();
                $totalExpected += $stats['total_item_details'];

                if ($stats['needs_qr_generation']) {
                    $result = $grDetail->regenerateQRCodes();
                    $results[] = [
                        'gr_detail_id' => $grDetail->gr_detail_id,
                        'item_name' => $grDetail->item->item_name ?? 'Unknown',
                        'result' => $result
                    ];

                    if ($result['success']) {
                        $totalGenerated += $result['regenerated_count'];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "QR codes regeneration completed. Generated {$totalGenerated} QR codes.",
                'data' => [
                    'gr_id' => $goodsReceived->gr_id,
                    'receive_number' => $goodsReceived->receive_number,
                    'total_expected' => $totalExpected,
                    'total_generated' => $totalGenerated,
                    'details' => $results
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate QR codes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get QR generation status untuk GR tertentu
     */
    public function getQRStatus(GoodsReceived $goodsReceived)
    {
        try {
            $goodsReceived->load(['grDetails.itemDetails']);

            $totalItems = 0;
            $totalWithQR = 0;
            $detailStats = [];

            foreach ($goodsReceived->grDetails as $grDetail) {
                $stats = $grDetail->getQRGenerationStats();
                $detailStats[] = [
                    'gr_detail_id' => $grDetail->gr_detail_id,
                    'item_code' => $grDetail->item->item_code ?? 'N/A',
                    'item_name' => $grDetail->item->item_name ?? 'Unknown',
                    'stats' => $stats
                ];

                $totalItems += $stats['total_item_details'];
                $totalWithQR += $stats['with_qr_code'];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'gr_id' => $goodsReceived->gr_id,
                    'receive_number' => $goodsReceived->receive_number,
                    'summary' => [
                        'total_item_details' => $totalItems,
                        'total_with_qr' => $totalWithQR,
                        'total_without_qr' => $totalItems - $totalWithQR,
                        'completion_rate' => $totalItems > 0 ? round(($totalWithQR / $totalItems) * 100, 2) : 0,
                        'needs_generation' => ($totalItems - $totalWithQR) > 0
                    ],
                    'details' => $detailStats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get QR status: ' . $e->getMessage()
            ], 500);
        }
    }
    private function generateItemDetailId($itemId): string
    {
        $item = Item::with('category')->find($itemId);

        // Ambil code_category dan item_code
        $codeCategory = $item->category->code_category ?? 'XXX';
        $itemCode = $item->item_code ?? 'XXX';

        // Gabung prefix
        $prefix = $codeCategory . $itemCode;

        // Cari nomor terakhir
        $lastDetail = ItemDetail::where('item_detail_id', 'like', $prefix . '%')
            ->orderBy('item_detail_id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastDetail) {
            $lastNumber = (int) substr($lastDetail->item_detail_id, -5);
            $nextNumber = $lastNumber + 1;
        }

        // Format: prefix + 5 digit angka
        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    private function generateItemDetailsForReceived(GoodsReceivedDetail $grDetail, array $itemData, array $serialNumbers = [])
    {
        $quantityReceived = (int)$itemData['quantity_received'];
        $item = Item::with('category')->find($itemData['item_id']);
        $itemCode = $item->item_code;

        // Generate prefix once
        $codeCategory = $item->category->code_category ?? 'XXX';
        $itemCodePrefix = $item->item_code ?? 'XXX';
        $prefix = $codeCategory . $itemCodePrefix;

        Log::info('ItemDetail ID Generation - UUID Approach', [
            'item_id' => $itemData['item_id'],
            'prefix' => $prefix,
            'quantity_to_generate' => $quantityReceived
        ]);

        return DB::transaction(function () use ($grDetail, $itemData, $serialNumbers, $quantityReceived, $item, $itemCode, $prefix) {
            $itemsGenerated = 0;

            for ($i = 0; $i < $quantityReceived; $i++) {
                $serialNumber = $this->getSerialNumber($serialNumbers, $i, $itemCode, $i + 1);

                // ZERO COLLISION APPROACH: UUID-based ID
                $itemDetailId = $this->generateUniqueItemDetailId($prefix);

                Log::info('Generating ItemDetail with UUID approach', [
                    'iteration' => $i + 1,
                    'generated_id' => $itemDetailId
                ]);

                try {
                    ItemDetail::create([
                        'item_detail_id' => $itemDetailId,
                        'gr_detail_id' => $grDetail->gr_detail_id,
                        'item_id' => $itemData['item_id'],
                        'serial_number' => $serialNumber,
                        'custom_attributes' => null,
                        'qr_code' => null,
                        'status' => 'stock',
                        'location' => 'Warehouse - Stock',
                        'notes' => "Received from GR: {$grDetail->gr_detail_id}",
                    ]);

                    $itemsGenerated++;

                    Log::info('ItemDetail created successfully', [
                        'item_detail_id' => $itemDetailId,
                        'iteration' => $i + 1,
                        'total_generated' => $itemsGenerated
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create ItemDetail', [
                        'item_detail_id' => $itemDetailId,
                        'error' => $e->getMessage(),
                        'iteration' => $i
                    ]);
                    throw $e;
                }
            }

            return $itemsGenerated;
        });
    }

    /**
     * Generate unique ItemDetail ID - ZERO collision guarantee
     */
    private function generateUniqueItemDetailId($prefix): string
    {
        // UUID approach - matematically guaranteed unique
        $uuid = str_replace('-', '', \Illuminate\Support\Str::uuid());

        // Take first 10 characters dari UUID untuk readability
        $uniquePart = strtoupper(substr($uuid, 0, 10));

        return $prefix . $uniquePart;

        // Result format: MDMMDM550E8400E2
        // - Prefix tetap (MDMMDM)
        // - 10 char UUID (550E8400E2) = guaranteed unique globally
    }

 

    /**
     * Get serial number dari input manual atau generate otomatis
     */
    private function getSerialNumber(array $serialNumbers, int $index, string $itemCode, int $sequence)
    {
        // Jika ada serial number manual yang diinput
        if (isset($serialNumbers[$index]) && !empty(trim($serialNumbers[$index]))) {
            return trim($serialNumbers[$index]);
        }

        // Generate otomatis jika tidak ada input manual
        return $this->generateSerialNumber($itemCode, $sequence);
    }

    /**
     * Generate serial number otomatis
     */
    // private function generateSerialNumber(string $itemCode, int $sequence)
    // {
    //     $year = date('Y');
    //     $month = date('m');
    //     $day = date('d');

    //     // Format: ITEMCODE-YYYYMMDD-XXX
    //     $baseSerial = "{$itemCode}-{$year}{$month}{$day}";

    //     // Check if serial already exists dan increment
    //     $counter = $sequence;
    //     do {
    //         $serialNumber = $baseSerial . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
    //         $exists = ItemDetail::where('serial_number', $serialNumber)->exists();
    //         if ($exists) {
    //             $counter++;
    //         }
    //     } while ($exists);

    //     return $serialNumber;
    // }

    /**
     * Generate unique GR ID
     */
    // private function generateGRId()
    // {
    //     $lastGR = GoodsReceived::orderBy('gr_id', 'desc')->first();
    //     $lastNumber = $lastGR ? (int) substr($lastGR->gr_id, 2) : 0;
    //     $newNumber = $lastNumber + 1;
    //     return 'GR' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
    // }

    /**
     * API endpoint untuk mendapatkan template serial numbers
     */
    // public function getSerialNumberTemplate(Request $request)
    // {
    //     try {
    //         $itemId = $request->get('item_id');
    //         $quantity = (int)$request->get('quantity', 1);

    //         if (!$itemId || $quantity < 1) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Item ID dan quantity wajib diisi'
    //             ], 400);
    //         }

    //         $item = Item::find($itemId);
    //         if (!$item) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Item tidak ditemukan'
    //             ], 404);
    //         }

    //         // Generate template serial numbers
    //         $templateSerials = [];
    //         for ($i = 1; $i <= $quantity; $i++) {
    //             $templateSerials[] = $this->generateSerialNumber($item->item_code, $i);
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'item_id' => $itemId,
    //                 'item_code' => $item->item_code,
    //                 'item_name' => $item->item_name,
    //                 'quantity' => $quantity,
    //                 'serial_numbers' => $templateSerials
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error generating serial numbers: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Validate serial number uniqueness via AJAX
     */
    // public function validateSerialNumber(Request $request)
    // {
    //     $serialNumber = trim($request->get('serial_number'));

    //     if (empty($serialNumber)) {
    //         return response()->json([
    //             'valid' => true,
    //             'message' => 'Serial number kosong'
    //         ]);
    //     }

    //     $exists = ItemDetail::where('serial_number', $serialNumber)->exists();

    //     return response()->json([
    //         'valid' => !$exists,
    //         'message' => $exists ? 'Serial number sudah digunakan' : 'Serial number tersedia'
    //     ]);
    // }

    /**
     * Preview item details yang akan dibuat sebelum save
     */
    public function previewItemDetails(Request $request)
    {
        try {
            $items = $request->get('items', []);
            $preview = [];

            foreach ($items as $itemData) {
                $item = Item::find($itemData['item_id']);
                if (!$item) continue;

                $quantity = (int)($itemData['quantity_received'] ?? 0);
                if ($quantity < 1) continue;

                $serialNumbers = $itemData['serial_numbers'] ?? [];
                $itemPreview = [
                    'item_id' => $item->item_id,
                    'item_code' => $item->item_code,
                    'item_name' => $item->item_name,
                    'quantity_received' => $quantity,
                    'item_details' => []
                ];

                for ($i = 1; $i <= $quantity; $i++) {
                    $serialNumber = $this->getSerialNumber($serialNumbers, $i - 1, $item->item_code, $i);

                    $itemPreview['item_details'][] = [
                        'sequence' => $i,
                        'serial_number' => $serialNumber,
                        'status' => 'available',
                        'location' => 'Warehouse - Stock',
                        'is_manual_serial' => isset($serialNumbers[$i - 1]) && !empty(trim($serialNumbers[$i - 1]))
                    ];
                }

                $preview[] = $itemPreview;
            }

            return response()->json([
                'success' => true,
                'data' => $preview,
                'summary' => [
                    'total_items' => count($preview),
                    'total_item_details' => array_sum(array_column($preview, 'quantity_received'))
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating preview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update stock untuk quantity_to_stock (barang masuk gudang)
     *
     * @param array $itemData
     * @return void
     */
    private function updateStockFromReceiving(array $itemData)
    {
        if ($itemData['quantity_to_stock'] > 0) {
            // Cari atau buat stock record
            $stock = Stock::firstOrCreate(
                ['item_id' => $itemData['item_id']],
                [
                    'stock_id' => $this->generateStockId(),
                    'quantity_available' => 0,
                    'quantity_used' => 0,
                    'total_quantity' => 0,
                    'last_updated' => now(),
                ]
            );

            // Add stock untuk quantity_to_stock
            $stock->addStock(
                $itemData['quantity_to_stock'],
                'goods_received',
                Auth::id()
            );
        }
    }

    /**
     * Generate ItemDetail records HANYA untuk quantity_to_ready
     *
     * @param GoodsReceivedDetail $grDetail
     * @param array $itemData
     * @return void
     */
    private function generateItemDetailsForReady(GoodsReceivedDetail $grDetail, array $itemData)
    {
        // Generate ItemDetails HANYA untuk quantity_to_ready
        if ($itemData['quantity_to_ready'] > 0) {
            $item = Item::find($itemData['item_id']);
            $itemCode = $item->item_code;

            for ($i = 1; $i <= $itemData['quantity_to_ready']; $i++) {

                // Generate serial number yang unique
                $serialNumber = $this->generateSerialNumber($itemCode, $i);

                // quantity_to_ready langsung jadi used
                $status = 'available';
                $location = 'F1 Logistik';
                $notes = "Unit {$item->item_name} langsung deploy untuk project urgent";

                // Create ItemDetail record
                ItemDetail::create([
                    'item_detail_id' => ItemDetail::generateItemDetailId(),
                    'gr_detail_id' => $grDetail->gr_detail_id,
                    'item_id' => $itemData['item_id'],
                    'serial_number' => $serialNumber,
                    'custom_attributes' => null, // Kosongkan dulu, nanti diedit manual
                    'qr_code' => null, // Will be generated later if needed
                    'status' => $status,
                    'location' => $location,
                    'notes' => $notes,
                ]);
            }
        }
    }

    /**
     * Generate stock ID
     *
     * @return string
     */
    private function generateStockId(): string
    {
        $lastStock = Stock::orderBy('stock_id', 'desc')->first();
        $lastNumber = $lastStock ? (int) substr($lastStock->stock_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        return 'STK' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Generate serial number for item
     *
     * @param string $itemCode
     * @param int $sequence
     * @return string
     */
    private function generateSerialNumber(string $itemCode, int $sequence): string
    {
        $year = date('Y');

        // Get last serial number untuk item ini di tahun yang sama
        $lastSerial = ItemDetail::whereHas('item', function ($q) use ($itemCode) {
            $q->where('item_code', $itemCode);
        })
            ->where('serial_number', 'like', "{$itemCode}-{$year}-%")
            ->orderBy('serial_number', 'desc')
            ->first();

        $lastNumber = 0;
        if ($lastSerial) {
            $lastNumber = (int) substr($lastSerial->serial_number, -3);
        }

        $newNumber = $lastNumber + $sequence;
        return "{$itemCode}-{$year}-" . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
    // Tampilkan detail goods received
    public function show(GoodsReceived $goodsReceived)
    {
        // Load relationships dengan conditional loading
        $relationships = [
            'supplier',
            'receivedBy.userLevel',
            'grDetails.item.category'
        ];

        // Only load PO relationship jika PO-based
        if ($goodsReceived->isPOBased() && $goodsReceived->po_id) {
            $relationships[] = 'purchaseOrder.poDetails.item';
        }

        $goodsReceived->load($relationships);

        // Summary info
        $summaryInfo = $goodsReceived->getSummaryInfo();

        // Status info
        $statusInfo = $goodsReceived->getStatusInfo();

        return view('goods-received.show', compact(
            'goodsReceived',
            'summaryInfo',
            'statusInfo'
        ));
    }


    // Tampilkan form edit goods received
    public function edit(GoodsReceived $goodsReceived)
    {
        // ================================================================
        // STEP 1: Existing validation (UNCHANGED)
        // ================================================================
        // Only allow edit if status is partial
        if ($goodsReceived->status === 'complete') {
            return back()->with('error', 'Penerimaan yang sudah complete tidak dapat diedit.');
        }

        // ================================================================
        // STEP 2: Load existing relationships (UPDATED - conditional loading)
        // ================================================================
        $goodsReceived->load([
            'grDetails.item',
            'supplier',                    // Always load supplier
            'receivedBy.userLevel'         // Always load receiver info
        ]);

        // Load PO relationship only if PO-based
        if ($goodsReceived->isPOBased()) {
            $goodsReceived->load(['purchaseOrder.poDetails.item']);
        }

        // ================================================================
        // STEP 3: Get data for dropdowns (NEW - for direct receipts)
        // ================================================================
        // Get all suppliers for dropdown (needed for direct receipts)
        $suppliers = Supplier::active()->orderBy('supplier_name')->get();

        // Get all items for dropdown (needed for direct receipts to add new items)
        $items = Item::with('category')->active()->orderBy('item_name')->get();

        // ================================================================
        // STEP 4: Prepare view data (UPDATED - add new data)
        // ================================================================
        return view('goods-received.edit', compact(
            'goodsReceived',
            'suppliers',        // NEW: for direct receipts
            'items'            // NEW: for adding items in direct receipts
        ));
    }

    // Update goods received
    public function update(Request $request, GoodsReceived $goodsReceived)
    {
        // ================================================================
        // STEP 1: Existing validation (UNCHANGED)
        // ================================================================
        if ($goodsReceived->status === 'complete') {
            return back()->with('error', 'Penerimaan yang sudah complete tidak dapat diedit.');
        }

        // ================================================================
        // STEP 2: Detect receipt type (NEW - determine update rules)
        // ================================================================
        $isPOBased = $goodsReceived->isPOBased();

        // ================================================================
        // STEP 3: Dynamic validation rules (UPDATED - conditional based on receipt type)
        // ================================================================
        $rules = [
            'receive_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.quantity_received' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.notes' => 'nullable|string',
        ];

        // Add conditional validation for direct receipts
        if (!$isPOBased) {
            $rules['supplier_id'] = 'required|string|exists:suppliers,supplier_id';
            $rules['delivery_note_number'] = 'nullable|string|max:100';
            $rules['invoice_number'] = 'nullable|string|max:100';
            $rules['external_reference'] = 'nullable|string|max:200';
        }

        $validator = Validator::make($request->all(), $rules, [
            'receive_date.required' => 'Tanggal penerimaan wajib diisi.',
            'supplier_id.required' => 'Supplier wajib dipilih untuk penerimaan langsung.',
            'items.required' => 'Items wajib diisi.',
            'items.min' => 'Minimal 1 item harus dipilih.',
            'items.*.quantity_received.required' => 'Quantity yang diterima wajib diisi.',
            'items.*.quantity_received.min' => 'Quantity minimal 1.',
        ]);

        // ================================================================
        // STEP 4: Existing custom validation (UNCHANGED - quantity split logic)
        // ================================================================
        $validator->after(function ($validator) use ($request) {
            if ($request->has('items')) {
                foreach ($request->items as $index => $item) {
                    $totalSplit = $item['quantity_to_stock'] + $item['quantity_to_ready'];
                    if ($totalSplit !== (int)$item['quantity_received']) {
                        $validator->errors()->add(
                            "items.{$index}.quantity_split",
                            'Total quantity to stock + ready harus sama dengan quantity received.'
                        );
                    }
                }
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $oldData = $goodsReceived->toArray();

            // ================================================================
            // STEP 5: Update GR header (UPDATED - conditional fields)
            // ================================================================
            $updateData = [
                'receive_date' => $request->receive_date,
                'notes' => $request->notes,
            ];

            // Add fields specific to direct receipts
            if (!$isPOBased) {
                $updateData['supplier_id'] = $request->supplier_id;
                $updateData['delivery_note_number'] = $request->delivery_note_number;
                $updateData['invoice_number'] = $request->invoice_number;
                $updateData['external_reference'] = $request->external_reference;
            }

            $goodsReceived->update($updateData);

            // ================================================================
            // STEP 6: Update GR details (UPDATED - conditional PO detail updates)
            // ================================================================
            foreach ($request->items as $grDetailId => $itemData) {
                $grDetail = GoodsReceivedDetail::find($grDetailId);

                if ($grDetail) {
                    // Store old quantity for PO update calculation
                    $oldQuantity = $grDetail->quantity_received;

                    // Update GR detail
                    $grDetail->update([
                        'quantity_received' => $itemData['quantity_received'],
                        'quantity_to_stock' => $itemData['quantity_to_stock'],
                        'quantity_to_ready' => $itemData['quantity_to_ready'],
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $itemData['quantity_received'] * $itemData['unit_price'], // NEW: calculated
                        'batch_number' => $itemData['batch_number'],
                        'expiry_date' => $itemData['expiry_date'],
                        'notes' => $itemData['notes'],
                    ]);

                    // ================================================================
                    // STEP 7: Update PO detail (UPDATED - only for PO-based)
                    // ================================================================
                    if ($isPOBased && $grDetail->po_detail_id) {
                        $poDetail = PoDetail::find($grDetail->po_detail_id);
                        if ($poDetail) {
                            // Calculate quantity difference and update PO detail
                            $quantityDiff = $itemData['quantity_received'] - $oldQuantity;
                            $poDetail->increment('quantity_received', $quantityDiff);
                        }
                    }
                }
            }

            // ================================================================
            // STEP 8: Re-process stock updates (UNCHANGED - existing logic)
            // ================================================================
            // For now, we assume stock was already processed and won't double-process
            // This might need adjustment based on business logic

            // ================================================================
            // STEP 9: Update status (UPDATED - conditional logic)
            // ================================================================
            if ($isPOBased) {
                // PO-based: update PO status and check completion
                $goodsReceived->updatePOStatus();

                // Update GR status based on PO completion
                if ($goodsReceived->isCompleteReceive()) {
                    $goodsReceived->update(['status' => 'complete']);
                }
            } else {
                // Direct receipts: keep as complete (they're always complete when created)
                // No additional status logic needed
            }

            // ================================================================
            // STEP 10: Log activity (UPDATED - include receipt type info)
            // ================================================================
            ActivityLog::logActivity('goods_receiveds', $goodsReceived->gr_id, 'update', $oldData, array_merge($goodsReceived->fresh()->toArray(), [
                'receipt_type' => $goodsReceived->receipt_type,
                'is_po_based' => $isPOBased
            ]));

            DB::commit();

            // ================================================================
            // STEP 11: Success response (UPDATED - receipt type aware message)
            // ================================================================
            $receiptTypeText = $isPOBased ? 'berdasarkan PO' : 'langsung';
            return redirect()->route('goods-received.show', $goodsReceived)
                ->with('success', "Penerimaan barang {$receiptTypeText} berhasil diupdate!");
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate penerimaan: ' . $e->getMessage());
        }
    }


    // API endpoint untuk get PO details yang belum fully received
    public function getPODetails(Request $request, $poId)
    {
        $po = PurchaseOrder::with(['poDetails.item.category', 'supplier'])->find($poId);

        if (!$po) {
            return response()->json(['error' => 'PO not found'], 404);
        }

        $details = $po->poDetails->map(function ($poDetail) {
            $remainingQty = $poDetail->quantity_ordered - $poDetail->quantity_received;

            return [
                'po_detail_id' => $poDetail->po_detail_id,
                'item_id' => $poDetail->item_id,
                'item_code' => $poDetail->item->item_code,
                'item_name' => $poDetail->item->item_name,
                'category_name' => $poDetail->item->category->category_name,
                'unit' => $poDetail->item->unit,
                'quantity_ordered' => $poDetail->quantity_ordered,
                'quantity_received' => $poDetail->quantity_received,
                'remaining_quantity' => $remainingQty,
                'unit_price' => $poDetail->unit_price,
                'can_receive' => $remainingQty > 0,
            ];
        })->filter(function ($detail) {
            return $detail['can_receive']; // Only return items that can still be received
        })->values();

        return response()->json([
            'po' => [
                'po_id' => $po->po_id,
                'po_number' => $po->po_number,
                'supplier_name' => $po->supplier->supplier_name,
                'po_date' => $po->po_date->format('Y-m-d'),
            ],
            'details' => $details
        ]);
    }

    // Generate GR ID
    private function generateGRId(): string
    {
        $lastGR = GoodsReceived::orderBy('gr_id', 'desc')->first();
        $lastNumber = $lastGR ? (int) substr($lastGR->gr_id, 2) : 0;
        $newNumber = $lastNumber + 1;
        return 'GR' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }



    /**
     * Generate serial numbers dengan format flexible (F1 atau custom)
     */
    // public function getSerialNumberTemplate(Request $request)
    // {
    //     try {
    //         $itemId = $request->get('item_id');
    //         $quantity = (int) $request->get('quantity', 1);
    //         $format = $request->get('format', 'f1'); // f1, custom, or auto
    //         $customPrefix = $request->get('prefix', ''); // Custom prefix jika ada

    //         // Validation
    //         if (!$itemId || $quantity <= 0) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Item ID dan quantity wajib diisi'
    //             ], 400);
    //         }

    //         if ($quantity > 1000) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Maksimal 100 serial numbers per request'
    //             ], 400);
    //         }

    //         // Validate item exists
    //         $item = Item::find($itemId);
    //         if (!$item) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Item tidak ditemukan'
    //             ], 404);
    //         }

    //         // Generate serial numbers based on format
    //         $serialNumbers = [];
    //         $formatInfo = [];

    //         switch ($format) {
    //             case 'f1':
    //                 $serialNumbers = $this->generateF1SerialNumbers($quantity);
    //                 $formatInfo = [
    //                     'type' => 'F1 Generated',
    //                     'pattern' => 'F1XXXXXX',
    //                     'description' => 'F1 + 6 random alphanumeric'
    //                 ];
    //                 break;

    //             case 'custom':
    //                 $serialNumbers = $this->generateCustomSerialNumbers($quantity, $customPrefix, $item);
    //                 $formatInfo = [
    //                     'type' => 'Custom Generated',
    //                     'pattern' => $customPrefix ? "{$customPrefix}XXXX" : 'Auto pattern',
    //                     'description' => 'Custom format based on item'
    //                 ];
    //                 break;

    //             case 'auto':
    //             default:
    //                 // Auto-detect best format based on item type
    //                 $autoFormat = $this->detectBestSerialFormat($item);
    //                 $serialNumbers = $this->generateAutoSerialNumbers($quantity, $autoFormat, $item);
    //                 $formatInfo = [
    //                     'type' => 'Auto Generated',
    //                     'pattern' => $autoFormat['pattern'],
    //                     'description' => $autoFormat['description']
    //                 ];
    //                 break;
    //         }

    //         // Validate all generated serial numbers are unique
    //         $validation = $this->validateGeneratedSerialNumbers($serialNumbers);

    //         if (!$validation['all_valid']) {
    //             Log::warning('Serial number conflicts detected, regenerating', [
    //                 'conflicts' => $validation['conflicts'],
    //                 'item_id' => $itemId
    //             ]);

    //             // Try regenerate with conflicts excluded
    //             if ($format === 'f1') {
    //                 $serialNumbers = $this->generateF1SerialNumbers($quantity, $validation['conflicts']);
    //             } else {
    //                 // For custom/auto, try different pattern
    //                 $serialNumbers = $this->generateCustomSerialNumbers($quantity, $customPrefix . '_', $item);
    //             }
    //         }

    //         Log::info('Serial numbers generated successfully', [
    //             'item_id' => $itemId,
    //             'item_code' => $item->item_code,
    //             'quantity_requested' => $quantity,
    //             'quantity_generated' => count($serialNumbers),
    //             'format' => $format,
    //             'generated_serials' => $serialNumbers
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'serial_numbers' => $serialNumbers,
    //                 'format_info' => $formatInfo,
    //                 'total_generated' => count($serialNumbers),
    //                 'item_info' => [
    //                     'item_id' => $item->item_id,
    //                     'item_code' => $item->item_code,
    //                     'item_name' => $item->item_name
    //                 ],
    //                 'validation' => $validation
    //             ],
    //             'message' => "Generated {$quantity} unique serial numbers"
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Failed to generate serial numbers', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //             'item_id' => $request->get('item_id'),
    //             'quantity' => $request->get('quantity'),
    //             'format' => $request->get('format')
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal generate serial numbers: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Generate custom serial numbers based on item
     */
    private function generateCustomSerialNumbers(int $quantity, string $customPrefix = '', Item $item): array
    {
        $serialNumbers = [];
        $maxAttempts = $quantity * 20;
        $attempts = 0;

        // Determine prefix
        if (empty($customPrefix)) {
            $prefix = $this->generatePrefixFromItem($item);
        } else {
            $prefix = strtoupper($customPrefix);
        }

        // Get existing serials with this prefix
        $existingSerials = ItemDetail::where('serial_number', 'LIKE', $prefix . '%')
            ->pluck('serial_number')
            ->toArray();

        Log::debug('Starting custom serial generation', [
            'prefix' => $prefix,
            'quantity_needed' => $quantity,
            'existing_with_prefix' => count($existingSerials)
        ]);

        while (count($serialNumbers) < $quantity && $attempts < $maxAttempts) {
            $attempts++;

            // Generate suffix (6-8 random chars)
            $suffixLength = strlen($prefix) <= 4 ? 8 : 6;
            $suffix = $this->generateRandomAlphanumeric($suffixLength);
            $serialNumber = $prefix . $suffix;

            // Check uniqueness
            if (
                !in_array($serialNumber, $serialNumbers) &&
                !in_array($serialNumber, $existingSerials)
            ) {
                $serialNumbers[] = $serialNumber;
            }
        }

        return $serialNumbers;
    }

    /**
     * Generate prefix from item information
     */
    private function generatePrefixFromItem(Item $item): string
    {
        // Try to create meaningful prefix from item
        $itemCode = strtoupper($item->item_code);
        $categoryCode = strtoupper($item->category->category_code ?? 'ITM');

        // Different strategies based on item type
        if (str_contains(strtolower($item->item_name), 'modem')) {
            return 'MDM';
        } elseif (str_contains(strtolower($item->item_name), 'router')) {
            return 'RTR';
        } elseif (str_contains(strtolower($item->item_name), 'switch')) {
            return 'SWT';
        } elseif (str_contains(strtolower($item->item_name), 'cable')) {
            return 'CBL';
        } elseif (strlen($itemCode) >= 3) {
            return substr($itemCode, 0, 3);
        } else {
            return substr($categoryCode, 0, 3);
        }
    }

    /**
     * Auto-detect best serial format for item
     */
    private function detectBestSerialFormat(Item $item): array
    {
        $itemName = strtolower($item->item_name);
        $categoryName = strtolower($item->category->category_name ?? '');

        // For network equipment, use specific patterns
        if (str_contains($itemName, 'modem') || str_contains($categoryName, 'modem')) {
            return [
                'pattern' => 'MDMXXXXXX',
                'prefix' => 'MDM',
                'description' => 'Modem serial format'
            ];
        }

        if (str_contains($itemName, 'router') || str_contains($categoryName, 'router')) {
            return [
                'pattern' => 'RTRXXXXXX',
                'prefix' => 'RTR',
                'description' => 'Router serial format'
            ];
        }

        if (str_contains($itemName, 'switch') || str_contains($categoryName, 'switch')) {
            return [
                'pattern' => 'SWTXXXXXX',
                'prefix' => 'SWT',
                'description' => 'Switch serial format'
            ];
        }

        // Default to F1 format
        return [
            'pattern' => 'F1XXXXXX',
            'prefix' => 'F1',
            'description' => 'Standard F1 format'
        ];
    }

    /**
     * Generate auto serial numbers
     */
    private function generateAutoSerialNumbers(int $quantity, array $formatInfo, Item $item): array
    {
        if ($formatInfo['prefix'] === 'F1') {
            return $this->generateF1SerialNumbers($quantity);
        } else {
            return $this->generateCustomSerialNumbers($quantity, $formatInfo['prefix'], $item);
        }
    }

    /**
     * FLEXIBLE: Validate serial number - support ANY format
     */
    public function validateSerialNumber(Request $request)
    {
        try {
            $serialNumber = trim($request->get('serial_number'));

            // Basic validation
            if (empty($serialNumber)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Serial number tidak boleh kosong',
                    'error_type' => 'empty'
                ]);
            }

            // Enhanced format validation - support multiple formats
            $formatValidation = $this->validateAnySerialFormat($serialNumber);
            if (!$formatValidation['valid']) {
                return response()->json([
                    'valid' => false,
                    'message' => $formatValidation['message'],
                    'error_type' => 'format',
                    'format_detected' => $formatValidation['format_detected'] ?? 'unknown',
                    'suggestions' => $formatValidation['suggestions'] ?? []
                ]);
            }

            // Database duplicate check
            $existingItem = ItemDetail::where('serial_number', $serialNumber)
                ->with('item')
                ->first();

            if ($existingItem) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Serial number sudah digunakan',
                    'error_type' => 'duplicate',
                    'existing_info' => [
                        'item_code' => $existingItem->item->item_code ?? 'N/A',
                        'item_name' => $existingItem->item->item_name ?? 'N/A',
                        'status' => $existingItem->status ?? 'N/A',
                        'location' => $existingItem->location ?? 'N/A',
                        'created_at' => $existingItem->created_at?->format('d/m/Y H:i')
                    ]
                ]);
            }

            return response()->json([
                'valid' => true,
                'message' => 'Serial number tersedia dan format valid',
                'format_info' => [
                    'detected_format' => $formatValidation['format_detected'],
                    'is_generated' => $formatValidation['is_generated'],
                    'is_original' => $formatValidation['is_original'],
                    'length' => strlen($serialNumber),
                    'pattern_match' => true
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Serial number validation error', [
                'serial_number' => $request->get('serial_number'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'valid' => false,
                'message' => 'Error validasi: ' . $e->getMessage(),
                'error_type' => 'system_error'
            ], 500);
        }
    }

    /**
     * Validate any serial number format (flexible)
     */
    private function validateAnySerialFormat(string $serialNumber): array
    {
        $sn = strtoupper(trim($serialNumber));

        // Must have minimum length
        if (strlen($sn) < 4) {
            return [
                'valid' => false,
                'message' => 'Serial number minimal 4 karakter'
            ];
        }

        // Must have maximum length
        if (strlen($sn) > 30) {
            return [
                'valid' => false,
                'message' => 'Serial number maksimal 30 karakter'
            ];
        }

        // Must contain only allowed characters
        if (!preg_match('/^[A-Z0-9\-_]+$/', $sn)) {
            return [
                'valid' => false,
                'message' => 'Serial number hanya boleh huruf, angka, dash (-), underscore (_)',
                'suggestions' => [
                    'Hapus karakter spesial selain dash (-) dan underscore (_)',
                    'Gunakan huruf besar',
                    'Tidak boleh ada spasi'
                ]
            ];
        }

        // Detect format type
        $formatDetected = $this->detectSerialNumberFormat($sn);

        return [
            'valid' => true,
            'message' => 'Format serial number valid',
            'format_detected' => $formatDetected['type'],
            'is_generated' => $formatDetected['is_generated'],
            'is_original' => $formatDetected['is_original']
        ];
    }

    /**
     * Detect serial number format type
     */
    private function detectSerialNumberFormat(string $serialNumber): array
    {
        $sn = strtoupper($serialNumber);

        // F1 format
        if (str_starts_with($sn, 'F1') && strlen($sn) === 8) {
            return [
                'type' => 'F1 Generated',
                'is_generated' => true,
                'is_original' => false
            ];
        }

        // Custom generated formats
        $generatedPrefixes = ['MDM', 'RTR', 'SWT', 'CBL'];
        foreach ($generatedPrefixes as $prefix) {
            if (str_starts_with($sn, $prefix)) {
                return [
                    'type' => $prefix . ' Generated',
                    'is_generated' => true,
                    'is_original' => false
                ];
            }
        }

        // Original manufacturer patterns
        if (preg_match('/^ZTE|^HW|^TP|^DL|^ZX/', $sn)) {
            return [
                'type' => 'Manufacturer Original',
                'is_generated' => false,
                'is_original' => true
            ];
        }

        // Generic serial number
        return [
            'type' => 'Custom/Original',
            'is_generated' => false,
            'is_original' => true
        ];
    }

    /**
     * Enhanced generate F1 serial numbers (keeping existing method)
     */
    private function generateF1SerialNumbers(int $quantity, array $excludeList = []): array
    {
        // Keep existing F1 generation logic...
        $serialNumbers = [];
        $maxAttempts = $quantity * 20;
        $attempts = 0;

        $existingF1Serials = ItemDetail::where('serial_number', 'LIKE', 'F1%')
            ->pluck('serial_number')
            ->toArray();

        $allExcludes = array_merge($excludeList, $existingF1Serials);

        while (count($serialNumbers) < $quantity && $attempts < $maxAttempts) {
            $attempts++;

            $randomPart = $this->generateRandomAlphanumeric(6);
            $serialNumber = 'F1' . $randomPart;

            if (
                !in_array($serialNumber, $serialNumbers) &&
                !in_array($serialNumber, $allExcludes)
            ) {
                $serialNumbers[] = $serialNumber;
            }
        }

        return $serialNumbers;
    }

    /**
     * Enhanced random alphanumeric generation
     */
    private function generateRandomAlphanumeric(int $length): string
    {
        // Exclude confusing characters
        $characters = 'ACDEFHJKLMNPQRTUVWXYZ23479';
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $result;
    }

    /**
     * Get suppliers untuk direct receipts
     */
    // public function getSuppliers(Request $request)
    // {
    //     $suppliers = Supplier::active()
    //         ->when($request->filled('search'), function ($query) use ($request) {
    //             $query->where('supplier_name', 'like', '%' . $request->search . '%');
    //         })
    //         ->orderBy('supplier_name')
    //         ->get(['supplier_id', 'supplier_name', 'supplier_code']);

    //     return response()->json([
    //         'success' => true,
    //         'data' => $suppliers
    //     ]);
    // }

    /**
     * Get items untuk direct receipts
     */
    // public function getItems(Request $request)
    // {
    //     $items = Item::with('category')
    //         ->active()
    //         ->when($request->filled('search'), function ($query) use ($request) {
    //             $search = $request->search;
    //             $query->where(function ($q) use ($search) {
    //                 $q->where('item_name', 'like', '%' . $search . '%')
    //                     ->orWhere('item_code', 'like', '%' . $search . '%');
    //             });
    //         })
    //         ->orderBy('item_name')
    //         ->get(['item_id', 'item_code', 'item_name', 'unit'])
    //         ->map(function ($item) {
    //             return [
    //                 'item_id' => $item->item_id,
    //                 'item_code' => $item->item_code,
    //                 'item_name' => $item->item_name,
    //                 'unit' => $item->unit,
    //                 'category_name' => $item->category->category_name ?? 'N/A'
    //             ];
    //         });

    //     return response()->json([
    //         'success' => true,
    //         'data' => $items
    //     ]);
    // }

    /**
     * Generate receive number berdasarkan receipt type
     */
    // public function generateReceiveNumber(Request $request)
    // {
    //     $receiptType = $request->get('receipt_type', 'po_based');
    //     $receiveNumber = GoodsReceived::generateReceiveNumber($receiptType);

    //     return response()->json([
    //         'success' => true,
    //         'data' => [
    //             'receive_number' => $receiveNumber,
    //             'receipt_type' => $receiptType
    //         ]
    //     ]);
    // }

    /**
     * Generate receive number berdasarkan receipt type
     */
    public function generateReceiveNumber(Request $request)
    {
        $receiptType = $request->get('receipt_type', 'po_based');
        $receiveNumber = GoodsReceived::generateReceiveNumber($receiptType);

        return response()->json([
            'success' => true,
            'data' => [
                'receive_number' => $receiveNumber,
                'receipt_type' => $receiptType
            ]
        ]);
    }

    /**
     * Get suppliers untuk direct receipts
     */
    public function getSuppliers(Request $request)
    {
        $suppliers = Supplier::active()
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('supplier_name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('supplier_name')
            ->get(['supplier_id', 'supplier_name', 'supplier_code']);

        return response()->json([
            'success' => true,
            'data' => $suppliers
        ]);
    }

    /**
     * Get items untuk direct receipts
     */
    public function getItems(Request $request)
    {
        $items = Item::with('category')
            ->active()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('item_name', 'like', '%' . $search . '%')
                        ->orWhere('item_code', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('item_name')
            ->get(['item_id', 'item_code', 'item_name', 'unit'])
            ->map(function ($item) {
                return [
                    'item_id' => $item->item_id,
                    'item_code' => $item->item_code,
                    'item_name' => $item->item_name,
                    'unit' => $item->unit,
                    'category_name' => $item->category->category_name ?? 'N/A'
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Simple serial number template generation
     */
    public function getSerialNumberTemplate(Request $request)
    {
        try {
            $itemId = $request->get('item_id');
            $quantity = (int) $request->get('quantity', 1);

            if (!$itemId || $quantity <= 0 || $quantity > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item ID dan quantity (1-100) wajib diisi'
                ], 400);
            }

            $item = Item::find($itemId);
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item tidak ditemukan'
                ], 404);
            }

            // Simple F1 generation
            $serialNumbers = $this->generateSimpleF1SerialNumbers($quantity);

            return response()->json([
                'success' => true,
                'data' => [
                    'serial_numbers' => $serialNumbers,
                    'item_info' => [
                        'item_id' => $item->item_id,
                        'item_code' => $item->item_code,
                        'item_name' => $item->item_name
                    ]
                ],
                'message' => "Generated {$quantity} serial numbers"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate serial numbers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simple F1 serial number generation
     */
    private function generateSimpleF1SerialNumbers(int $quantity): array
    {
        $serialNumbers = [];
        $characters = 'ACDEFHJKLMNPQRTUVWXYZ23479'; // Exclude confusing chars

        for ($i = 0; $i < $quantity; $i++) {
            $attempts = 0;
            do {
                $randomPart = '';
                for ($j = 0; $j < 6; $j++) {
                    $randomPart .= $characters[random_int(0, strlen($characters) - 1)];
                }
                $serialNumber = 'F1' . $randomPart;
                $attempts++;
            } while (
                in_array($serialNumber, $serialNumbers) ||
                ItemDetail::where('serial_number', $serialNumber)->exists() &&
                $attempts < 50
            );

            if ($attempts < 50) {
                $serialNumbers[] = $serialNumber;
            }
        }

        return $serialNumbers;
    }

    // /**
    //  * Simple serial number validation
    //  */
    // public function validateSerialNumber(Request $request)
    // {
    //     $serialNumber = trim(string: $request->get('serial_number'));

    //     if (empty($serialNumber)) {
    //         return response()->json([
    //             'valid' => false,
    //             'message' => 'Serial number tidak boleh kosong'
    //         ]);
    //     }

    //     $exists = ItemDetail::where('serial_number', $serialNumber)->exists();

    //     return response()->json([
    //         'valid' => !$exists,
    //         'message' => $exists ? 'Serial number sudah digunakan' : 'Serial number tersedia'
    //     ]);
    // }

    // /**
    //  * Generate GR ID
    //  */
    // private function generateGRId(): string
    // {
    //     $lastGR = GoodsReceived::orderBy('gr_id', 'desc')->first();
    //     $lastNumber = $lastGR ? (int) substr($lastGR->gr_id, 2) : 0;
    //     $newNumber = $lastNumber + 1;
    //     return 'GR' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    // }
}
