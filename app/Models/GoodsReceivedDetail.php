<?php

// ================================================================
// 2. app/Models/GoodsReceivedDetail.php
// ================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class GoodsReceivedDetail extends Model
{
    protected $table = 'goods_received_details';
    protected $primaryKey = 'gr_detail_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'gr_detail_id',
        'gr_id',
        'item_id',
        'quantity_received',
        'quantity_to_stock',
        'quantity_to_ready',
        'unit_price',
        'batch_number',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'quantity_received' => 'integer',
        'quantity_to_stock' => 'integer',
        'quantity_to_ready' => 'integer',
        'unit_price' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    // Relationship: GRDetail belongs to GoodsReceived
    public function goodsReceived(): BelongsTo
    {
        return $this->belongsTo(GoodsReceived::class, 'gr_id', 'gr_id');
    }

    // Relationship: GRDetail belongs to Item
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    // Relationship: GRDetail has many ItemDetails (individual tracking)
    public function itemDetails(): HasMany
    {
        return $this->hasMany(ItemDetail::class, 'gr_detail_id', 'gr_detail_id');
    }

    // Helper method: Calculate total value
    public function getTotalValue(): float
    {
        return $this->quantity_received * $this->unit_price;
    }

    // Helper method: Validate split quantities
    public function validateSplit(): bool
    {
        return ($this->quantity_to_stock + $this->quantity_to_ready) === $this->quantity_received;
    }

    // Helper method: Get split info
    public function getSplitInfo(): array
    {
        $total = $this->quantity_received;

        return [
            'total_received' => $total,
            'to_stock' => $this->quantity_to_stock,
            'to_ready' => $this->quantity_to_ready,
            'stock_percentage' => $total > 0 ? round(($this->quantity_to_stock / $total) * 100, 1) : 0,
            'ready_percentage' => $total > 0 ? round(($this->quantity_to_ready / $total) * 100, 1) : 0,
        ];
    }

    // Helper method: Update corresponding PO detail
    public function updatePODetail(): void
    {
        $poDetail = $this->getPODetail();

        if ($poDetail) {
            // Calculate total received for this item across all GR
            $totalReceived = GoodsReceivedDetail::where('item_id', $this->item_id)
                ->whereHas('goodsReceived', function($q) use ($poDetail) {
                    $q->where('po_id', $poDetail->po_id);
                })
                ->sum('quantity_received');

            $poDetail->update(['quantity_received' => $totalReceived]);
        }
    }

    // Helper method: Get corresponding PO detail
    public function getPODetail()
    {
        return PoDetail::where('po_id', $this->goodsReceived->po_id)
            ->where('item_id', $this->item_id)
            ->first();
    }

    // ================================================================
    // QR CODE GENERATION METHODS
    // ================================================================

    /**
     * Generate QR codes for all item details associated with this GR detail
     * Method ini dipanggil setelah ItemDetail dibuat
     */
    public function generateQRCodesForItemDetails(): array
    {
        $results = [
            'success' => true,
            'generated_count' => 0,
            'failed_count' => 0,
            'details' => [],
            'errors' => []
        ];

        try {
            // Load item details if not already loaded
            if (!$this->relationLoaded('itemDetails')) {
                $this->load(['itemDetails.item']);
            }

            foreach ($this->itemDetails as $itemDetail) {
                $result = $this->generateQRCodeForSingleItem($itemDetail);

                if ($result['success']) {
                    $results['generated_count']++;
                    $results['details'][] = [
                        'item_detail_id' => $itemDetail->item_detail_id,
                        'serial_number' => $itemDetail->serial_number,
                        'qr_code' => $result['qr_code'],
                        'qr_image_saved' => $result['image_saved']
                    ];
                } else {
                    $results['failed_count']++;
                    $results['errors'][] = [
                        'item_detail_id' => $itemDetail->item_detail_id,
                        'serial_number' => $itemDetail->serial_number,
                        'error' => $result['error']
                    ];
                }
            }

            if ($results['failed_count'] > 0) {
                $results['success'] = false;
            }

            Log::info('QR codes generation completed for GR Detail', [
                'gr_detail_id' => $this->gr_detail_id,
                'generated' => $results['generated_count'],
                'failed' => $results['failed_count']
            ]);

        } catch (\Exception $e) {
            $results['success'] = false;
            $results['errors'][] = [
                'general_error' => $e->getMessage()
            ];

            Log::error('Failed to generate QR codes for GR Detail: ' . $e->getMessage(), [
                'gr_detail_id' => $this->gr_detail_id,
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $results;
    }

    /**
     * Generate QR code for single item detail
     */
    private function generateQRCodeForSingleItem(ItemDetail $itemDetail): array
    {
        try {
            // Load item relationship if not loaded
            if (!$itemDetail->relationLoaded('item')) {
                $itemDetail->load('item');
            }

            // Generate QR code string
            $qrCodeString = $this->generateQRCodeString($itemDetail);

            // Generate QR content
            $qrContent = $this->generateQRContent($itemDetail);

            // Save QR code image
            $imageSaved = $this->saveQRCodeImage($qrCodeString, $qrContent, $itemDetail);

            // Update item detail with QR code
            $itemDetail->update([
                'qr_code' => $qrCodeString . '.svg'
            ]);

            // Log activity
            \App\Models\ActivityLog::logActivity('item_details', $itemDetail->item_detail_id, 'qr_generated', null, [
                'qr_code' => $qrCodeString . '.svg',
                'generated_from_gr' => true,
                'gr_detail_id' => $this->gr_detail_id
            ]);

            return [
                'success' => true,
                'qr_code' => $qrCodeString,
                'image_saved' => $imageSaved,
                'qr_content' => $qrContent
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate QR code for item detail: ' . $e->getMessage(), [
                'item_detail_id' => $itemDetail->item_detail_id,
                'gr_detail_id' => $this->gr_detail_id
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate QR code string identifier
     */
    private function generateQRCodeString(ItemDetail $itemDetail): string
    {
        return 'AA-' . $itemDetail->item_detail_id . '-' . time();
    }

    /**
     * Generate QR content data
     */
    private function generateQRContent(ItemDetail $itemDetail): string
    {
        $qrData = [
            'type' => 'item_detail',
            'item_detail_id' => $itemDetail->item_detail_id,
            'serial_number' => $itemDetail->serial_number,
            'item_id' => $itemDetail->item_id,
            'item_code' => $itemDetail->item->item_code,
            'item_name' => $itemDetail->item->item_name,
            // 'status' => $itemDetail->status,
            // 'location' => $itemDetail->location,
            // 'generated_at' => now()->toISOString(),
            // 'generated_from' => 'goods_received',
            // 'gr_detail_id' => $this->gr_detail_id,
            // 'gr_id' => $this->gr_id,
            // 'api_url' => route('api.item-details.scan', $itemDetail->item_detail_id),
            // 'view_url' => route('item-details.show', $itemDetail->item_detail_id),
            // 'transaction_ready' => true
        ];

        return json_encode($qrData);
    }

    /**
     * Save QR code as SVG image
     */
    private function saveQRCodeImage(string $qrCodeString, string $qrContent, ItemDetail $itemDetail): bool
    {
        try {
            // Create directory if doesn't exist
            $directory = 'qr-codes/item-details';
            $fullPath = storage_path('app/public/' . $directory);

            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
                Log::info('Created QR code directory: ' . $fullPath);
            }

            // Generate QR code as SVG
            $svgContent = QrCode::size(500)
                ->margin(4)
                ->errorCorrection('L')
                ->generate($qrContent);

            // Save SVG file
            $fileName = $qrCodeString . '.svg';
            $filePath = $fullPath . '/' . $fileName;

            $saved = file_put_contents($filePath, $svgContent);

            if ($saved !== false) {
                Log::info('QR Code SVG saved successfully', [
                    'item_detail_id' => $itemDetail->item_detail_id,
                    'gr_detail_id' => $this->gr_detail_id,
                    'file_path' => $directory . '/' . $fileName,
                    'qr_code' => $qrCodeString,
                    'file_size' => $saved . ' bytes'
                ]);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Failed to save QR code image: ' . $e->getMessage(), [
                'item_detail_id' => $itemDetail->item_detail_id,
                'gr_detail_id' => $this->gr_detail_id,
                'qr_code' => $qrCodeString
            ]);
            return false;
        }
    }

    /**
     * Generate QR codes in bulk for all item details (public method)
     * Method ini bisa dipanggil manual jika diperlukan
     */
    public function generateBulkQRCodes(): array
    {
        return $this->generateQRCodesForItemDetails();
    }

    /**
     * Regenerate QR codes for existing item details
     * Berguna jika QR code hilang atau perlu di-regenerate
     */
    public function regenerateQRCodes(): array
    {
        $results = [
            'success' => true,
            'regenerated_count' => 0,
            'skipped_count' => 0,
            'failed_count' => 0,
            'details' => [],
            'errors' => []
        ];

        try {
            $this->load(['itemDetails.item']);

            foreach ($this->itemDetails as $itemDetail) {
                // Check if QR code already exists
                if (!empty($itemDetail->qr_code)) {
                    $results['skipped_count']++;
                    $results['details'][] = [
                        'item_detail_id' => $itemDetail->item_detail_id,
                        'serial_number' => $itemDetail->serial_number,
                        'action' => 'skipped',
                        'reason' => 'QR code already exists'
                    ];
                    continue;
                }

                $result = $this->generateQRCodeForSingleItem($itemDetail);

                if ($result['success']) {
                    $results['regenerated_count']++;
                    $results['details'][] = [
                        'item_detail_id' => $itemDetail->item_detail_id,
                        'serial_number' => $itemDetail->serial_number,
                        'action' => 'regenerated',
                        'qr_code' => $result['qr_code']
                    ];
                } else {
                    $results['failed_count']++;
                    $results['errors'][] = [
                        'item_detail_id' => $itemDetail->item_detail_id,
                        'serial_number' => $itemDetail->serial_number,
                        'error' => $result['error']
                    ];
                }
            }

            if ($results['failed_count'] > 0) {
                $results['success'] = false;
            }

        } catch (\Exception $e) {
            $results['success'] = false;
            $results['errors'][] = [
                'general_error' => $e->getMessage()
            ];
        }

        return $results;
    }

    /**
     * Get QR generation statistics for this GR detail
     */
    public function getQRGenerationStats(): array
    {
        $this->load('itemDetails');

        $totalItems = $this->itemDetails->count();
        $withQR = $this->itemDetails->whereNotNull('qr_code')->count();
        $withoutQR = $totalItems - $withQR;

        return [
            'total_item_details' => $totalItems,
            'with_qr_code' => $withQR,
            'without_qr_code' => $withoutQR,
            'qr_completion_rate' => $totalItems > 0 ? round(($withQR / $totalItems) * 100, 2) : 0,
            'needs_qr_generation' => $withoutQR > 0
        ];
    }

    // Static method: Generate detail ID
    public static function generateDetailId(): string
    {
        $lastDetail = self::orderBy('gr_detail_id', 'desc')->first();
        $lastNumber = $lastDetail ? (int) substr($lastDetail->gr_detail_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        return 'GRD' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    // ================================================================
    // EVENT HOOKS (untuk auto-generation)
    // ================================================================

    /**
     * Auto-generate QR codes after item details are created
     * Method ini dipanggil dari GoodsReceivedController setelah item details dibuat
     */
    public function autoGenerateQRCodes(): array
    {
        Log::info('Auto-generating QR codes for GR Detail', [
            'gr_detail_id' => $this->gr_detail_id,
            'item_id' => $this->item_id
        ]);

        return $this->generateQRCodesForItemDetails();
    }
}
