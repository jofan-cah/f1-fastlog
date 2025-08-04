<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\ItemDetail;
use App\Models\Item;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    /**
     * Display transaction dashboard
     */
    /**
     * Display transaction dashboard - Updated to handle all types
     */

    public function index(Request $request)
    {
        $currentType = $request->get('type');
        $allowedTypes = Transaction::getUserAllowedTypes();

        // ✅ NEW: Get search and filter parameters including date
        $search = $request->get('search');
        $statusFilter = $request->get('status');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // ✅ SUPER SIMPLE: LVL003 hanya boleh IN dan OUT
        $user = auth()->user();
        $userLevel = $user->userLevel->level_name ?? '';
        // dd($userLevel);

        if ($userLevel == 'Teknisi' && $currentType && !in_array($currentType, ['IN', 'OUT'])) {
            return redirect()->route('dashboard');
        }

        // Define transaction type configurations
        $typeConfigs = [
            'IN' => [
                'text' => 'Barang Masuk',
                'description' => 'Kelola transaksi barang masuk ke sistem',
                'icon' => 'fas fa-arrow-down',
                'gradient' => 'from-green-600 to-green-700',
                'class' => 'bg-green-100 text-green-800'
            ],
            'OUT' => [
                'text' => 'Barang Keluar',
                'description' => 'Kelola transaksi barang keluar dari sistem',
                'icon' => 'fas fa-arrow-up',
                'gradient' => 'from-blue-600 to-blue-700',
                'class' => 'bg-blue-100 text-blue-800'
            ],
            'REPAIR' => [
                'text' => 'Barang Repair',
                'description' => 'Kelola transaksi barang yang perlu diperbaiki',
                'icon' => 'fas fa-wrench',
                'gradient' => 'from-yellow-600 to-yellow-700',
                'class' => 'bg-yellow-100 text-yellow-800'
            ],
            'LOST' => [
                'text' => 'Barang Hilang',
                'description' => 'Kelola transaksi barang yang hilang',
                'icon' => 'fas fa-exclamation-triangle',
                'gradient' => 'from-red-600 to-red-700',
                'class' => 'bg-red-100 text-red-800'
            ],
            'RETURN' => [
                'text' => 'Pengembalian',
                'description' => 'Kelola transaksi pengembalian barang',
                'icon' => 'fas fa-undo',
                'gradient' => 'from-purple-600 to-purple-700',
                'class' => 'bg-purple-100 text-purple-800'
            ]
        ];

        // Get current type config
        $currentTypeConfig = $currentType ? ($typeConfigs[$currentType] ?? null) : null;

        // Build query
        $query = Transaction::with(['item', 'createdBy', 'approvedBy']);

        // ✅ FIXED: Teknisi tidak bisa lihat transaksi
        if (auth()->user()->userLevel && strtolower(auth()->user()->userLevel->level_name) === 'teknisi') {
            $query->whereRaw('1 = 0'); // Empty result for teknisi
        }

        // Apply type filter if specified
        if ($currentType && in_array($currentType, $allowedTypes)) {
            $query->where('transaction_type', $currentType);
        }

        // ✅ NEW: Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($itemQuery) use ($search) {
                        $itemQuery->where('item_name', 'like', "%{$search}%")
                            ->orWhere('item_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('createdBy', function ($userQuery) use ($search) {
                        $userQuery->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        // ✅ NEW: Apply status filter
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        // ✅ NEW: Apply date filters
        if ($dateFrom) {
            $query->whereDate('transaction_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('transaction_date', '<=', $dateTo);
        }

        // Get transactions
        $transactions = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($transaction) {
                $typeInfo = $transaction->getTypeInfo();
                $statusInfo = $transaction->getStatusInfo();

                return [
                    'id' => $transaction->transaction_id,
                    'transaction_number' => $transaction->transaction_number,
                    'transaction_type' => $transaction->transaction_type,
                    'status' => $transaction->status,
                    'item_name' => $transaction->item->item_name ?? 'Unknown',
                    'item_code' => $transaction->item->item_code ?? 'N/A',
                    'created_by_name' => $transaction->createdBy->full_name ?? 'Unknown',
                    'approved_by_name' => $transaction->approvedBy->full_name ?? null,
                    'transaction_date' => $transaction->transaction_date->format('d M Y H:i'),
                    'approved_date' => $transaction->approved_date ? $transaction->approved_date->format('d M Y H:i') : null,

                    // Type info
                    'type_text' => $typeInfo['text'],
                    'type_icon' => $typeInfo['icon'],
                    'type_class' => $typeInfo['class'],

                    // Status info
                    'status_text' => $statusInfo['text'],
                    'status_icon' => $statusInfo['icon'],
                    'status_class' => $statusInfo['class'],

                    // Action permissions
                    'can_edit' => $transaction->created_by === auth()->id() && $transaction->status === Transaction::STATUS_PENDING,
                    'can_approve' => Transaction::canUserApprove() && $transaction->status === Transaction::STATUS_PENDING,
                    'can_cancel' => $transaction->created_by === auth()->id() && $transaction->canBeCancelled(),
                ];
            });

        // ✅ NEW: Get statistics with all filters applied
        $stats = $this->getTransactionStats($currentType, $search, $statusFilter, $dateFrom, $dateTo);

        // Get available transaction types and statuses
        $transactionTypes = Transaction::getTransactionTypes();
        $transactionStatuses = Transaction::getStatuses();

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'transactions' => $transactions,
                'stats' => $stats,
                'filters' => [
                    'search' => $search,
                    'status' => $statusFilter,
                    'type' => $currentType,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo
                ]
            ]);
        }

        return view('transactions.index', compact(
            'transactions',
            'stats',
            'currentType',
            'currentTypeConfig',
            'transactionTypes',
            'transactionStatuses',
            'allowedTypes'
        ));
    }

    // ✅ UPDATED: getTransactionStats with date filters
    private function getTransactionStats($currentType = null, $search = null, $statusFilter = null, $dateFrom = null, $dateTo = null)
    {
        $query = Transaction::query();

        // Teknisi tidak bisa lihat stats
        if (auth()->user()->userLevel && strtolower(auth()->user()->userLevel->level_name) === 'teknisi') {
            $query->whereRaw('1 = 0');
        }

        // Apply type filter
        if ($currentType) {
            $query->where('transaction_type', $currentType);
        }

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($itemQuery) use ($search) {
                        $itemQuery->where('item_name', 'like', "%{$search}%")
                            ->orWhere('item_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('createdBy', function ($userQuery) use ($search) {
                        $userQuery->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        // Apply date filters
        if ($dateFrom) {
            $query->whereDate('transaction_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('transaction_date', '<=', $dateTo);
        }

        // Get base stats
        $totalQuery = clone $query;
        $pendingQuery = clone $query;
        $approvedTodayQuery = clone $query;
        $totalApprovedQuery = clone $query;

        $total = $totalQuery->count();
        $pending = $pendingQuery->where('status', Transaction::STATUS_PENDING)->count();

        // Approved today - consider date filter
        if ($dateFrom || $dateTo) {
            // If date filter applied, show approved in that range
            $approvedToday = $approvedTodayQuery->where('status', Transaction::STATUS_APPROVED)->count();
        } else {
            // Default behavior - approved today only
            $approvedToday = $approvedTodayQuery
                ->where('status', Transaction::STATUS_APPROVED)
                ->whereDate('approved_date', today())
                ->count();
        }

        $totalApproved = $totalApprovedQuery->where('status', Transaction::STATUS_APPROVED)->count();

        // Calculate success rate
        $successRate = $total > 0 ? round(($totalApproved / $total) * 100, 1) : 0;

        return [
            'total_transactions' => $total,
            'pending_count' => $pending,
            'approved_today' => $approvedToday,
            'success_rate' => $successRate,

            // Additional breakdown stats
            'approved_count' => $totalApproved,
            'rejected_count' => (clone $query)->where('status', Transaction::STATUS_REJECTED)->count(),

            // Date range info for frontend
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo,
                'is_filtered' => $dateFrom || $dateTo
            ]
        ];
    }

    // public function index(Request $request)
    // {
    //     $currentType = $request->get('type');
    //     $allowedTypes = Transaction::getUserAllowedTypes();

    //     // Define transaction type configurations
    //     $typeConfigs = [
    //         'IN' => [
    //             'text' => 'Barang Masuk',
    //             'description' => 'Kelola transaksi barang masuk ke sistem',
    //             'icon' => 'fas fa-arrow-down',
    //             'gradient' => 'from-green-600 to-green-700',
    //             'class' => 'bg-green-100 text-green-800'
    //         ],
    //         'OUT' => [
    //             'text' => 'Barang Keluar',
    //             'description' => 'Kelola transaksi barang keluar dari sistem',
    //             'icon' => 'fas fa-arrow-up',
    //             'gradient' => 'from-blue-600 to-blue-700',
    //             'class' => 'bg-blue-100 text-blue-800'
    //         ],
    //         'REPAIR' => [
    //             'text' => 'Barang Repair',
    //             'description' => 'Kelola transaksi barang yang perlu diperbaiki',
    //             'icon' => 'fas fa-wrench',
    //             'gradient' => 'from-yellow-600 to-yellow-700',
    //             'class' => 'bg-yellow-100 text-yellow-800'
    //         ],
    //         'LOST' => [
    //             'text' => 'Barang Hilang',
    //             'description' => 'Kelola transaksi barang yang hilang',
    //             'icon' => 'fas fa-exclamation-triangle',
    //             'gradient' => 'from-red-600 to-red-700',
    //             'class' => 'bg-red-100 text-red-800'
    //         ],
    //         'RETURN' => [
    //             'text' => 'Pengembalian',
    //             'description' => 'Kelola transaksi pengembalian barang',
    //             'icon' => 'fas fa-undo',
    //             'gradient' => 'from-purple-600 to-purple-700',
    //             'class' => 'bg-purple-100 text-purple-800'
    //         ]
    //     ];

    //     // Get current type config
    //     $currentTypeConfig = $currentType ? ($typeConfigs[$currentType] ?? null) : null;

    //     // Build query
    //     $query = Transaction::with(['item', 'createdBy', 'approvedBy']);
    //     if (auth()->user()->userLevel && strtolower(auth()->user()->userLevel->level_name) === 'teknisi') {
    //         // Teknisi tidak bisa lihat transaksi sama sekali
    //         $query->where('transaction_id', null); // This will return empty result
    //     }

    //     // Apply type filter if specified
    //     if ($currentType && in_array($currentType, $allowedTypes)) {
    //         $query->where('transaction_type', $currentType);
    //     }

    //     // Get transactions
    //     $transactions = $query->orderBy('created_at', 'desc')
    //         ->get()
    //         ->map(function ($transaction) {
    //             $typeInfo = $transaction->getTypeInfo();
    //             $statusInfo = $transaction->getStatusInfo();

    //             return [
    //                 'id' => $transaction->transaction_id,
    //                 'transaction_number' => $transaction->transaction_number,
    //                 'transaction_type' => $transaction->transaction_type,
    //                 'status' => $transaction->status,
    //                 'item_name' => $transaction->item->item_name ?? 'Unknown',
    //                 'item_code' => $transaction->item->item_code ?? 'N/A',
    //                 'created_by_name' => $transaction->createdBy->full_name ?? 'Unknown',
    //                 'approved_by_name' => $transaction->approvedBy->full_name ?? null,
    //                 'transaction_date' => $transaction->transaction_date->format('d M Y H:i'),
    //                 'approved_date' => $transaction->approved_date ? $transaction->approved_date->format('d M Y H:i') : null,

    //                 // Type info
    //                 'type_text' => $typeInfo['text'],
    //                 'type_icon' => $typeInfo['icon'],
    //                 'type_class' => $typeInfo['class'],

    //                 // Status info
    //                 'status_text' => $statusInfo['text'],
    //                 'status_icon' => $statusInfo['icon'],
    //                 'status_class' => $statusInfo['class'],

    //                 // Action permissions
    //                 'can_edit' => $transaction->created_by === auth()->id() && $transaction->status === Transaction::STATUS_PENDING,
    //                 'can_approve' => Transaction::canUserApprove() && $transaction->status === Transaction::STATUS_PENDING,
    //                 'can_cancel' => $transaction->created_by === auth()->id() && $transaction->canBeCancelled(),
    //             ];
    //         });

    //     // Get statistics
    //     $stats = $this->getTransactionStats($currentType);

    //     // Get available transaction types and statuses
    //     $transactionTypes = Transaction::getTransactionTypes();
    //     $transactionStatuses = Transaction::getStatuses();

    //     // Handle AJAX requests
    //     if ($request->ajax()) {
    //         return response()->json([
    //             'success' => true,
    //             'transactions' => $transactions,
    //             'stats' => $stats
    //         ]);
    //     }

    //     return view('transactions.index', compact(
    //         'transactions',
    //         'stats',
    //         'currentType',
    //         'currentTypeConfig',
    //         'transactionTypes',
    //         'transactionStatuses',
    //         'allowedTypes'
    //     ));
    // }

    // /**
    //  * Get transaction statistics based on type filter
    //  */
    // private function getTransactionStats($type = null)
    // {
    //     $query = Transaction::query();

    //     // Apply user filter for teknisi
    //     if (auth()->user()->userLevel->level_name === 'teknisi') {
    //         $query->where('created_by', auth()->id());
    //     }

    //     // Apply type filter
    //     if ($type) {
    //         $query->where('transaction_type', $type);
    //     }

    //     $totalTransactions = $query->count();
    //     $pendingCount = (clone $query)->where('status', Transaction::STATUS_PENDING)->count();
    //     $approvedToday = (clone $query)->where('status', Transaction::STATUS_APPROVED)
    //         ->whereDate('approved_date', today())->count();
    //     $rejectedCount = (clone $query)->where('status', Transaction::STATUS_REJECTED)->count();

    //     $successRate = $totalTransactions > 0 ?
    //         round((($totalTransactions - $rejectedCount) / $totalTransactions) * 100, 1) : 0;

    //     return [
    //         'total_transactions' => $totalTransactions,
    //         'pending_count' => $pendingCount,
    //         'approved_today' => $approvedToday,
    //         'rejected_count' => $rejectedCount,
    //         'success_rate' => $successRate,
    //     ];
    // }
    /**
     * Show create transaction form - Updated for dual method
     */
    public function create(Request $request)
    {
        $allowedTypes = Transaction::getUserAllowedTypes();

        // Get categories for manual selection
        $categories = \App\Models\Category::where('is_active', true)
            ->orderBy('category_name')
            ->get(['category_id', 'category_name']);

        // If QR data provided, pre-fill form
        $qrData = null;
        $itemDetail = null;

        if ($request->has('qr_data')) {
            try {
                $qrData = json_decode($request->qr_data, true);
                if ($qrData && isset($qrData['item_detail_id'])) {
                    $itemDetail = ItemDetail::with(['item.category'])
                        ->where('item_detail_id', $qrData['item_detail_id'])
                        ->first();
                }
            } catch (\Exception $e) {
                // Invalid QR data, continue without pre-filling
            }
        }

        // Get transaction types with proper labels
        $transactionTypes = Transaction::getTransactionTypes();

        // Filter allowed types for current user
        $filteredTypes = [];
        foreach ($allowedTypes as $type) {
            if (isset($transactionTypes[$type])) {
                $filteredTypes[$type] = $transactionTypes[$type];
            }
        }

        return view('transactions.create', compact(
            'allowedTypes',
            'categories',
            'qrData',
            'itemDetail',
            'filteredTypes',
            'transactionTypes' // Add this line
        ));
    }

    public function store(Request $request)
    {
        // Simplified validation - always expect items array
        $request->validate([
            'transaction_type' => 'required|in:IN,OUT,REPAIR,LOST,RETURN',
            'items' => 'required|array|min:1',
            'items.*.item_detail_id' => 'required|exists:item_details,item_detail_id',
            'items.*.notes' => 'nullable|string',
            'reference_id' => 'nullable|string|max:100',
            'from_location' => 'nullable|string|max:100',
            'to_location' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'kondisi' => 'nullable|in:good,no_good'
        ]);

        // dd($request->all());
        // dd( $request->kondisi);

        try {
            DB::beginTransaction();

            $items = $request->items;

            // Validate all items exist and check status
            $itemDetails = [];
            foreach ($items as $itemData) {
                $itemDetail = ItemDetail::where('item_detail_id', $itemData['item_detail_id'])->first();

                if (!$itemDetail) {
                    throw new \Exception("Item detail {$itemData['item_detail_id']} not found");
                }

                // Status validation logic (keep existing validation)
                if ($itemDetail->status === 'stock') {
                    throw new \Exception("Item {$itemDetail->serial_number} dengan status 'Stock' tidak dapat ditransaksikan.");
                }

                // Other validations (keep existing)...

                $itemDetails[] = [
                    'detail' => $itemDetail,
                    'notes' => $itemData['notes'] ?? null,
                    'kondisi' => $request->kondisi ?? 'good',
                ];
            }

            // Create main transaction
            $transaction = Transaction::create([
                'transaction_id' => Transaction::generateTransactionId(),
                'transaction_number' => Transaction::generateTransactionNumber($request->transaction_type),
                'transaction_type' => $request->transaction_type,
                'reference_id' => $request->reference_id,
                'item_id' => $itemDetails[0]['detail']->item_id,
                'quantity' => count($items),
                'from_location' => $request->from_location,
                // 'kondisi' => $request->kondisi ?? 'good',
                'to_location' => $request->to_location,
                'notes' => $request->notes,
                'status' => Transaction::STATUS_PENDING,
                'created_by' => auth()->id(),
                'transaction_date' => now(),
            ]);

            // Create transaction details
            foreach ($itemDetails as $itemInfo) {
                TransactionDetail::create([
                    'transaction_detail_id' => TransactionDetail::generateTransactionDetailId(),
                    'transaction_id' => $transaction->transaction_id,
                    'item_detail_id' => $itemInfo['detail']->item_detail_id,
                    'status_before' => $request->kondisi !== 'good' ? 'no_good' : 'good',
                    'status_after' => $request->kondisi,
                    'notes' => $itemInfo['notes'],
                ]);

                // Update kondisi di tabel item_details
                $itemInfo['detail']->kondisi = $itemInfo['kondisi'];
                $itemInfo['detail']->save();
            }





            // Log activity
            ActivityLog::logActivity(
                'transactions',
                $transaction->transaction_id,
                'create_transaction',
                null,
                [
                    'transaction_type' => $transaction->transaction_type,
                    'items_count' => count($items),
                    'item_detail_ids' => collect($items)->pluck('item_detail_id')->toArray()
                ]
            );

            DB::commit();

            // Return response
            $itemsCount = count($items);
            return response()->json([
                'success' => true,
                'message' => $itemsCount === 1
                    ? 'Transaksi berhasil dibuat dengan 1 item'
                    : "Transaksi berhasil dibuat dengan {$itemsCount} items",
                'transaction' => [
                    'transaction_id' => $transaction->transaction_id,
                    'transaction_number' => $transaction->transaction_number,
                    'items_count' => $itemsCount,
                    'status' => $transaction->status
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create transaction: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'items_count' => count($request->items ?? [])
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Create transaction from QR scan (AJAX)
     */
    public function createFromQR(Request $request)
    {
        $request->validate([
            'qr_content' => 'required|json',
            'transaction_type' => 'required|in:' . implode(',', Transaction::getUserAllowedTypes()),
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $qrData = json_decode($request->qr_content, true);

            $transactionData = [
                'transaction_type' => $request->transaction_type,
                'notes' => $request->notes,
                'reference_id' => $request->reference_id,
                'reference_type' => $request->reference_type,
                'to_location' => $request->to_location,
            ];

            $result = Transaction::createFromQRScan($qrData, $transactionData);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'transaction' => [
                        'id' => $result['transaction']->transaction_id,
                        'number' => $result['transaction']->transaction_number,
                        'status' => $result['transaction']->getStatusInfo(),
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create transaction from QR: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat transaksi dari QR scan'
            ], 500);
        }
    }




    /**
     * Show transaction detail
     */
    public function show(Transaction $transaction)
    {
        // Check permission
        $user = auth()->user();
        $levelName = strtolower($user->getUserLevel()->level_name ?? '');

        if ($levelName === 'teknisi' && $transaction->created_by !== $user->user_id) {
            abort(403, 'Unauthorized access');
        }

        $transaction->load([
            'item',
            'createdBy',
            'approvedBy',
            'transactionDetails.itemDetail'
        ]);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show edit form (only for pending transactions)
     */
    public function edit(Transaction $transaction)
    {
        // dd(auth()->user()->userLevel->level_name);
        // Check permission - only creator can edit pending transactions
        if (auth()->user()->userLevel->level_name !== 'Admin' && auth()->user()->userLevel->level_name !== 'Logistik' ) {
            abort(403, 'Cannot edit this transaction');
        }

        $allowedTypes = Transaction::getUserAllowedTypes();
        $transaction->load(['transactionDetails.itemDetail.item']);

        return view('transactions.edit', compact('transaction', 'allowedTypes'));
    }

    /**
     * Update transaction (only pending transactions)
     */
    public function update(Request $request, Transaction $transaction)
    {
        // Check permission
        if ($transaction->created_by !== auth()->id() || $transaction->status !== Transaction::STATUS_PENDING) {
            abort(403, 'Cannot update this transaction');
        }

        $request->validate([
            'transaction_type' => 'required|in:' . implode(',', Transaction::getUserAllowedTypes()),
            'notes' => 'nullable|string|max:1000',
            'reference_id' => 'nullable|string|max:100',
            'reference_type' => 'nullable|string|max:50',
            'to_location' => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            $oldData = $transaction->toArray();

            $transaction->update([
                'transaction_type' => $request->transaction_type,
                'reference_id' => $request->reference_id,
                'reference_type' => $request->reference_type,
                'to_location' => $request->to_location,
                'notes' => $request->notes,
            ]);

            // Update transaction detail notes if provided
            if ($request->has('detail_notes')) {
                $transaction->transactionDetails()->update([
                    'notes' => $request->detail_notes
                ]);
            }

            // Log activity
            ActivityLog::logActivity(
                'transactions',
                $transaction->transaction_id,
                'update',
                $oldData,
                $transaction->toArray()
            );

            DB::commit();

            return redirect()->route('transactions.show', $transaction)
                ->with('success', 'Transaksi berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update transaction: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal update transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Cancel transaction (only creator for pending transactions)
     */
    public function cancel(Transaction $transaction)
    {
        // Check permission
        if ($transaction->created_by !== auth()->id() || !$transaction->canBeCancelled()) {
            abort(403, 'Cannot cancel this transaction');
        }

        try {
            $transaction->update([
                'status' => Transaction::STATUS_CANCELLED,
                'approved_by' => auth()->id(),
                'approved_date' => now(),
                'notes' => $transaction->notes . "\n\nCancelled by user at " . now()->format('Y-m-d H:i:s')
            ]);

            // Log activity
            ActivityLog::logActivity(
                'transactions',
                $transaction->transaction_id,
                'cancel',
                ['status' => $transaction->getOriginal('status')],
                ['status' => Transaction::STATUS_CANCELLED]
            );

            return redirect()->route('transactions.index')
                ->with('success', 'Transaksi berhasil dibatalkan');
        } catch (\Exception $e) {
            Log::error('Failed to cancel transaction: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal membatalkan transaksi');
        }
    }

    /**
     * Get available items for transaction type (AJAX)
     */
    public function getAvailableItems(Request $request)
    {
        $request->validate([
            'transaction_type' => 'required|in:' . implode(',', Transaction::getUserAllowedTypes()),
        ]);

        try {
            $items = ItemDetail::getAvailableForTransaction($request->transaction_type);

            return response()->json([
                'success' => true,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get available items'
            ], 500);
        }
    }

    /**
     * Scan QR and get item info (AJAX)
     */
    public function scanQR(Request $request)
    {
        $request->validate([
            'qr_content' => 'required|json'
        ]);

        try {
            $qrData = json_decode($request->qr_content, true);

            if (!$qrData || !isset($qrData['item_detail_id'])) {
                throw new \Exception('Invalid QR code format');
            }

            $itemDetail = ItemDetail::with('item')
                ->where('item_detail_id', $qrData['item_detail_id'])
                ->first();

            if (!$itemDetail) {
                throw new \Exception('Item not found');
            }

            // Get available transaction types for this item
            $availableTypes = $itemDetail->getAvailableTransactionTypes();
            $userAllowedTypes = Transaction::getUserAllowedTypes();
            $allowedTypes = array_intersect($availableTypes, $userAllowedTypes);

            $transactionTypes = [];
            foreach ($allowedTypes as $type) {
                $transactionTypes[$type] = Transaction::getTransactionTypes()[$type];
            }

            return response()->json([
                'success' => true,
                'item_detail' => [
                    'id' => $itemDetail->item_detail_id,
                    'serial_number' => $itemDetail->serial_number,
                    'item_name' => $itemDetail->item->item_name,
                    'item_code' => $itemDetail->item->item_code,
                    'current_status' => $itemDetail->status,
                    'location' => $itemDetail->location,
                    'status_info' => $itemDetail->getStatusInfo(),
                ],
                'available_transaction_types' => $transactionTypes,
                'qr_data' => $qrData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get transaction history for user
     */
    public function history(Request $request)
    {
        $query = Transaction::with(['item', 'createdBy', 'approvedBy'])
            ->when(auth()->user()->getUserLevel()->level_name === 'teknisi', function ($q) {
                $q->where('created_by', auth()->id());
            });

        // Apply filters
        if ($request->has('type') && $request->type !== '') {
            $query->where('transaction_type', $request->type);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->paginate(20);

        $transactionTypes = Transaction::getTransactionTypes();
        $transactionStatuses = Transaction::getStatuses();

        return view('transactions.history', compact(
            'transactions',
            'transactionTypes',
            'transactionStatuses'
        ));
    }

    /**
     * Export transaction history
     */
    public function export(Request $request)
    {
        // This would implement export functionality
        // For now, return JSON response

        $query = Transaction::with(['item', 'createdBy', 'approvedBy', 'transactionDetails.itemDetail'])
            ->when(auth()->user()->getUserLevel()->level_name === 'teknisi', function ($q) {
                $q->where('created_by', auth()->id());
            });

        // Apply same filters as history
        if ($request->has('type') && $request->type !== '') {
            $query->where('transaction_type', $request->type);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        $exportData = $transactions->map(function ($transaction) {
            return [
                'transaction_number' => $transaction->transaction_number,
                'type' => $transaction->getTypeInfo()['text'],
                'status' => $transaction->getStatusInfo()['text'],
                'item_name' => $transaction->item->item_name ?? '',
                'item_code' => $transaction->item->item_code ?? '',
                'serial_numbers' => $transaction->transactionDetails->pluck('itemDetail.serial_number')->join(', '),
                'notes' => $transaction->notes,
                'created_by' => $transaction->createdBy->full_name ?? '',
                'approved_by' => $transaction->approvedBy->full_name ?? '',
                'transaction_date' => $transaction->transaction_date->format('Y-m-d H:i:s'),
                'approved_date' => $transaction->approved_date?->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $exportData,
            'filename' => 'transaction_history_' . now()->format('Y-m-d_H-i-s') . '.json'
        ]);
    }
}
