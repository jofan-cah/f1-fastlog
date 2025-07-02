<?php


// ================================================================
// 2. app/Models/GoodsReceivedDetail.php
// ================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    // Static method: Generate detail ID
    public static function generateDetailId(): string
    {
        $lastDetail = self::orderBy('gr_detail_id', 'desc')->first();
        $lastNumber = $lastDetail ? (int) substr($lastDetail->gr_detail_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        return 'GRD' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
