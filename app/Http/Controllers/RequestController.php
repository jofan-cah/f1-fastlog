<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\ItemDetail;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    /**
     * Display item request dashboard for teknisi
     */
    public function index()
    {
        $user = auth()->user();

        // Get user's requests
        $myRequests = Transaction::where('created_by', $user->user_id)
            ->with(['item', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get request statistics
        $stats = [
            'total_requests' => Transaction::where('created_by', $user->user_id)->count(),
            'pending_requests' => Transaction::where('created_by', $user->user_id)
                ->where('status', Transaction::STATUS_PENDING)->count(),
            'approved_today' => Transaction::where('created_by', $user->user_id)
                ->where('status', Transaction::STATUS_APPROVED)
                ->whereDate('approved_date', today())->count(),
            'rejected_requests' => Transaction::where('created_by', $user->user_id)
                ->where('status', Transaction::STATUS_REJECTED)->count(),
        ];

        // Get available items for quick request
        $availableItems = ItemDetail::available()
            ->with(['item.category', 'item.stock'])
            ->limit(20)
            ->get()
            ->groupBy('item.category.category_name');

        return view('requests.index', compact('myRequests', 'stats', 'availableItems'));
    }

    /**
     * Show request form
     */
    public function create(Request $request)
    {
        // Get available items by category
        $categories = Category::whereHas('items.itemDetails', function ($query) {
            $query->where('status', 'available');
        })->with(['items.itemDetails' => function ($query) {
            $query->where('status', 'available')->with('item');
        }])->get();

        // If QR data provided, pre-fill form
        $qrData = null;
        $selectedItem = null;

        if ($request->has('qr_data')) {
            $qrData = json_decode($request->qr_data, true);
            if ($qrData && isset($qrData['item_detail_id'])) {
                $selectedItem = ItemDetail::with(['item.category'])
                    ->where('item_detail_id', $qrData['item_detail_id'])
                    ->first();
            }
        }

        return view('requests.create', compact('categories', 'qrData', 'selectedItem'));
    }

    /**
     * Store new item request
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_detail_id' => 'required|exists:item_details,item_detail_id',
            'request_type' => 'required|in:OUT,RETURN',
            'purpose' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'expected_return_date' => 'nullable|date|after:today',
            'project_code' => 'nullable|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            // Get item detail
            $itemDetail = ItemDetail::with('item')->findOrFail($request->item_detail_id);

            // Validate item availability
            if ($request->request_type === 'OUT' && !$itemDetail->isAvailable()) {
                throw new \Exception('Item tidak tersedia untuk permintaan keluar');
            }

            if ($request->request_type === 'RETURN' && $itemDetail->status !== 'used') {
                throw new \Exception('Item tidak dapat dikembalikan karena tidak dalam status terpakai');
            }

            // Create transaction
            $transaction = Transaction::create([
                'transaction_type' => $request->request_type === 'OUT' ? Transaction::TYPE_OUT : Transaction::TYPE_IN,
                'reference_id' => $request->project_code,
                'reference_type' => 'project',
                'item_id' => $itemDetail->item_id,
                'quantity' => 1,
                'from_location' => $itemDetail->location,
                'to_location' => $request->request_type === 'OUT' ? 'Field Work' : $itemDetail->location,
                'notes' => "Purpose: {$request->purpose}\n" . ($request->notes ?: ''),
                'status' => Transaction::STATUS_PENDING,
                'created_by' => auth()->id(),
                'transaction_date' => now(),
            ]);

            // Create transaction detail
            \App\Models\TransactionDetail::create([
                'transaction_id' => $transaction->transaction_id,
                'item_detail_id' => $itemDetail->item_detail_id,
                'status_before' => $itemDetail->status,
                'status_after' => null, // Will be set when approved
                'notes' => $request->expected_return_date ? "Expected return: {$request->expected_return_date}" : null,
            ]);

            DB::commit();

            return redirect()->route('requests.index')
                ->with('success', "Permintaan {$transaction->transaction_number} berhasil dibuat dan menunggu approval");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Quick request from QR scan (AJAX)
     */
    public function quickRequest(Request $request)
    {
        $request->validate([
            'qr_content' => 'required|json',
            'purpose' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
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

            if (!$itemDetail->isAvailable()) {
                throw new \Exception('Item tidak tersedia untuk permintaan');
            }

            // Create OUT transaction
            $transactionData = [
                'transaction_type' => Transaction::TYPE_OUT,
                'notes' => "Purpose: {$request->purpose}\n" . ($request->notes ?: ''),
                'to_location' => 'Field Work',
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
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Show request detail
     */
    public function show(Transaction $transaction)
    {
        // Check if user owns this request
        if ($transaction->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        $transaction->load([
            'item.stock',
            'approvedBy',
            'transactionDetails.itemDetail'
        ]);

        return view('requests.show', compact('transaction'));
    }

    /**
     * Cancel pending request
     */
    public function cancel(Transaction $transaction)
    {
        // Check if user owns this request and it's pending
        if ($transaction->created_by !== auth()->id() || $transaction->status !== Transaction::STATUS_PENDING) {
            abort(403, 'Cannot cancel this request');
        }

        try {
            $transaction->update([
                'status' => Transaction::STATUS_CANCELLED,
                'notes' => $transaction->notes . "\n\nCancelled by user at " . now()->format('Y-m-d H:i:s')
            ]);

            return redirect()->route('requests.index')
                ->with('success', 'Permintaan berhasil dibatalkan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membatalkan permintaan');
        }
    }

    /**
     * Search available items (AJAX) - Enhanced version
     */
    public function searchItems(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'category_id' => 'nullable|exists:categories,category_id'
        ]);

        try {
            $searchQuery = $request->input('query');
            $categoryId = $request->input('category_id');

            $itemsQuery = ItemDetail::with(['item.category', 'item.stock'])
                ->where(function ($q) use ($searchQuery, $categoryId) {
                    // Search by item_detail_id (exact match)
                    $q->where('item_detail_id', $searchQuery)
                        // Search by serial_number
                        ->orWhere('serial_number', 'like', "%{$searchQuery}%")
                        // Search in related item
                        ->orWhereHas('item', function ($itemQ) use ($searchQuery, $categoryId) {
                            $itemQ->where('item_name', 'like', "%{$searchQuery}%")
                                ->orWhere('item_code', 'like', "%{$searchQuery}%");

                            if ($categoryId) {
                                $itemQ->where('category_id', $categoryId);
                            }
                        });
                });

            // Filter by transaction readiness
            $items = $itemsQuery->where('status', '!=', 'lost')
                ->limit(50)
                ->get()
                ->map(function ($itemDetail) {
                    $statusInfo = $itemDetail->getStatusInfo();

                    return [
                        'item_detail_id' => $itemDetail->item_detail_id,
                        'serial_number' => $itemDetail->serial_number,
                        'item_name' => $itemDetail->item->item_name,
                        'item_code' => $itemDetail->item->item_code,
                        'category_name' => $itemDetail->item->category->category_name ?? 'N/A',
                        'location' => $itemDetail->location,
                        'current_status' => $itemDetail->status,
                        'status_text' => $statusInfo['text'],
                        'status_class' => $statusInfo['class'],
                        'stock_available' => $itemDetail->item->stock->quantity_available ?? 0,
                        'stock_total' => $itemDetail->item->stock->total_quantity ?? 0,
                        'transaction_ready' => $itemDetail->isTransactionReady(),
                        'available_transaction_types' => $itemDetail->getAvailableTransactionTypes(),
                        'qr_data' => $itemDetail->generateQRForTransaction()
                    ];
                });

            return response()->json([
                'success' => true,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available items by category (AJAX) - Enhanced version
     */
    public function getItemsByCategory(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,category_id'
        ]);

        try {
            $items = ItemDetail::with(['item.stock'])
                ->whereHas('item', function ($query) use ($request) {
                    $query->where('category_id', $request->category_id);
                })
                ->where('status', '!=', 'lost') // Exclude lost items
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get()
                ->map(function ($itemDetail) {
                    $statusInfo = $itemDetail->getStatusInfo();

                    return [
                        'item_detail_id' => $itemDetail->item_detail_id,
                        'serial_number' => $itemDetail->serial_number,
                        'item_name' => $itemDetail->item->item_name,
                        'item_code' => $itemDetail->item->item_code,
                        'location' => $itemDetail->location,
                        'current_status' => $itemDetail->status,
                        'status_text' => $statusInfo['text'],
                        'status_class' => $statusInfo['class'],
                        'stock_available' => $itemDetail->item->stock->quantity_available ?? 0,
                        'transaction_ready' => $itemDetail->isTransactionReady(),
                        'available_transaction_types' => $itemDetail->getAvailableTransactionTypes(),
                        'qr_data' => $itemDetail->generateQRForTransaction()
                    ];
                });

            return response()->json([
                'success' => true,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available categories for dropdown
     */
    public function getCategories()
    {
        try {
            $categories = \App\Models\Category::where('is_active', true)
                ->whereHas('items.itemDetails', function ($query) {
                    $query->where('status', '!=', 'lost');
                })
                ->orderBy('category_name')
                ->get(['category_id', 'category_name', 'description']);

            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get categories'
            ], 500);
        }
    }

    /**
     * Get item suggestions for autocomplete
     */
    public function getItemSuggestions(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1'
        ]);

        try {
            $query = $request->query;

            // Get unique item names and codes that match
            $suggestions = ItemDetail::with('item')
                ->whereHas('item', function ($q) use ($query) {
                    $q->where('item_name', 'like', "%{$query}%")
                        ->orWhere('item_code', 'like', "%{$query}%");
                })
                ->where('status', '!=', 'lost')
                ->get()
                ->groupBy('item_id')
                ->map(function ($group) {
                    $item = $group->first()->item;
                    return [
                        'item_id' => $item->item_id,
                        'item_name' => $item->item_name,
                        'item_code' => $item->item_code,
                        'available_count' => $group->where('status', 'available')->count(),
                        'total_count' => $group->count()
                    ];
                })
                ->values()
                ->take(10);

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get suggestions'
            ], 500);
        }
    }

    /**
     * Get request history for current user
     */
    public function history(Request $request)
    {
        $query = Transaction::where('created_by', auth()->id())
            ->with(['item', 'approvedBy']);

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

        $requests = $query->orderBy('transaction_date', 'desc')
            ->paginate(20);

        $transactionTypes = [
            Transaction::TYPE_OUT => 'Permintaan Keluar',
            Transaction::TYPE_IN => 'Pengembalian'
        ];

        $transactionStatuses = Transaction::getStatuses();

        return view('requests.history', compact(
            'requests',
            'transactionTypes',
            'transactionStatuses'
        ));
    }

    /**
     * Get user's current items (items currently used by user)
     */
    public function myItems()
    {
        // Get items currently used by this user
        $myItems = \App\Models\TransactionDetail::whereHas('transaction', function ($query) {
            $query->where('created_by', auth()->id())
                ->where('status', Transaction::STATUS_APPROVED)
                ->where('transaction_type', Transaction::TYPE_OUT);
        })
            ->whereHas('itemDetail', function ($query) {
                $query->where('status', 'used');
            })
            ->with([
                'itemDetail.item',
                'transaction'
            ])
            ->get()
            ->map(function ($detail) {
                return [
                    'item_detail_id' => $detail->itemDetail->item_detail_id,
                    'serial_number' => $detail->itemDetail->serial_number,
                    'item_name' => $detail->itemDetail->item->item_name,
                    'item_code' => $detail->itemDetail->item->item_code,
                    'location' => $detail->itemDetail->location,
                    'taken_date' => $detail->transaction->approved_date,
                    'transaction_number' => $detail->transaction->transaction_number,
                    'can_return' => true,
                    'qr_data' => $detail->itemDetail->generateQRForTransaction()
                ];
            });

        return view('requests.my-items', compact('myItems'));
    }

    /**
     * Return item (create return transaction)
     */
    public function returnItem(Request $request)
    {
        $request->validate([
            'item_detail_id' => 'required|exists:item_details,item_detail_id',
            'condition' => 'required|in:good,damaged,repair',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $itemDetail = ItemDetail::with('item')->findOrFail($request->item_detail_id);

            // Validate that user currently has this item
            $hasItem = \App\Models\TransactionDetail::whereHas('transaction', function ($query) {
                $query->where('created_by', auth()->id())
                    ->where('status', Transaction::STATUS_APPROVED)
                    ->where('transaction_type', Transaction::TYPE_OUT);
            })
                ->where('item_detail_id', $request->item_detail_id)
                ->whereHas('itemDetail', function ($query) {
                    $query->where('status', 'used');
                })
                ->exists();

            if (!$hasItem) {
                throw new \Exception('Anda tidak memiliki item ini atau item sudah dikembalikan');
            }

            // Determine transaction type based on condition
            $transactionType = $request->condition === 'good' ? Transaction::TYPE_IN : ($request->condition === 'repair' ? Transaction::TYPE_REPAIR : Transaction::TYPE_IN);

            // Create return transaction
            $transaction = Transaction::create([
                'transaction_type' => $transactionType,
                'reference_type' => 'return',
                'item_id' => $itemDetail->item_id,
                'quantity' => 1,
                'from_location' => 'Field Work',
                'to_location' => $itemDetail->location,
                'notes' => "Item return - Condition: {$request->condition}\n" . ($request->notes ?: ''),
                'status' => Transaction::STATUS_PENDING,
                'created_by' => auth()->id(),
                'transaction_date' => now(),
            ]);

            // Create transaction detail
            \App\Models\TransactionDetail::create([
                'transaction_id' => $transaction->transaction_id,
                'item_detail_id' => $itemDetail->item_detail_id,
                'status_before' => $itemDetail->status,
                'status_after' => null,
                'notes' => "Return condition: {$request->condition}",
            ]);

            DB::commit();

            return redirect()->route('requests.my-items')
                ->with('success', "Pengembalian {$transaction->transaction_number} berhasil dibuat dan menunggu approval");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Gagal membuat pengembalian: ' . $e->getMessage());
        }
    }
}
