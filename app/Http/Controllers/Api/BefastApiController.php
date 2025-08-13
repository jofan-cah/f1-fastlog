<?php

// ================================================================
// UPDATE BefastApiController - Modem Focused
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
     * API 1: Get Modem Items (Skip categories, default Modem)
     *
     * GET /api/befast/items
     */
    public function getItems(Request $request)
    {
        try {
            // Hardcode filter untuk modem categories
            $modemKeywords = ['modem', 'adsl', 'vdsl', 'router', 'olt', 'onu', 'ont'];

            $items = Item::with('category')
                ->whereHas('category', function($q) use ($modemKeywords) {
                    $q->where('is_active', true);
                    foreach ($modemKeywords as $keyword) {
                        $q->orWhere('category_name', 'like', "%{$keyword}%");
                    }
                })
                ->whereHas('itemDetails', function($q) {
                    $q->where('status', 'available'); // Only items yang punya available stock
                })
                ->get()
                ->map(function($item) {
                    // Hitung available count per item
                    $availableCount = $item->itemDetails()
                        ->where('status', 'available')
                        ->count();

                    return [
                        'item_id' => $item->item_id,
                        'item_name' => $item->item_name,
                        'item_code' => $item->item_code,
                        'brand' => $item->brand ?? 'Unknown',
                        'model' => $item->model ?? 'Unknown',
                        'category_name' => $item->category->category_name,
                        'available_count' => $availableCount,
                        'unit' => $item->unit,
                        'description' => $item->description
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
                           str_contains(strtolower($item['brand']), $search);
                })->values();
            }

            Log::info('Befast API - Items requested', [
                'total_items' => $items->count(),
                'search' => $request->search,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Modem items retrieved successfully',
                'data' => [
                    'items' => $items,
                    'total_count' => $items->count(),
                    'total_available_stock' => $items->sum('available_count')
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
     * API 2: Get Serial Numbers by Item ID
     *
     * GET /api/befast/serials/{item_id}
     */
    public function getSerials(Request $request, $itemId)
    {
        try {
            $item = Item::with('category')->find($itemId);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found',
                    'error_code' => 'ITEM_NOT_FOUND'
                ], 404);
            }

            // Verify it's a modem item
            $modemKeywords = ['modem', 'adsl', 'vdsl', 'router', 'olt', 'onu', 'ont'];
            $isModemItem = false;

            foreach ($modemKeywords as $keyword) {
                if (stripos($item->category->category_name, $keyword) !== false) {
                    $isModemItem = true;
                    break;
                }
            }

            if (!$isModemItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item is not a modem type',
                    'error_code' => 'NOT_MODEM_ITEM'
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
                                   'notes' => $itemDetail->notes,
                                   'custom_attributes' => $itemDetail->custom_attributes,
                                   'created_at' => $itemDetail->created_at->format('Y-m-d H:i:s')
                               ];
                           });

            Log::info('Befast API - Serials requested', [
                'item_id' => $itemId,
                'item_name' => $item->item_name,
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
                        'item_name' => $item->item_name,
                        'item_code' => $item->item_code,
                        'brand' => $item->brand ?? 'Unknown',
                        'model' => $item->model ?? 'Unknown',
                        'category_name' => $item->category->category_name
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
            Log::error('Befast API - Get serials failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get serial numbers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API 3: Book Serial Number (sama seperti sebelumnya)
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

            // Verify it's a modem
            $modemKeywords = ['modem', 'adsl', 'vdsl', 'router', 'olt', 'onu', 'ont'];
            $isModem = false;

            foreach ($modemKeywords as $keyword) {
                if (stripos($modem->item->category->category_name, $keyword) !== false) {
                    $isModem = true;
                    break;
                }
            }

            if (!$isModem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Serial number is not a modem',
                    'error_code' => 'NOT_MODEM_SERIAL'
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
                'booking_notes' => $request->booking_notes
            ];

            // Update modem
            $modem->update([
                'status' => 'reserved',
                'notes' => $bookingNotes,
                'custom_attributes' => $attributes
            ]);

            // Log activity
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
                        'item_name' => $modem->item->item_name,
                        'item_code' => $modem->item->item_code,
                        'category' => $modem->item->category->category_name
                    ],
                    'booking_timestamp' => now()->toISOString()
                ]
            );

            Log::info('Befast API - Modem booked successfully', [
                'serial_number' => $request->serial_number,
                'item_detail_id' => $modem->item_detail_id,
                'item_name' => $modem->item->item_name,
                'booked_by' => $request->booked_by,
                'customer_info' => $request->customer_info,
                'old_status' => $oldStatus,
                'new_status' => 'reserved',
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
                        'item_name' => $modem->item->item_name,
                        'item_code' => $modem->item->item_code,
                        'brand' => $modem->item->brand ?? 'Unknown',
                        'category' => $modem->item->category->category_name
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
