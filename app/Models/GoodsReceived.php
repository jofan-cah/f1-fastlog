<?php

// ================================================================
// 1. app/Models/GoodsReceived.php
// ================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class GoodsReceived extends Model
{
    protected $table = 'goods_receiveds';
    protected $primaryKey = 'gr_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'gr_id',
        'receive_number',
        'po_id',
        'supplier_id',
        'receive_date',
        'status',
        'notes',
        'received_by',
    ];

    protected $casts = [
        'receive_date' => 'date',
    ];

    // Relationship: GR belongs to PurchaseOrder
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

    // Helper method: Get status info
    public function getStatusInfo(): array
    {
        $statuses = [
            'partial' => [
                'text' => 'Sebagian',
                'class' => 'bg-yellow-100 text-yellow-800',
                'badge_class' => 'badge-warning',
                'description' => 'Penerimaan sebagian barang'
            ],
            'complete' => [
                'text' => 'Selesai',
                'class' => 'bg-green-100 text-green-800',
                'badge_class' => 'badge-success',
                'description' => 'Penerimaan selesai'
            ],
        ];

        return $statuses[$this->status] ?? $statuses['partial'];
    }

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

    // Helper method: Get summary info
    public function getSummaryInfo(): array
    {
        $details = $this->grDetails;

        return [
            'total_items' => $details->count(),
            'total_quantity' => $details->sum('quantity_received'),
            'total_to_stock' => $details->sum('quantity_to_stock'),
            'total_to_ready' => $details->sum('quantity_to_ready'),
            'total_value' => $this->getTotalValueReceived(),
        ];
    }

    // Helper method: Check if all items from PO are received
    public function isCompleteReceive(): bool
    {
        $poDetails = $this->purchaseOrder->poDetails;

        foreach ($poDetails as $poDetail) {
            if ($poDetail->quantity_received < $poDetail->quantity_ordered) {
                return false;
            }
        }

        return true;
    }

    // Helper method: Update PO status based on this receiving
    public function updatePOStatus(): void
    {
        $this->purchaseOrder->updateStatusBasedOnReceived();
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
                        'goods_receiveds',
                        $this->received_by
                    );
                }

                // Add to ready stock (direct usage)
                if ($detail->quantity_to_ready > 0) {
                    $stock->addStock(
                        $detail->quantity_to_ready,
                        'goods_received_ready',
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

    // Scope: Filter by PO
    public function scopeByPO($query, $poId)
    {
        return $query->where('po_id', $poId);
    }

    // Scope: Filter by supplier
    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    // Scope: Filter by date range
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('receive_date', [$startDate, $endDate]);
    }

    // Scope: Filter by status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Static method: Generate receive number
    public static function generateReceiveNumber(): string
    {
        $lastGR = self::orderBy('receive_number', 'desc')->first();

        if (!$lastGR) {
            return 'GR' . date('Y') . '001';
        }

        // Extract number from last GR
        $lastNumber = (int) substr($lastGR->receive_number, -3);
        $newNumber = $lastNumber + 1;

        return 'GR' . date('Y') . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    // Static method: Get statistics
    public static function getStatistics(): array
    {
        return [
            'total' => self::count(),
            'today' => self::whereDate('receive_date', today())->count(),
            'this_week' => self::whereBetween('receive_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => self::whereMonth('receive_date', now()->month)->count(),
            'partial' => self::where('status', 'partial')->count(),
            'complete' => self::where('status', 'complete')->count(),
        ];
    }
}
