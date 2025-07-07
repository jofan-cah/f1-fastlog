<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_orders';
    protected $primaryKey = 'po_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'po_id',
        'po_number',
        'supplier_id',
        'po_date',
        'expected_date',
        'status',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'po_date' => 'date',
        'expected_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    // Relationship: PO belongs to Supplier
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    // Relationship: PO belongs to User (creator)
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    // Relationship: PO has many PoDetails
    public function poDetails(): HasMany
    {
        return $this->hasMany(PoDetail::class, 'po_id', 'po_id');
    }

    // Relationship: PO has many GoodsReceived
    public function goodsReceived(): HasMany
    {
        return $this->hasMany(GoodsReceived::class, 'po_id', 'po_id');
    }

    // Helper method: Get status info
    public function getStatusInfo(): array
    {
        $statuses = [
            'draft' => [
                'text' => 'Draft',
                'class' => 'bg-gray-100 text-gray-800',
                'badge_class' => 'badge-secondary',
                'description' => 'PO masih dalam tahap penyusunan'
            ],
            'sent' => [
                'text' => 'Terkirim',
                'class' => 'bg-blue-100 text-blue-800',
                'badge_class' => 'badge-primary',
                'description' => 'PO telah dikirim ke supplier'
            ],
            'partial' => [
                'text' => 'Sebagian Diterima',
                'class' => 'bg-yellow-100 text-yellow-800',
                'badge_class' => 'badge-warning',
                'description' => 'Sebagian barang sudah diterima'
            ],
            'received' => [
                'text' => 'Selesai',
                'class' => 'bg-green-100 text-green-800',
                'badge_class' => 'badge-success',
                'description' => 'Semua barang sudah diterima'
            ],
            'cancelled' => [
                'text' => 'Dibatalkan',
                'class' => 'bg-red-100 text-red-800',
                'badge_class' => 'badge-danger',
                'description' => 'PO dibatalkan'
            ],
        ];

        return $statuses[$this->status] ?? $statuses['draft'];
    }

    // Helper method: Check if PO can be edited
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft']);
    }

    // Helper method: Check if PO can be cancelled
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'sent']);
    }

    // Helper method: Check if PO can receive goods
    public function canReceiveGoods(): bool
    {
        return in_array($this->status, ['sent', 'partial']);
    }

    // Helper method: Calculate total amount from details
    public function calculateTotalAmount(): float
    {
        return $this->poDetails()->sum('total_price');
    }

    // Helper method: Update total amount
    public function updateTotalAmount(): void
    {
        $this->update(['total_amount' => $this->calculateTotalAmount()]);
    }

    // Helper method: Get completion percentage
    public function getCompletionPercentage(): float
    {
        $details = $this->poDetails;

        if ($details->isEmpty()) {
            return 0;
        }

        $totalOrdered = $details->sum('quantity_ordered');
        $totalReceived = $details->sum('quantity_received');

        if ($totalOrdered == 0) {
            return 0;
        }

        return round(($totalReceived / $totalOrdered) * 100, 1);
    }

    // Helper method: Check if PO is overdue
    public function isOverdue(): bool
    {
        return $this->expected_date &&
            $this->expected_date->isPast() &&
            !in_array($this->status, ['received', 'cancelled']);
    }

    // Helper method: Get days until expected date
    public function getDaysUntilExpected(): ?int
    {
        if (!$this->expected_date) {
            return null;
        }

        return now()->diffInDays($this->expected_date, false);
    }

    // Helper method: Get summary info
    public function getSummaryInfo(): array
    {
        $details = $this->poDetails;

        return [
            'total_items' => $details->count(),
            'total_quantity' => $details->sum('quantity_ordered'),
            'total_received' => $details->sum('quantity_received'),
            'total_amount' => $this->total_amount,
            'completion_percentage' => $this->getCompletionPercentage(),
            'is_overdue' => $this->isOverdue(),
            'days_until_expected' => $this->getDaysUntilExpected(),
        ];
    }

    // Helper method: Update PO status based on received quantities
    public function updateStatusBasedOnReceived(): void
    {
        $details = $this->poDetails;

        if ($details->isEmpty()) {
            return;
        }

        $totalOrdered = $details->sum('quantity_ordered');
        $totalReceived = $details->sum('quantity_received');

        if ($totalReceived == 0) {
            // No items received yet
            if ($this->status === 'partial') {
                $this->update(['status' => 'sent']);
            }
        } elseif ($totalReceived >= $totalOrdered) {
            // All items received
            $this->update(['status' => 'received']);
        } else {
            // Partially received
            $this->update(['status' => 'partial']);
        }
    }

    // Scope: Filter by status
    public function scopeByStatus($query, $status)
    {
        if (!$status) return $query;
        return $query->where('status', $status);
    }

    // Scope: Filter by supplier
    public function scopeBySupplier($query, $supplierId)
    {
        if (!$supplierId) return $query;
        return $query->where('supplier_id', $supplierId);
    }

    // Scope: Filter by date range
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('po_date', [$startDate, $endDate]);
    }

    // Scope: Overdue POs
    public function scopeOverdue($query)
    {
        return $query->where('expected_date', '<', now())
            ->whereNotIn('status', ['received', 'cancelled']);
    }

    // Scope: Active POs (not cancelled or completed)
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['received', 'cancelled']);
    }

    public static function generatePONumber(): string
    {
        // Format prefix: PO + Tahun + Bulan
        $prefix = 'PO' . date('Ym'); // YYYYMM

        // Ambil PO terakhir untuk bulan ini
        $lastPO = self::where('po_number', 'like', $prefix . '%')
            ->orderBy('po_number', 'desc')
            ->first();

        if (!$lastPO) {
            return $prefix . '0001';
        }

        // Ambil 4 digit terakhir (sequence)
        $lastNumber = (int) substr($lastPO->po_number, -4);
        $newNumber = $lastNumber + 1;

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }


    // Static method: Get PO statistics
    public static function getStatistics(): array
    {
        return [
            'total' => self::count(),
            'draft' => self::where('status', 'draft')->count(),
            'sent' => self::where('status', 'sent')->count(),
            'partial' => self::where('status', 'partial')->count(),
            'received' => self::where('status', 'received')->count(),
            'cancelled' => self::where('status', 'cancelled')->count(),
            'overdue' => self::overdue()->count(),
            'this_month' => self::whereMonth('po_date', now()->month)->count(),
        ];
    }
}
