<?php

namespace App\Http\Controllers;

use App\Models\ItemDetail;
use App\Models\Item;
use App\Models\ActivityLog;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\FacadesLog;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class ItemDetailController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    // Tampilkan daftar item details
    //misal ini index saya nambahin per page
    public function index(Request $request)
    {
        // Enhanced query with purchaseOrder relationship for PO search
        $query = ItemDetail::with([
            'item.category',
            'goodsReceivedDetail.goodsReceived.purchaseOrder'
        ]);

        // Filter by item
        if ($request->filled('item_id')) {
            $query->byItem($request->item_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->byLocation($request->location);
        }

        // Filter by kondisi (NEW)
        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }

        // Enhanced search - includes PO number search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('qr_code', 'like', "%{$search}%")
                    ->orWhere('item_detail_id', 'like', "%{$search}%")
                    // Search in item details
                    ->orWhereHas('item', function ($q2) use ($search) {
                        $q2->where('item_name', 'like', "%{$search}%")
                            ->orWhere('item_code', 'like', "%{$search}%");
                    })
                    // NEW: Search in PO number
                    ->orWhereHas('goodsReceivedDetail.goodsReceived.purchaseOrder', function ($q3) use ($search) {
                        $q3->where('po_number', 'like', "%{$search}%");
                    });
            });
        }

        // NEW: Dedicated PO search filter
        if ($request->filled('po_search')) {
            $poSearch = $request->po_search;
            $query->whereHas('goodsReceivedDetail.goodsReceived.purchaseOrder', function ($q) use ($poSearch) {
                $q->where('po_number', 'like', "%{$poSearch}%");
            });
        }

        // Custom pagination
        $perPage = $request->input('per_page', 25);
        $allowedPerPage = [25, 50, 100, 1000];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 25;
        }
        $totalByStatus = ItemDetail::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $itemDetails = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $itemDetails->appends($request->query());

        // Filter options
        $items = Item::active()->orderBy('item_name')->get();
        $statuses = ['available', 'used', 'damaged', 'maintenance', 'reserved', 'stock'];
        $locations = ItemDetail::distinct('location')->pluck('location')->filter();

        // NEW: Get kondisi options
        $kondisiOptions = ['good', 'no_good'];

        // NEW: Get available PO numbers for dropdown
        $poNumbers = ItemDetail::with('goodsReceivedDetail.goodsReceived.purchaseOrder')
            ->whereHas('goodsReceivedDetail.goodsReceived.purchaseOrder')
            ->get()
            ->map(function ($itemDetail) {
                return $itemDetail->goodsReceivedDetail->goodsReceived->purchaseOrder->po_number ?? null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $perPageOptions = [25, 50, 100, 1000];

        return view('item-details.index', compact(
            'itemDetails',
            'items',
            'statuses',
            'locations',
            'kondisiOptions',
            'poNumbers',
            'perPageOptions',
            'perPage',
            'totalByStatus'
        ));
    }
    // Tampilkan detail item
    public function show(ItemDetail $itemDetail)
    {
        $itemDetail->load(['item.category', 'goodsReceivedDetail.goodsReceived.purchaseOrder']);

        $statusInfo = $itemDetail->getStatusInfo();
        $formattedAttributes = $itemDetail->getFormattedAttributes();
        $usageHistory = $itemDetail->getUsageHistory();

        return view('item-details.show', compact('itemDetail', 'statusInfo', 'formattedAttributes', 'usageHistory'));
    }
    // **NEW: Show edit form**
    public function edit(ItemDetail $itemDetail)
    {
        $itemDetail->load(['item.category', 'goodsReceivedDetail.goodsReceived']);

        // Get available locations from existing data
        $locations = ItemDetail::distinct('location')
            ->whereNotNull('location')
            ->pluck('location')
            ->filter()
            ->sort()
            ->values()
            ->toArray(); // Convert to array to avoid count() issues

        // Get status options
        $statuses = [
            'available' => 'Tersedia',
            'used' => 'Terpakai',
            'damaged' => 'Rusak',
            'maintenance' => 'Maintenance',
            'reserved' => 'Reserved'
        ];

        // Get attribute templates based on item category
        $attributeTemplates = $this->getAttributeTemplatesForEdit($itemDetail->item);

        return view('item-details.edit', compact(
            'itemDetail',
            'locations',
            'statuses',
            'attributeTemplates'
        ));
    }


    // **UPDATED: Update item detail with auto QR generation (SVG format)**
    public function update(Request $request, ItemDetail $itemDetail)
    {
        $validator = Validator::make($request->all(), [
            'serial_number' => 'required|string|max:100|unique:item_details,serial_number,' . $itemDetail->item_detail_id . ',item_detail_id',
            'status' => 'required|in:available,used,damaged,maintenance,reserved',
            'location' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'custom_attributes' => 'nullable|array',
            'custom_attributes.*' => 'nullable|string|max:255',
        ], [
            'serial_number.required' => 'Serial number wajib diisi.',
            'serial_number.unique' => 'Serial number sudah digunakan.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $oldData = $itemDetail->toArray();

            // Clean up custom attributes - remove empty values
            $customAttributes = null;
            if ($request->filled('custom_attributes')) {
                $customAttributes = array_filter($request->custom_attributes, function ($value) {
                    return !is_null($value) && $value !== '';
                });

                // Only set if there are actual values
                $customAttributes = !empty($customAttributes) ? $customAttributes : null;
            }

            // Prepare update data
            $updateData = [
                'serial_number' => $request->serial_number,
                'status' => $request->status,
                'location' => $request->location,
                'notes' => $request->notes,
                'custom_attributes' => $customAttributes,
            ];

            // **Auto-generate QR code if not exists**
            $qrGenerated = false;
            if (empty($itemDetail->qr_code)) {
                $qrCode = $this->generateQRCodeForItem($itemDetail);
                if ($qrCode) {
                    $updateData['qr_code'] = $qrCode . '.svg';
                    $qrGenerated = true;
                }
            }

            // Update item detail
            $itemDetail->update($updateData);

            // Log activity
            ActivityLog::logActivity('item_details', $itemDetail->item_detail_id, 'update', $oldData, [
                'serial_number' => $request->serial_number,
                'status' => $request->status,
                'location' => $request->location,
                'custom_attributes_updated' => !is_null($customAttributes),
                'qr_code_generated' => $qrGenerated,
                'notes' => $request->notes
            ]);

            $successMessage = 'Item detail berhasil diupdate!';
            if ($qrGenerated) {
                $successMessage .= ' QR Code telah digenerate otomatis.';
            }

            return redirect()->route('item-details.show', $itemDetail)
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate item detail: ' . $e->getMessage());
        }
    }

    /**
     * Generate QR Code for Item Detail (SVG format, no ImageMagick needed)
     */
    private function generateQRCodeForItem(ItemDetail $itemDetail): ?string
    {
        try {
            // Load item relationship if not loaded
            if (!$itemDetail->relationLoaded('item')) {
                $itemDetail->load('item');
            }

            // Generate simple QR code string
            $qrCodeString = 'AA-' . $itemDetail->item_detail_id . '-' . time();

            // Create QR content with API endpoint for future transactions
            $qrContent = json_encode([
                'type' => 'item_detail',
                'item_detail_id' => $itemDetail->item_detail_id,
                'serial_number' => $itemDetail->serial_number,
                'item_id' => $itemDetail->item_id,
                'item_code' => $itemDetail->item->item_code,
                'item_name' => $itemDetail->item->item_name,
                // 'status' => $itemDetail->status,
                // 'generated_at' => now()->toISOString(),
                // 'api_url' => route('api.item-details.scan', $itemDetail->item_detail_id),
                // 'view_url' => route('item-details.show', $itemDetail->item_detail_id),
                // 'transaction_ready' => true
            ]);

            // Generate and save QR code image using SVG format
            $imageSaved = $this->saveQRCodeImageSVG($qrCodeString, $qrContent, $itemDetail);

            if (!$imageSaved) {
                Log::warning('QR Code image failed to save, but string generated', [
                    'item_detail_id' => $itemDetail->item_detail_id,
                    'qr_code' => $qrCodeString
                ]);
            }

            return $qrCodeString;
        } catch (\Exception $e) {
            Log::error('Failed to generate QR code for item detail: ' . $e->getMessage(), [
                'item_detail_id' => $itemDetail->item_detail_id,
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }



    /**
     * API Endpoint: Scan QR Code for transactions (JSON response)
     */
    public function apiScanQR(Request $request, $itemDetailId = null)
    {
        try {
            // Get QR code from request or use item detail ID
            $qrCode = $request->get('qr_code');
            $itemDetail = null;

            if ($itemDetailId) {
                // Direct scan by item detail ID
                $itemDetail = ItemDetail::with(['item.category', 'goodsReceivedDetail.goodsReceived'])
                    ->find($itemDetailId);
            } elseif ($qrCode) {
                // Scan by QR code string
                $itemDetail = ItemDetail::with(['item.category', 'goodsReceivedDetail.goodsReceived'])
                    ->where('qr_code', $qrCode)
                    ->first();
            }

            if (!$itemDetail) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item tidak ditemukan',
                    'error_code' => 'ITEM_NOT_FOUND'
                ], 404);
            }

            // Check if item is available for transaction
            $transactionReady = $this->checkTransactionAvailability($itemDetail);

            return response()->json([
                'success' => true,
                'data' => [
                    'item_detail' => [
                        'item_detail_id' => $itemDetail->item_detail_id,
                        'serial_number' => $itemDetail->serial_number,
                        'qr_code' => $itemDetail->qr_code,
                        'status' => $itemDetail->status,
                        'location' => $itemDetail->location,
                        'notes' => $itemDetail->notes,
                        'custom_attributes' => $itemDetail->custom_attributes,
                    ],
                    'item' => [
                        'item_id' => $itemDetail->item->item_id,
                        'item_code' => $itemDetail->item->item_code,
                        'item_name' => $itemDetail->item->item_name,
                        'category' => $itemDetail->item->category->category_name ?? null,
                        'unit' => $itemDetail->item->unit,
                    ],
                    'goods_received' => [
                        'gr_id' => $itemDetail->goodsReceivedDetail->goodsReceived->gr_id ?? null,
                        'receive_number' => $itemDetail->goodsReceivedDetail->goodsReceived->receive_number ?? null,
                        'receive_date' => $itemDetail->goodsReceivedDetail->goodsReceived->receive_date ?? null,
                    ],
                    'transaction_info' => $transactionReady,
                    'status_info' => $itemDetail->getStatusInfo(),
                    'scanned_at' => now()->toISOString()
                ],
                'meta' => [
                    'scan_type' => $qrCode ? 'qr_code' : 'direct_id',
                    'transaction_ready' => $transactionReady['can_transact'],
                    'requires_confirmation' => $transactionReady['requires_confirmation'],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage(),
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Check if item is available for transaction
     */
    private function checkTransactionAvailability(ItemDetail $itemDetail): array
    {
        $canTransact = false;
        $message = '';
        $requiresConfirmation = false;
        $allowedActions = [];

        switch ($itemDetail->status) {
            case 'available':
                $canTransact = true;
                $message = 'Item tersedia untuk transaksi';
                $allowedActions = ['checkout', 'deploy', 'maintenance', 'reserve'];
                break;

            case 'used':
                $canTransact = true;
                $requiresConfirmation = true;
                $message = 'Item sedang digunakan, perlu konfirmasi untuk transaksi';
                $allowedActions = ['return', 'maintenance', 'damage_report'];
                break;

            case 'maintenance':
                $canTransact = true;
                $requiresConfirmation = true;
                $message = 'Item dalam maintenance, aksi terbatas';
                $allowedActions = ['complete_maintenance', 'damage_report'];
                break;

            case 'reserved':
                $canTransact = false;
                $message = 'Item sedang direserve, tidak dapat ditransaksikan';
                $allowedActions = ['unreserve'];
                break;

            case 'damaged':
                $canTransact = false;
                $message = 'Item rusak, tidak dapat ditransaksikan';
                $allowedActions = ['repair', 'dispose'];
                break;

            default:
                $canTransact = false;
                $message = 'Status item tidak dikenali';
                $allowedActions = [];
        }

        return [
            'can_transact' => $canTransact,
            'message' => $message,
            'requires_confirmation' => $requiresConfirmation,
            'allowed_actions' => $allowedActions,
            'current_status' => $itemDetail->status,
            'location' => $itemDetail->location,
        ];
    }

    /**
     * API Endpoint: Update item status via QR transaction
     */
    public function apiUpdateStatus(Request $request, ItemDetail $itemDetail)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|string|in:checkout,return,deploy,maintenance,complete_maintenance,reserve,unreserve,damage_report,repair,dispose',
            'location' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'user_id' => 'required|string', // ID user yang melakukan transaksi
            'confirmation' => 'boolean', // Untuk aksi yang perlu konfirmasi
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $action = $request->action;
            $newStatus = $this->mapActionToStatus($action);
            $transactionInfo = $this->checkTransactionAvailability($itemDetail);

            // Check if transaction is allowed
            if (!$transactionInfo['can_transact']) {
                return response()->json([
                    'success' => false,
                    'error' => $transactionInfo['message'],
                    'error_code' => 'TRANSACTION_NOT_ALLOWED'
                ], 403);
            }

            // Check if action requires confirmation
            if ($transactionInfo['requires_confirmation'] && !$request->boolean('confirmation')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Aksi ini memerlukan konfirmasi',
                    'error_code' => 'CONFIRMATION_REQUIRED',
                    'requires_confirmation' => true
                ], 409);
            }

            // Check if action is allowed for current status
            if (!in_array($action, $transactionInfo['allowed_actions'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Aksi tidak diizinkan untuk status saat ini',
                    'error_code' => 'ACTION_NOT_ALLOWED',
                    'allowed_actions' => $transactionInfo['allowed_actions']
                ], 403);
            }

            $oldData = $itemDetail->toArray();

            // Update item detail
            $itemDetail->update([
                'status' => $newStatus,
                'location' => $request->location ?? $itemDetail->location,
                'notes' => $request->notes ?? $itemDetail->notes,
            ]);

            // Log transaction activity
            ActivityLog::logActivity('item_details', $itemDetail->item_detail_id, 'qr_transaction', $oldData, [
                'action' => $action,
                'old_status' => $oldData['status'],
                'new_status' => $newStatus,
                'user_id' => $request->user_id,
                'location' => $request->location,
                'notes' => $request->notes,
                'transaction_type' => 'qr_scan'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diproses',
                'data' => [
                    'item_detail_id' => $itemDetail->item_detail_id,
                    'serial_number' => $itemDetail->serial_number,
                    'old_status' => $oldData['status'],
                    'new_status' => $newStatus,
                    'action' => $action,
                    'location' => $itemDetail->location,
                    'processed_at' => now()->toISOString(),
                    'processed_by' => $request->user_id
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Transaction failed: ' . $e->getMessage(),
                'error_code' => 'TRANSACTION_FAILED'
            ], 500);
        }
    }

    /**
     * Map transaction action to item status
     */
    private function mapActionToStatus(string $action): string
    {
        $actionStatusMap = [
            'checkout' => 'used',
            'return' => 'available',
            'deploy' => 'used',
            'maintenance' => 'maintenance',
            'complete_maintenance' => 'available',
            'reserve' => 'reserved',
            'unreserve' => 'available',
            'damage_report' => 'damaged',
            'repair' => 'maintenance',
            'dispose' => 'damaged',
        ];

        return $actionStatusMap[$action] ?? 'available';
    }

    /**
     * Save QR Code as SVG format (no ImageMagick dependency)
     */
    private function saveQRCodeImageSVG(string $qrCodeString, string $qrContent, ItemDetail $itemDetail): bool
    {
        try {
            // Create directory if it doesn't exist
            $directory = 'qr-codes/item-details';
            $fullPath = storage_path('app/public/' . $directory);

            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
                Log::info('Created QR code directory: ' . $fullPath);
            }

            // // Generate QR code as SVG (no ImageMagick needed)
            // $svgContent = QrCode::format('svg')
            //     ->size(300)
            //     ->margin(2)
            //     ->errorCorrection('M')
            //     ->generate($qrContent);

            $svgContent = QrCode::size(500)           // Perbesar ukuran
                ->margin(4)           // Perbesar margin/quiet zone
                ->errorCorrection('L') // Tingkatkan error correction
                ->generate($qrContent);

            // Define storage path with SVG extension
            $fileName = $qrCodeString . '.svg';
            $filePath = $fullPath . '/' . $fileName;

            // Save SVG content directly to file
            $saved = file_put_contents($filePath, $svgContent);

            if ($saved !== false) {
                Log::info('QR Code SVG saved successfully', [
                    'item_detail_id' => $itemDetail->item_detail_id,
                    'file_path' => $directory . '/' . $fileName,
                    'qr_code' => $qrCodeString,
                    'full_path' => $filePath,
                    'file_size' => $saved . ' bytes'
                ]);
                return true;
            } else {
                Log::error('Failed to save QR code SVG to file system', [
                    'item_detail_id' => $itemDetail->item_detail_id,
                    'file_path' => $filePath
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception while saving QR code SVG: ' . $e->getMessage(), [
                'item_detail_id' => $itemDetail->item_detail_id,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Get QR Code image URL (updated for SVG format)
     */
    public function getQRImageUrl(ItemDetail $itemDetail): ?string
    {
        if (empty($itemDetail->qr_code)) {
            return null;
        }

        // Check for SVG file first
        $svgFileName = 'qr-codes/item-details/' . $itemDetail->item_detail_id . '.svg';
        if (Storage::disk('public')->exists($svgFileName)) {
            return Storage::disk('public')->url($svgFileName);
        }

        // Fallback to PNG if exists (backward compatibility)
        $pngFileName = 'qr-codes/item-details/' . $itemDetail->item_detail_id . '.png';
        if (Storage::disk('public')->exists($pngFileName)) {
            return Storage::disk('public')->url($pngFileName);
        }

        return null;
    }

    public function regenerateQRImage(ItemDetail $itemDetail): bool
    {
        if (empty($itemDetail->qr_code)) {
            return false;
        }

        try {
            // Load item if needed
            if (!$itemDetail->relationLoaded('item')) {
                $itemDetail->load('item');
            }

            // Recreate QR content
            $qrContent = json_encode([
                'type' => 'item_detail',
                'item_detail_id' => $itemDetail->item_detail_id,
                'serial_number' => $itemDetail->serial_number,
                'item_id' => $itemDetail->item_id,
                'item_code' => $itemDetail->item->item_code,
                'item_name' => $itemDetail->item->item_name,
                'status' => $itemDetail->status,
                'generated_at' => now()->toISOString(),
                'api_url' => route('api.item-details.scan', $itemDetail->item_detail_id),
                'view_url' => route('item-details.show', $itemDetail->item_detail_id),
                'transaction_ready' => true
            ]);

            return $this->saveQRCodeImage($itemDetail->qr_code, $qrContent, $itemDetail);
        } catch (\Exception $e) {
            Log::error('Failed to regenerate QR image: ' . $e->getMessage(), [
                'item_detail_id' => $itemDetail->item_detail_id
            ]);
            return false;
        }
    }

    /**
     * Manual QR Code generation endpoint (for existing items)
     */
    public function generateQR(ItemDetail $itemDetail)
    {
        try {
            // Check if QR code already exists
            if (!empty($itemDetail->qr_code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code sudah ada untuk item ini',
                    'existing_qr_code' => $itemDetail->qr_code
                ]);
            }

            // Generate new QR code
            $qrCode = $this->generateQRCodeForItem($itemDetail);
            $dataQr = $qrCode . '.svg';
            dd($dataQr);

            if ($qrCode) {
                // Update item detail with new QR code
                $itemDetail->update(['qr_code' => $dataQr]);

                // Log activity
                ActivityLog::logActivity('item_details', $itemDetail->item_detail_id, 'qr_generated', null, [
                    'qr_code' => $dataQr,
                    'generated_manually' => true
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'QR Code berhasil digenerate!',
                    'qr_code' => $qrCode,
                    'qr_image_url' => Storage::disk('public')->url('qr-codes/item-details/' . $itemDetail->item_detail_id . '.png')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal generate QR Code'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate QR Code: ' . $e->getMessage()
            ], 500);
        }
    }

    // Tambahkan method ini di ItemDetailController.php

    /**
     * AJAX endpoint to get attribute templates based on category
     */
    // Tambahkan method ini di ItemDetailController.php
    // Perbaiki method ini di ItemDetailController.php

    /**
     * AJAX endpoint to get attribute templates based on category
     */
    public function getAttributeTemplates($categoryId)
    {
        try {
            // Find category by ID dengan relationship parent
            $category = \App\Models\Category::with('parent')->find($categoryId);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'error' => 'Category not found'
                ], 404);
            }

            // Get parent category name first
            $parentCategoryName = $this->getParentCategoryName($category);

            // Get templates using parent category name
            $templates = $this->getAttributeTemplatesByCategory($parentCategoryName);

            return response()->json([
                'success' => true,
                'templates' => $templates,
                'category_name' => $category->category_name,
                'parent_name' => $parentCategoryName
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get parent category name or current category name if no parent
     */
    private function getParentCategoryName($category): string
    {
        // If category has parent, use parent's name
        if ($category->parent_id && $category->parent) {
            return $category->parent->category_name;
        }

        // Otherwise use current category name
        return $category->category_name;
    }

    /**
     * Get attribute templates by category name (using parent category name)
     */
    private function getAttributeTemplatesByCategory(string $categoryName): array
    {
        $categoryName = strtolower($categoryName);

        switch ($categoryName) {
            case 'adsl/vdsl modem':
            case 'modem':
                return [
                    'firmware_version' => 'Firmware Version',
                    'mac_address' => 'MAC Address',
                    'power_consumption' => 'Power Consumption',
                    'wifi_capability' => 'WiFi Capability',
                    'wifi_password' => 'WiFi Password',
                    'ports' => 'Ports Configuration',
                ];

            case 'router':
                return [
                    'firmware_version' => 'Firmware Version',
                    'mac_address' => 'MAC Address',
                    'power_consumption' => 'Power Consumption',
                    'wifi_capability' => 'WiFi Capability',
                    'wifi_ssid_2g' => 'WiFi SSID 2.4G',
                    'wifi_ssid_5g' => 'WiFi SSID 5G',
                    'ports' => 'Ports Configuration',
                ];

            case 'switch':
                return [
                    'firmware_version' => 'Firmware Version',
                    'mac_address' => 'MAC Address',
                    'power_consumption' => 'Power Consumption',
                    'switching_capacity' => 'Switching Capacity',
                    'ports' => 'Ports Configuration',
                    'auto_negotiation' => 'Auto Negotiation',
                ];

            case 'cable':
            case 'kabel':
                return [
                    'cable_type' => 'Cable Type',
                    'length' => 'Length (meters)',
                    'connector_type' => 'Connector Type',
                    'category_rating' => 'Category Rating',
                    'shielding' => 'Shielding Type',
                    'color' => 'Cable Color',
                ];

            case 'antenna':
                return [
                    'frequency_range' => 'Frequency Range',
                    'gain' => 'Gain (dBi)',
                    'connector_type' => 'Connector Type',
                    'polarization' => 'Polarization',
                    'impedance' => 'Impedance',
                    'mounting_type' => 'Mounting Type',
                ];

            default:
                return [
                    'serial_internal' => 'Internal Serial',
                    'condition' => 'Physical Condition',
                    'warranty_until' => 'Warranty Until',
                    'purchase_date' => 'Purchase Date',
                    'vendor' => 'Vendor/Brand',
                    'model' => 'Model Number',
                ];
        }
    }

    /**
     * Get attribute templates for edit form (return array, not JSON)
     */
    private function getAttributeTemplatesForEdit($item): array
    {
        // Check if item and category exist
        if (!$item || !$item->category) {
            return [];
        }

        // Load parent relationship if not already loaded
        if (!$item->category->relationLoaded('parent')) {
            $item->category->load('parent');
        }

        // Get parent category name for template matching
        $parentCategoryName = $this->getParentCategoryName($item->category);

        // Use existing method to get templates
        return $this->getAttributeTemplatesByCategory($parentCategoryName);
    }

    // Update status item detail
    public function updateStatus(Request $request, ItemDetail $itemDetail)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:available,used,damaged,maintenance,reserved',
            'location' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $oldData = $itemDetail->toArray();

            $itemDetail->update([
                'status' => $request->status,
                'location' => $request->location ?? $itemDetail->location,
                'notes' => $request->notes ?? $itemDetail->notes,
            ]);

            // Log activity
            ActivityLog::logActivity('item_details', $itemDetail->item_detail_id, 'status_update', $oldData, [
                'old_status' => $oldData['status'],
                'new_status' => $request->status,
                'location' => $request->location,
                'notes' => $request->notes
            ]);

            return back()->with('success', 'Status item berhasil diupdate!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengupdate status: ' . $e->getMessage());
        }
    }

    // API endpoint untuk scan QR
    public function scanQR(Request $request)
    {
        $qrCode = $request->get('qr_code');

        $itemDetail = ItemDetail::where('qr_code', $qrCode)
            ->with(['item.category'])
            ->first();

        if (!$itemDetail) {
            return response()->json(['error' => 'Item tidak ditemukan'], 404);
        }

        return response()->json([
            'item_detail' => [
                'item_detail_id' => $itemDetail->item_detail_id,
                'serial_number' => $itemDetail->serial_number,
                'item_code' => $itemDetail->item->item_code,
                'item_name' => $itemDetail->item->item_name,
                'category' => $itemDetail->item->category->category_name,
                'status' => $itemDetail->status,
                'status_info' => $itemDetail->getStatusInfo(),
                'location' => $itemDetail->location,
                'custom_attributes' => $itemDetail->getFormattedAttributes(),
            ]
        ]);
    }

    public function bulkPrintLabels(Request $request)
    {
        $itemDetailIds = $request->input('item_detail_ids', []);

        if (empty($itemDetailIds)) {
            return redirect()->back()->with('error', 'Tidak ada item yang dipilih');
        }

        $itemDetails = ItemDetail::with(['item.category', 'goodsReceivedDetail.goodsReceived.purchaseOrder'])
            ->whereIn('item_detail_id', $itemDetailIds)
            ->get();

        $printConfig = [
            'label_size' => $request->input('label_size', 'sfp'),
            'labels_per_row' => (int) $request->input('labels_per_row', 6),
            'include_item_name' => $request->boolean('include_item_name', true),
            'include_serial' => $request->boolean('include_serial', true),
            'include_po' => $request->boolean('include_po', false),
        ];

        // Define label dimensions
        $labelDimensions = [
            'sfp' => ['width' => '3cm', 'height' => '1.3cm', 'font_size' => '8px'],
            'small' => ['width' => '4cm', 'height' => '2cm', 'font_size' => '10px'],
            'medium' => ['width' => '5cm', 'height' => '3cm', 'font_size' => '12px'],
            'large' => ['width' => '6cm', 'height' => '4cm', 'font_size' => '14px'],
        ];

        $dimensions = $labelDimensions[$printConfig['label_size']];

        return view('item-details.qr-labels-print', compact('itemDetails', 'printConfig', 'dimensions'));
    }

    public function bulkUpdateStatusFromStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'changes' => 'required|array|min:1',
            'changes.*.item_detail_id' => 'required|string|exists:item_details,item_detail_id',
            'changes.*.status' => 'required|in:available,used,damaged,maintenance,reserved,stock',
            'changes.*.location' => 'nullable|string|max:100',
            'changes.*.notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $changes = $request->changes;
            $updatedCount = 0;
            $stocksSynced = [];
            $errors = [];
            $affectedStockIds = []; // Track unique stock IDs

            foreach ($changes as $change) {
                try {
                    $itemDetail = ItemDetail::with('item.stock')->find($change['item_detail_id']);

                    if (!$itemDetail) {
                        $errors[] = [
                            'item_detail_id' => $change['item_detail_id'],
                            'error' => 'Item detail not found'
                        ];
                        continue;
                    }

                    $oldStatus = $itemDetail->status;
                    $newStatus = $change['status'];

                    // Update item detail
                    $itemDetail->update([
                        'status' => $newStatus,
                        'location' => $change['location'] ?? $itemDetail->location,
                        'notes' => $change['notes'] ?? $itemDetail->notes,
                    ]);

                    $updatedCount++;

                    // **Collect affected stock IDs untuk sync batch**
                    $stock = $itemDetail->item->stock;
                    if ($stock && !in_array($stock->stock_id, $affectedStockIds)) {
                        $affectedStockIds[] = $stock->stock_id;
                    }

                    // Log activity
                    ActivityLog::logActivity('item_details', $itemDetail->item_detail_id, 'bulk_status_update_from_stock', [
                        'old_status' => $oldStatus
                    ], [
                        'new_status' => $newStatus,
                        'location' => $change['location'],
                        'notes' => $change['notes'],
                        'bulk_operation' => true,
                        'from_stock_management' => true
                    ]);
                } catch (\Exception $e) {
                    $errors[] = [
                        'item_detail_id' => $change['item_detail_id'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            // **CRITICAL: Sync semua affected stocks setelah semua item details diupdate**
            foreach ($affectedStockIds as $stockId) {
                try {
                    $stock = Stock::find($stockId);
                    if ($stock) {
                        $syncResult = $stock->syncWithItemDetails();
                        if ($syncResult['success']) {
                            $stocksSynced[] = $stockId;

                            // Log stock sync
                            Log::info('Stock auto-synced after bulk item detail update', [
                                'stock_id' => $stockId,
                                'sync_result' => $syncResult['changes'],
                                'triggered_by' => 'bulk_update_status_from_stock'
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to sync stock after bulk update', [
                        'stock_id' => $stockId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil update {$updatedCount} item details. Stock " . count($stocksSynced) . " item telah disinkronkan.",
                'data' => [
                    'updated_count' => $updatedCount,
                    'stocks_synced_count' => count($stocksSynced),
                    'stocks_synced' => $stocksSynced,
                    'affected_stock_ids' => $affectedStockIds,
                    'errors_count' => count($errors),
                    'errors' => $errors,
                    'debug_info' => [
                        'total_changes_requested' => count($changes),
                        'successful_updates' => $updatedCount,
                        'unique_stocks_affected' => count($affectedStockIds),
                        'stocks_successfully_synced' => count($stocksSynced)
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Bulk update status from stock failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal bulk update: ' . $e->getMessage()
            ], 500);
        }
    }
}
