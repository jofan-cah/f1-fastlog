<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoDetail extends Model
{
    protected $table = 'po_details';
    protected $primaryKey = 'po_detail_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'po_detail_id',
        'po_id',
        'item_id',
        'quantity_ordered',
        'unit_price',
        'total_price',
        'quantity_received',
        'notes',
    ];

    protected $casts = [
        'quantity_ordered' => 'integer',
        'quantity_received' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // Relationship: PoDetail belongs to PurchaseOrder
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id', 'po_id');
    }

    // Relationship: PoDetail belongs to Item
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    // Helper method: Calculate total price
    public function calculateTotalPrice(): float
    {
        return $this->quantity_ordered * $this->unit_price;
    }

    // Helper method: Update total price
    public function updateTotalPrice(): void
    {
        $this->update(['total_price' => $this->calculateTotalPrice()]);
    }

    // Helper method: Get remaining quantity to receive
    public function getRemainingQuantity(): int
    {
        return max(0, $this->quantity_ordered - $this->quantity_received);
    }

    // Helper method: Check if fully received
    public function isFullyReceived(): bool
    {
        return $this->quantity_received >= $this->quantity_ordered;
    }

    // Helper method: Get completion percentage
    public function getCompletionPercentage(): float
    {
        if ($this->quantity_ordered == 0) {
            return 0;
        }

        return round(($this->quantity_received / $this->quantity_ordered) * 100, 1);
    }

    // Helper method: Get status info
    public function getStatusInfo(): array
    {
        $percentage = $this->getCompletionPercentage();

        if ($percentage == 0) {
            return [
                'status' => 'pending',
                'text' => 'Menunggu',
                'class' => 'bg-gray-100 text-gray-800'
            ];
        } elseif ($percentage >= 100) {
            return [
                'status' => 'completed',
                'text' => 'Selesai',
                'class' => 'bg-green-100 text-green-800'
            ];
        } else {
            return [
                'status' => 'partial',
                'text' => "Sebagian ({$percentage}%)",
                'class' => 'bg-yellow-100 text-yellow-800'
            ];
        }
    }

    // Static method: Generate detail ID
    public static function generateDetailId(): string
    {
        $lastDetail = self::orderBy('po_detail_id', 'desc')->first();
        $lastNumber = $lastDetail ? (int) substr($lastDetail->po_detail_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        return 'POD' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
