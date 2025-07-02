<?php

namespace App\Http\Controllers;

use App\Models\PoDetail;
use App\Models\PurchaseOrder;
use App\Models\Item;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PoDetailController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    // Add item to PO
    public function addItem(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeEdited()) {
            return response()->json([
                'success' => false,
                'message' => 'PO tidak dapat diedit.'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'item_id' => 'required|string|exists:items,item_id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if item already exists in PO
            $existingDetail = $purchaseOrder->poDetails()
                ->where('item_id', $request->item_id)
                ->first();

            if ($existingDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item sudah ada dalam PO ini.'
                ], 400);
            }

            // Create new detail
            $detailId = PoDetail::generateDetailId();
            $totalPrice = $request->quantity * $request->unit_price;

            $detail = PoDetail::create([
                'po_detail_id' => $detailId,
                'po_id' => $purchaseOrder->po_id,
                'item_id' => $request->item_id,
                'quantity_ordered' => $request->quantity,
                'unit_price' => $request->unit_price,
                'total_price' => $totalPrice,
                'quantity_received' => 0,
                'notes' => $request->notes,
            ]);

            // Update PO total amount
            $purchaseOrder->updateTotalAmount();

            // Load item for response
            $detail->load('item');

            // Log activity
            ActivityLog::logActivity('po_details', $detail->po_detail_id, 'create', null, $detail->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil ditambahkan ke PO.',
                'detail' => $detail,
                'new_total' => $purchaseOrder->fresh()->total_amount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan item: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update PO detail
    public function update(Request $request, PoDetail $poDetail)
    {
        if (!$poDetail->purchaseOrder->canBeEdited()) {
            return response()->json([
                'success' => false,
                'message' => 'PO tidak dapat diedit.'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $oldData = $poDetail->toArray();

            $poDetail->update([
                'quantity_ordered' => $request->quantity,
                'unit_price' => $request->unit_price,
                'total_price' => $request->quantity * $request->unit_price,
                'notes' => $request->notes,
            ]);

            // Update PO total amount
            $poDetail->purchaseOrder->updateTotalAmount();

            // Log activity
            ActivityLog::logActivity('po_details', $poDetail->po_detail_id, 'update', $oldData, $poDetail->fresh()->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Detail PO berhasil diupdate.',
                'detail' => $poDetail->fresh(),
                'new_total' => $poDetail->purchaseOrder->fresh()->total_amount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate detail: ' . $e->getMessage()
            ], 500);
        }
    }

    // Remove item from PO
    public function destroy(PoDetail $poDetail)
    {
        if (!$poDetail->purchaseOrder->canBeEdited()) {
            return response()->json([
                'success' => false,
                'message' => 'PO tidak dapat diedit.'
            ], 400);
        }

        try {
            $purchaseOrder = $poDetail->purchaseOrder;
            $oldData = $poDetail->toArray();

            $poDetail->delete();

            // Update PO total amount
            $purchaseOrder->updateTotalAmount();

            // Log activity
            ActivityLog::logActivity('po_details', $poDetail->po_detail_id, 'delete', $oldData, null);

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus dari PO.',
                'new_total' => $purchaseOrder->fresh()->total_amount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus item: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get PO details
    public function getDetails(PurchaseOrder $purchaseOrder)
    {
        $details = $purchaseOrder->poDetails()
            ->with('item.category')
            ->orderBy('created_at')
            ->get()
            ->map(function($detail) {
                return [
                    'po_detail_id' => $detail->po_detail_id,
                    'item_id' => $detail->item_id,
                    'item_code' => $detail->item->item_code,
                    'item_name' => $detail->item->item_name,
                    'category_name' => $detail->item->category->category_name,
                    'unit' => $detail->item->unit,
                    'quantity_ordered' => $detail->quantity_ordered,
                    'quantity_received' => $detail->quantity_received,
                    'remaining_quantity' => $detail->getRemainingQuantity(),
                    'unit_price' => $detail->unit_price,
                    'total_price' => $detail->total_price,
                    'completion_percentage' => $detail->getCompletionPercentage(),
                    'status_info' => $detail->getStatusInfo(),
                    'notes' => $detail->notes,
                ];
            });

        return response()->json([
            'success' => true,
            'details' => $details,
            'summary' => $purchaseOrder->getSummaryInfo()
        ]);
    }
}
