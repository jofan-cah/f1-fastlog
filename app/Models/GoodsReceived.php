<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GoodsReceived extends Model
{
    protected $table = 'goods_receiveds';
    protected $primaryKey = 'gr_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'gr_id',
        'receive_number',
        'po_id',                    // NULLABLE - bisa null untuk non-PO receipts
        'supplier_id',
        'receive_date',
        'status',
        'notes',
        'received_by',
        'receipt_type',             // NEW: 'po_based' atau 'direct'
        'delivery_note_number',     // NEW: nomor surat jalan
        'invoice_number',           // NEW: nomor invoice
        'external_reference',       // NEW: referensi eksternal lainnya
    ];

    protected $casts = [
        'receive_date' => 'date',
    ];

    // CONSTANTS untuk receipt types
    public const RECEIPT_TYPE_PO_BASED = 'po_based';
    public const RECEIPT_TYPE_DIRECT = 'direct';

    // Relationship: GR belongs to PurchaseOrder (NULLABLE)
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id', 'po_id');
    }

    // Relationship: GR belongs to Supplier
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    // Relationship: GR belongs to User (receiver)
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by', 'user_id');
    }

    // Relationship: GR has many GoodsReceivedDetails
    public function grDetails(): HasMany
    {
        return $this->hasMany(GoodsReceivedDetail::class, 'gr_id', 'gr_id');
    }

    // NEW: Helper method to check if this is PO-based receipt
    public function isPOBased(): bool
    {
        return $this->receipt_type === self::RECEIPT_TYPE_PO_BASED && !is_null($this->po_id);
    }

    // NEW: Helper method to check if this is direct receipt
    public function isDirectReceipt(): bool
    {
        return $this->receipt_type === self::RECEIPT_TYPE_DIRECT;
    }

    // // UPDATED: Helper method: Get status info
    // public function getStatusInfo(): array
    // {
    //     $statuses = [
    //         'partial' => [
    //             'text' => 'Sebagian',
    //             'class' => 'bg-yellow-100 text-yellow-800',
    //             'badge_class' => 'badge-warning',
    //             'description' => $this->isPOBased()
    //                 ? 'Penerimaan sebagian barang dari PO'
    //                 : 'Penerimaan sebagian barang (direct)'
    //         ],
    //         'complete' => [
    //             'text' => 'Selesai',
    //             'class' => 'bg-green-100 text-green-800',
    //             'badge_class' => 'badge-success',
    //             'description' => $this->isPOBased()
    //                 ? 'Penerimaan selesai sesuai PO'
    //                 : 'Penerimaan barang selesai (direct)'
    //         ],
    //     ];

    //     return $statuses[$this->status] ?? $statuses['partial'];
    // }

    // Helper method: Calculate total items received
    public function getTotalItemsReceived(): int
    {
        return $this->grDetails()->sum('quantity_received');
    }

    // Helper method: Calculate total value received
    public function getTotalValueReceived(): float
    {
        return $this->grDetails()
            ->selectRaw('SUM(quantity_received * unit_price) as total')
            ->value('total') ?? 0;
    }

/**
 * Get summary info for PO
 */
public function getSummaryInfo(): array
{
    // Load relationship jika belum di-load
    if (!$this->relationLoaded('poDetails')) {
        $this->load('poDetails');
    }

    $poDetails = $this->poDetails ?? collect();

    $totalQuantity = $poDetails->sum('quantity_ordered') ?? 0;
    $totalReceived = $poDetails->sum('quantity_received') ?? 0;
    $totalValue = $poDetails->sum(function ($detail) {
        return ($detail->quantity_ordered ?? 0) * ($detail->unit_price ?? 0);
    }) ?? 0;

    $completionPercentage = $totalQuantity > 0
        ? round(($totalReceived / $totalQuantity) * 100, 2)
        : 0;

    return [
        'total_quantity' => $totalQuantity,
        'total_received' => $totalReceived,
        'total_value' => $totalValue,
        'completion_percentage' => $completionPercentage,
        'total_items' => $poDetails->count(),
        'items_fully_received' => $poDetails->filter(function($detail) {
            return ($detail->quantity_received ?? 0) >= ($detail->quantity_ordered ?? 0);
        })->count(),
        'items_partial_received' => $poDetails->filter(function($detail) {
            $received = $detail->quantity_received ?? 0;
            $ordered = $detail->quantity_ordered ?? 0;
            return $received > 0 && $received < $ordered;
        })->count(),
        'items_not_received' => $poDetails->filter(function($detail) {
            return ($detail->quantity_received ?? 0) == 0;
        })->count(),
    ];
}
public function poDetails()
{
    return $this->hasMany(PoDetail::class, 'po_id', 'po_id');
}

/**
 * Get status info for PO
 */
public function getStatusInfo(): array
{
    $statuses = [
        'draft' => [
            'text' => 'Draft',
            'class' => 'bg-gray-100 text-gray-800',
            'badge_class' => 'badge-secondary'
        ],
        'sent' => [
            'text' => 'Sent',
            'class' => 'bg-blue-100 text-blue-800',
            'badge_class' => 'badge-primary'
        ],
        'partial' => [
            'text' => 'Partial',
            'class' => 'bg-yellow-100 text-yellow-800',
            'badge_class' => 'badge-warning'
        ],
        'complete' => [
            'text' => 'Complete',
            'class' => 'bg-green-100 text-green-800',
            'badge_class' => 'badge-success'
        ],
        'cancelled' => [
            'text' => 'Cancelled',
            'class' => 'bg-red-100 text-red-800',
            'badge_class' => 'badge-danger'
        ],
    ];

    return $statuses[$this->status ?? 'draft'] ?? $statuses['draft'];
}

/**
 * Get completion percentage
 */
public function getCompletionPercentage(): float
{
    $summary = $this->getSummaryInfo();
    return $summary['completion_percentage'] ?? 0;
}

// ALTERNATIF: Method yang lebih sederhana jika masih error
/**
 * Simple version - Get summary info safely
 */
public function getSummaryInfoSafe(): array
{
    try {
        // Load relationship dengan try-catch
        if (!$this->relationLoaded('poDetails')) {
            $this->load('poDetails');
        }

        $poDetails = $this->poDetails;

        // Jika masih null, return default values
        if (!$poDetails || $poDetails->isEmpty()) {
            return [
                'total_quantity' => 0,
                'total_received' => 0,
                'total_value' => 0,
                'completion_percentage' => 0,
                'total_items' => 0,
                'items_fully_received' => 0,
                'items_partial_received' => 0,
                'items_not_received' => 0,
            ];
        }

        // Hitung dengan safe operations
        $totalQuantity = 0;
        $totalReceived = 0;
        $totalValue = 0;

        foreach ($poDetails as $detail) {
            $qty = (int)($detail->quantity_ordered ?? 0);
            $rec = (int)($detail->quantity_received ?? 0);
            $price = (float)($detail->unit_price ?? 0);

            $totalQuantity += $qty;
            $totalReceived += $rec;
            $totalValue += $qty * $price;
        }

        $completionPercentage = $totalQuantity > 0
            ? round(($totalReceived / $totalQuantity) * 100, 2)
            : 0;

        return [
            'total_quantity' => $totalQuantity,
            'total_received' => $totalReceived,
            'total_value' => $totalValue,
            'completion_percentage' => $completionPercentage,
            'total_items' => $poDetails->count(),
            'items_fully_received' => $poDetails->filter(function($detail) {
                return ($detail->quantity_received ?? 0) >= ($detail->quantity_ordered ?? 0);
            })->count(),
            'items_partial_received' => $poDetails->filter(function($detail) {
                $received = $detail->quantity_received ?? 0;
                $ordered = $detail->quantity_ordered ?? 0;
                return $received > 0 && $received < $ordered;
            })->count(),
            'items_not_received' => $poDetails->filter(function($detail) {
                return ($detail->quantity_received ?? 0) == 0;
            })->count(),
        ];

    } catch (\Exception $e) {
        Log::error('Error in getSummaryInfo', [
            'po_id' => $this->po_id ?? 'unknown',
            'error' => $e->getMessage()
        ]);

        // Return safe default values jika ada error
        return [
            'total_quantity' => 0,
            'total_received' => 0,
            'total_value' => 0,
            'completion_percentage' => 0,
            'total_items' => 0,
            'items_fully_received' => 0,
            'items_partial_received' => 0,
            'items_not_received' => 0,
        ];
    }
}

    // UPDATED: Helper method: Check if all items from PO are received (only for PO-based)
    public function isCompleteReceive(): bool
    {
        if (!$this->isPOBased()) {
            // For direct receipts, we consider it complete if status is 'complete'
            return $this->status === 'complete';
        }

        // Existing PO-based logic
        $poDetails = $this->purchaseOrder->poDetails;
        foreach ($poDetails as $poDetail) {
            if ($poDetail->quantity_received < $poDetail->quantity_ordered) {
                return false;
            }
        }
        return true;
    }

    // UPDATED: Helper method: Update PO status based on this receiving (only for PO-based)
    public function updatePOStatus(): void
    {
        if ($this->isPOBased() && $this->purchaseOrder) {
            $this->purchaseOrder->updateStatusBasedOnReceived();
        }
        // If not PO-based, do nothing
    }

    // Helper method: Process stock updates
    public function processStockUpdates(): void
    {
        foreach ($this->grDetails as $detail) {
            $item = $detail->item;
            $stock = $item->stock;

            if ($stock) {
                // Add to available stock
                if ($detail->quantity_to_stock > 0) {
                    $stock->addStock(
                        $detail->quantity_to_stock,
                        $this->isPOBased() ? 'goods_received_po' : 'goods_received_direct',
                        $this->received_by
                    );
                }

                // Add to ready stock (direct usage)
                if ($detail->quantity_to_ready > 0) {
                    $stock->addStock(
                        $detail->quantity_to_ready,
                        $this->isPOBased() ? 'goods_received_ready_po' : 'goods_received_ready_direct',
                        $this->received_by
                    );

                    // Immediately move to used (since it's ready for use)
                    $stock->reduceStock(
                        $detail->quantity_to_ready,
                        'ready_for_use',
                        $this->received_by
                    );
                }
            }
        }
    }

    // NEW: Validation method for direct receipts
    public function validateDirectReceipt(): array
    {
        $errors = [];

        if ($this->isDirectReceipt()) {
            // Supplier is required for direct receipts
            if (!$this->supplier_id) {
                $errors[] = 'Supplier wajib dipilih untuk penerimaan langsung';
            }

            // At least one external reference should be provided
            if (!$this->delivery_note_number && !$this->invoice_number && !$this->external_reference) {
                $errors[] = 'Minimal satu referensi eksternal harus diisi (nomor surat jalan, invoice, atau referensi lain)';
            }

            // Check if details exist
            if ($this->grDetails()->count() === 0) {
                $errors[] = 'Detail barang harus diisi';
            }
        }

        return $errors;
    }

    // Existing scopes
    public function scopeByPO($query, $poId)
    {
        return $query->where('po_id', $poId);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('receive_date', [$startDate, $endDate]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // NEW: Scopes for receipt types
    public function scopePOBased($query)
    {
        return $query->where('receipt_type', self::RECEIPT_TYPE_PO_BASED)
            ->whereNotNull('po_id');
    }

    public function scopeDirectReceipts($query)
    {
        return $query->where('receipt_type', self::RECEIPT_TYPE_DIRECT);
    }

    public function scopeByReceiptType($query, $type)
    {
        return $query->where('receipt_type', $type);
    }

    // UPDATED: Static method: Generate receive number with type prefix
public static function generateReceiveNumber(string $type = self::RECEIPT_TYPE_PO_BASED): string
{
    $prefix = $type === self::RECEIPT_TYPE_DIRECT ? 'GRD' : 'GR';
    $currentYear = date('Y');
    $currentMonth = date('m');
    $yearMonth = $currentYear . $currentMonth;

    // Ambil SEMUA GR terakhir (GR dan GRD) untuk dapat sequence yang sama
    $lastGR = self::where(function($query) use ($yearMonth) {
            $query->where('receive_number', 'like', 'GR' . $yearMonth . '%')
                  ->orWhere('receive_number', 'like', 'GRD' . $yearMonth . '%');
        })
        ->orderBy('receive_number', 'desc')
        ->first();

    if (!$lastGR) {
        return $prefix . $yearMonth . '0001';
    }

    // Extract number dari GR/GRD terakhir
    $lastNumber = (int) substr($lastGR->receive_number, -4);
    $newNumber = $lastNumber + 1;

    return $prefix . $yearMonth . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
}

    // UPDATED: Static method: Get statistics
    public static function getStatistics(): array
    {
        return [
            'total' => self::count(),
            'today' => self::whereDate('receive_date', today())->count(),
            'this_week' => self::whereBetween('receive_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => self::whereMonth('receive_date', now()->month)->count(),
            'partial' => self::where('status', 'partial')->count(),
            'complete' => self::where('status', 'complete')->count(),

            // NEW: Statistics by receipt type
            'po_based' => self::where('receipt_type', self::RECEIPT_TYPE_PO_BASED)->count(),
            'direct_receipts' => self::where('receipt_type', self::RECEIPT_TYPE_DIRECT)->count(),
            'po_based_today' => self::where('receipt_type', self::RECEIPT_TYPE_PO_BASED)
                ->whereDate('receive_date', today())->count(),
            'direct_receipts_today' => self::where('receipt_type', self::RECEIPT_TYPE_DIRECT)
                ->whereDate('receive_date', today())->count(),

            // NEW: Value-based statistics
            'zero_value_receipts' => self::whereHas('grDetails', function ($q) {
                $q->where('unit_price', 0);
            })->count(),
            'valued_receipts' => self::whereHas('grDetails', function ($q) {
                $q->where('unit_price', '>', 0);
            })->count(),
        ];
    }

    // NEW: Helper method to check if this is a zero-value receipt
    public function hasZeroValueItems(): bool
    {
        return $this->grDetails()->where('unit_price', 0)->exists();
    }

    // NEW: Helper method to check if this is entirely zero-value
    public function isEntirelyZeroValue(): bool
    {
        return $this->grDetails()->where('unit_price', '>', 0)->count() === 0;
    }

    // NEW: Scope for zero-value receipts
    public function scopeZeroValue($query)
    {
        return $query->whereHas('grDetails', function ($q) {
            $q->where('unit_price', 0);
        });
    }

    // NEW: Scope for valued receipts
    public function scopeValued($query)
    {
        return $query->whereHas('grDetails', function ($q) {
            $q->where('unit_price', '>', 0);
        });
    }

    // NEW: Static method: Get receipt type options
    public static function getReceiptTypeOptions(): array
    {
        return [
            self::RECEIPT_TYPE_PO_BASED => 'Berdasarkan PO',
            self::RECEIPT_TYPE_DIRECT => 'Penerimaan Langsung'
        ];
    }

    // NEW: Helper method to get receipt type display name
    public function getReceiptTypeDisplayName(): string
    {
        $options = self::getReceiptTypeOptions();
        return $options[$this->receipt_type] ?? 'Unknown';
    }
}
