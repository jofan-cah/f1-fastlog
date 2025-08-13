<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ItemDetail;
use App\Models\Category;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BefastApiController extends Controller
{
    /**
     * API 1: Get Categories - Khusus modem
     */
    public function getCategories(Request $request)
    {
        try {
            // Keywords untuk modem-related categories
            $modemKeywords = ['modem', 'adsl', 'vdsl', 'router', 'olt', 'onu', 'ont'];

            $categories = Category::where('is_active', true)
                ->where(function($q) use ($modemKeywords) {
                    foreach ($modemKeywords as $keyword) {
                        $q->orWhere('category_name', 'like', "%{$keyword}%");
                    }
                })
                ->get()
                ->map(function($category) {
                    // Hitung available items
                    $availableCount = ItemDetail::whereHas('item', function($q) use ($category) {
                        $q->where('category_id', $category->category_id);
                    })->where('status', 'available')->count();

                    return [
                        'category_id' => $category->category_id,
                        'category_name' => $category->category_name,
                        'code_category' => $category->code_category,
                        'available_count' => $availableCount
                    ];
                })
                ->filter(function($category) {
                    return $category['available_count'] > 0; // Only yang ada available
                })
                ->values();

            Log::info('Befast API - Categories requested', [
                'total_categories' => $categories->count(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories,
                'meta' => [
                    'total_categories' => $categories->count(),
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Befast API - Get categories failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API 2: Get Serial Numbers by Category
     */
    public function getSerials(Request $request, $categoryId)
    {
        try {
            $category = Category::find($categoryId);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            $query = ItemDetail::with('item')
                ->whereHas('item', function($itemQuery) use ($categoryId) {
                    $itemQuery->where('category_id', $categoryId);
                })
                ->where('status', 'available'); // Only available

            // Optional search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('serial_number', 'like', "%{$search}%");
                });
            }

            $serials = $query->orderBy('created_at', 'desc')
                           ->get()
                           ->map(function($itemDetail) {
                               return [
                                   'item_detail_id' => $itemDetail->item_detail_id,
                                   'serial_number' => $itemDetail->serial_number,
                                   'item_name' => $itemDetail->item->item_name,
                                   'item_code' => $itemDetail->item->item_code,
                                   'location' => $itemDetail->location,
                                   'status' => $itemDetail->status
                               ];
                           });

            Log::info('Befast API - Serials requested', [
                'category_id' => $categoryId,
                'total_serials' => $serials->count(),
                'search' => $request->search,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Serial numbers retrieved successfully',
                'data' => [
                    'category' => [
                        'category_id' => $category->category_id,
                        'category_name' => $category->category_name
                    ],
                    'serials' => $serials,
                    'total_count' => $serials->count()
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
     * API 3: Book Serial Number (Status jadi reserved)
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
                             ->with('item')
                             ->first();

            if (!$modem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Serial number not found',
                    'error_code' => 'SERIAL_NOT_FOUND'
                ], 404);
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

            // Buat notes booking
            $bookingNotes = "🔒 BOOKED via Befast by: {$request->booked_by}";
            if ($request->customer_info) {
                $bookingNotes .= " | Customer: {$request->customer_info}";
            }
            if ($request->booking_notes) {
                $bookingNotes .= " | Notes: {$request->booking_notes}";
            }

            // Update modem
            $modem->update([
                'status' => 'reserved',  // ✅ Status jadi booking
                'notes' => $bookingNotes
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
                    'booking_timestamp' => now()->toISOString()
                ]
            );

            Log::info('Befast API - Serial booked successfully', [
                'serial_number' => $request->serial_number,
                'item_detail_id' => $modem->item_detail_id,
                'booked_by' => $request->booked_by,
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
                    'item_name' => $modem->item->item_name,
                    'old_status' => $oldStatus,
                    'new_status' => 'reserved',
                    'booking_info' => [
                        'booked_by' => $request->booked_by,
                        'booking_date' => now()->toDateString(),
                        'booking_time' => now()->toTimeString(),
                        'customer_info' => $request->customer_info,
                        'notes' => $request->booking_notes
                    ]
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

    /**
     * API 4: Cancel Booking (Bonus)
     */
    public function cancelBooking(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string',
            'cancelled_by' => 'required|string|max:255',
            'cancel_reason' => 'nullable|string|max:500'
        ]);

        try {
            $modem = ItemDetail::where('serial_number', $request->serial_number)->first();

            if (!$modem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Serial number not found'
                ], 404);
            }

            if ($modem->status !== 'reserved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Modem is not in reserved status',
                    'current_status' => $modem->status
                ], 400);
            }

            // Update back to available
            $modem->update([
                'status' => 'available',
                'notes' => "❌ Booking CANCELLED by: {$request->cancelled_by}" .
                          ($request->cancel_reason ? " | Reason: {$request->cancel_reason}" : "")
            ]);

            Log::info('Befast API - Booking cancelled', [
                'serial_number' => $request->serial_number,
                'cancelled_by' => $request->cancelled_by,
                'cancel_reason' => $request->cancel_reason,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully! ✅',
                'data' => [
                    'serial_number' => $modem->serial_number,
                    'old_status' => 'reserved',
                    'new_status' => 'available',
                    'cancelled_by' => $request->cancelled_by,
                    'cancel_reason' => $request->cancel_reason
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Befast API - Cancel booking failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
