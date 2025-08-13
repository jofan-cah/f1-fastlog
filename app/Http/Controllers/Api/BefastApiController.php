<?php

// ================================================================
// FIXED BefastApiController - Sesuai Model Item
// ================================================================

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ItemDetail;
use App\Models\Category;
use App\Models\Item;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BefastApiController extends Controller
{
    /**
     * API 1: Get Modem Items (Fixed - sesuai model Item)
     *
     * GET /api/befast/items
     */
    public function getItems(Request $request)
    {
        try {
            // ✅ FIXED: Filter categories yang mengandung kata "modem"
            $modemCategories = Category::where('is_active', true)
                ->where(function($q) {
                    $q->where('category_name', 'like', '%modem%')
                      ->orWhere('category_name', 'like', '%adsl%')
                      ->orWhere('category_name', 'like', '%vdsl%')
                      ->orWhere('category_name', 'like', '%router%')
                      ->orWhere('category_name', 'like', '%olt%')
                      ->orWhere('category_name', 'like', '%onu%')
                      ->orWhere('category_name', 'like', '%ont%');
                })
                ->pluck('category_id')
                ->toArray();

            Log::info('Modem categories found', [
                'category_ids' => $modemCategories,
                'total_categories' => count($modemCategories)
            ]);

            // ✅ FIXED: Query items yang ada di modem categories dan punya stock available
            $items = Item::with(['category', 'stock'])
                ->whereIn('category_id', $modemCategories)
                ->where('is_active', true)
                ->whereHas('itemDetails', function($q) {
                    $q->where('status', 'available');
                })
                ->get()
                ->map(function($item) {
                    // Hitung available count per item
                    $availableCount = $item->itemDetails()
                        ->where('status', 'available')
                        ->count();

                    return [
                        'item_id' => $item->item_id,
                        'item_code' => $item->item_code,
                        'item_name' => $item->item_name,
                        'category_id' => $item->category_id,
                        'category_name' => $item->category->category_name ?? 'Unknown',
                        'unit' => $item->unit,
                        'description' => $item->description,
                        'available_count' => $availableCount,
                        'min_stock' => $item->min_stock,
                        'stock_info' => $item->getStockInfo(), // Method dari model
                        'qr_code' => $item->qr_code
                    ];
                })
                ->filter(function($item) {
                    return $item['available_count'] > 0; // Only yang ada stock
                })
                ->values();

            // Optional search
            if ($request->has('search')) {
                $search = strtolower($request->search);
                $items = $items->filter(function($item) use ($search) {
                    return str_contains(strtolower($item['item_name']), $search) ||
                           str_contains(strtolower($item['item_code']), $search) ||
                           str_contains(strtolower($item['description'] ?? ''), $search);
                })->values();
            }

            Log::info('Befast API - Items requested', [
                'total_modem_categories' => count($modemCategories),
                'total_items_found' => $items->count(),
                'search' => $request->search,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Modem items retrieved successfully',
                'data' => [
                    'items' => $items,
                    'total_count' => $items->count(),
                    'total_available_stock' => $items->sum('available_count'),
                    'modem_categories_included' => $modemCategories
                ],
                'meta' => [
                    'category_filter' => 'modem_only',
                    'search_applied' => $request->search ? true : false,
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Befast API - Get items failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get modem items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API 2: Get Serial Numbers by Item ID (Fixed)
     *
     * GET /api/befast/serials/{item_id}
     */
    public function getSerials(Request $request, $itemId)
    {
        try {
            $item = Item::with(['category', 'stock'])->find($itemId);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found',
                    'error_code' => 'ITEM_NOT_FOUND'
                ], 404);
            }

            // ✅ FIXED: Verify it's a modem item berdasarkan category
            $modemKeywords = ['modem', 'adsl', 'vdsl', 'router', 'olt', 'onu', 'ont'];
            $isModemItem = false;

            if ($item->category) {
                foreach ($modemKeywords as $keyword) {
                    if (stripos($item->category->category_name, $keyword) !== false) {
                        $isModemItem = true;
                        break;
                    }
                }
            }

            if (!$isModemItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item is not a modem type',
                    'error_code' => 'NOT_MODEM_ITEM',
                    'item_category' => $item->category->category_name ?? 'Unknown'
                ], 400);
            }

            $query = ItemDetail::where('item_id', $itemId)
                             ->where('status', 'available'); // Only available

            // Optional search by serial number
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('serial_number', 'like', "%{$search}%");
            }

            // Optional location filter
            if ($request->has('location')) {
                $query->where('location', 'like', "%{$request->location}%");
            }

            $serials = $query->orderBy('created_at', 'desc')
                           ->get()
                           ->map(function($itemDetail) {
                               return [
                                   'item_detail_id' => $itemDetail->item_detail_id,
                                   'serial_number' => $itemDetail->serial_number,
                                   'location' => $itemDetail->location,
                                   'status' => $itemDetail->status,
                                   'kondisi' => $itemDetail->kondisi ?? 'good',
                                   'notes' => $itemDetail->notes,
                                   'custom_attributes' => $itemDetail->custom_attributes,
                                   'qr_code' => $itemDetail->qr_code,
                                   'created_at' => $itemDetail->created_at->format('Y-m-d H:i:s'),
                                   'status_info' => $itemDetail->getStatusInfo() // Method dari model
                               ];
                           });

            Log::info('Befast API - Serials requested', [
                'item_id' => $itemId,
                'item_name' => $item->item_name,
                'item_category' => $item->category->category_name ?? 'Unknown',
                'total_serials' => $serials->count(),
                'search' => $request->search,
                'location' => $request->location,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Serial numbers retrieved successfully',
                'data' => [
                    'item' => [
                        'item_id' => $item->item_id,
                        'item_code' => $item->item_code,
                        'item_name' => $item->item_name,
                        'category_id' => $item->category_id,
                        'category_name' => $item->category->category_name ?? 'Unknown',
                        'unit' => $item->unit,
                        'description' => $item->description,
                        'stock_info' => $item->getStockInfo()
                    ],
                    'serials' => $serials,
                    'total_count' => $serials->count()
                ],
                'meta' => [
                    'filters_applied' => [
                        'search' => $request->search,
                        'location' => $request->location,
                        'status' => 'available'
                    ],
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Befast API - Get serials failed', [
                'item_id' => $itemId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get serial numbers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API 3: Book Serial Number (Fixed - enhanced logging)
     *
     * POST /api/befast/book-serial
     */
    public function bookSerial(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string',
            'booked_by' => 'required|string|max:255',
            'booking_notes' => 'nullable|string|max:500',
            'customer_info' => 'nullable|string|max:255'
        ]);

        try {
            // Find modem by serial number
            $modem = ItemDetail::where('serial_number', $request->serial_number)
                             ->with(['item.category'])
                             ->first();

            if (!$modem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Serial number not found',
                    'error_code' => 'SERIAL_NOT_FOUND'
                ], 404);
            }

            // ✅ FIXED: Verify it's a modem berdasarkan category
            $modemKeywords = ['modem', 'adsl', 'vdsl', 'router', 'olt', 'onu', 'ont'];
            $isModem = false;

            if ($modem->item && $modem->item->category) {
                foreach ($modemKeywords as $keyword) {
                    if (stripos($modem->item->category->category_name, $keyword) !== false) {
                        $isModem = true;
                        break;
                    }
                }
            }

            if (!$isModem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Serial number is not a modem',
                    'error_code' => 'NOT_MODEM_SERIAL',
                    'item_category' => $modem->item->category->category_name ?? 'Unknown'
                ], 400);
            }

            // Check if available
            if ($modem->status !== 'available') {
                return response()->json([
                    'success' => false,
                    'message' => 'Modem is not available for booking',
                    'error_code' => 'MODEM_NOT_AVAILABLE',
                    'current_status' => $modem->status
                ], 400);
            }

            // Update status to reserved
            $oldStatus = $modem->status;

            // Enhanced booking notes
            $bookingNotes = "🔒 BOOKED via Befast by: {$request->booked_by}";
            if ($request->customer_info) {
                $bookingNotes .= " | Customer: {$request->customer_info}";
            }
            if ($request->booking_notes) {
                $bookingNotes .= " | Notes: {$request->booking_notes}";
            }

            // Update custom attributes untuk tracking
            $attributes = $modem->custom_attributes ?? [];
            $attributes['befast_booking'] = [
                'booked_by' => $request->booked_by,
                'booking_date' => now()->toDateString(),
                'booking_time' => now()->toTimeString(),
                'customer_info' => $request->customer_info,
                'booking_notes' => $request->booking_notes,
                'api_source' => 'befast'
            ];

            // Update modem
            $modem->update([
                'status' => 'reserved',
                'notes' => $bookingNotes,
                'custom_attributes' => $attributes
            ]);

            // Enhanced ActivityLog
            ActivityLog::logActivity(
                'item_details',
                $modem->item_detail_id,
                'befast_booking',
                ['status' => $oldStatus],
                [
                    'status' => 'reserved',
                    'booked_by' => $request->booked_by,
                    'customer_info' => $request->customer_info,
                    'booking_notes' => $request->booking_notes,
                    'api_source' => 'befast',
                    'item_info' => [
                        'item_id' => $modem->item->item_id,
                        'item_code' => $modem->item->item_code,
                        'item_name' => $modem->item->item_name,
                        'category' => $modem->item->category->category_name ?? 'Unknown'
                    ],
                    'booking_timestamp' => now()->toISOString()
                ]
            );

            Log::info('Befast API - Modem booked successfully', [
                'serial_number' => $request->serial_number,
                'item_detail_id' => $modem->item_detail_id,
                'item_info' => [
                    'item_id' => $modem->item->item_id,
                    'item_code' => $modem->item->item_code,
                    'item_name' => $modem->item->item_name,
                    'category' => $modem->item->category->category_name ?? 'Unknown'
                ],
                'booking_info' => [
                    'booked_by' => $request->booked_by,
                    'customer_info' => $request->customer_info,
                    'booking_notes' => $request->booking_notes
                ],
                'status_change' => [
                    'from' => $oldStatus,
                    'to' => 'reserved'
                ],
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Modem successfully booked! 🎉',
                'data' => [
                    'item_detail_id' => $modem->item_detail_id,
                    'serial_number' => $modem->serial_number,
                    'item_info' => [
                        'item_id' => $modem->item->item_id,
                        'item_code' => $modem->item->item_code,
                        'item_name' => $modem->item->item_name,
                        'category_id' => $modem->item->category_id,
                        'category_name' => $modem->item->category->category_name ?? 'Unknown',
                        'unit' => $modem->item->unit,
                        'description' => $modem->item->description
                    ],
                    'status_change' => [
                        'old_status' => $oldStatus,
                        'new_status' => 'reserved'
                    ],
                    'booking_info' => [
                        'booked_by' => $request->booked_by,
                        'booking_date' => now()->toDateString(),
                        'booking_time' => now()->toTimeString(),
                        'customer_info' => $request->customer_info,
                        'notes' => $request->booking_notes
                    ]
                ],
                'meta' => [
                    'api_source' => 'befast',
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Befast API - Book serial failed', [
                'serial_number' => $request->serial_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to book modem',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
