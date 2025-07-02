<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemDetail extends Model
{
    protected $table = 'item_details';
    protected $primaryKey = 'item_detail_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'item_detail_id',
        'gr_detail_id',
        'item_id',
        'serial_number',
        'custom_attributes',
        'qr_code',
        'status',
        'location',
        'notes',
    ];

    protected $casts = [
        'custom_attributes' => 'array',
    ];

    // Relationship: ItemDetail belongs to GoodsReceivedDetail
    public function goodsReceivedDetail(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedDetail::class, 'gr_detail_id', 'gr_detail_id');
    }

    // Relationship: ItemDetail belongs to Item
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    // Relationship: ItemDetail has many TransactionDetails
    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'item_detail_id', 'item_detail_id');
    }

    // Helper method: Get status info
    public function getStatusInfo(): array
    {
        $statuses = [
            'available' => [
                'text' => 'Tersedia',
                'class' => 'bg-green-100 text-green-800',
                'badge_class' => 'badge-success'
            ],
            'used' => [
                'text' => 'Terpakai',
                'class' => 'bg-blue-100 text-blue-800',
                'badge_class' => 'badge-primary'
            ],
            'damaged' => [
                'text' => 'Rusak',
                'class' => 'bg-red-100 text-red-800',
                'badge_class' => 'badge-danger'
            ],
            'maintenance' => [
                'text' => 'Maintenance',
                'class' => 'bg-yellow-100 text-yellow-800',
                'badge_class' => 'badge-warning'
            ],
            'reserved' => [
                'text' => 'Reserved',
                'class' => 'bg-purple-100 text-purple-800',
                'badge_class' => 'badge-info'
            ],
        ];

        return $statuses[$this->status] ?? $statuses['available'];
    }

    // Helper method: Check if item is available
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    // Helper method: Get formatted custom attributes - ENHANCED
    public function getFormattedAttributes(): array
    {
        // Handle case where custom_attributes is null, empty, or not array
        if (!$this->custom_attributes || !is_array($this->custom_attributes) || empty($this->custom_attributes)) {
            return [];
        }

        $formatted = [];
        foreach ($this->custom_attributes as $key => $value) {
            // Skip empty values
            if (is_null($value) || $value === '') {
                continue;
            }

            $formatted[] = [
                'key' => ucfirst(str_replace(['_', '-'], ' ', $key)),
                'value' => $value,
                'raw_key' => $key // Keep original key for editing
            ];
        }

        return $formatted;
    }

    // Helper method: Get custom attribute value by key
    public function getCustomAttribute(string $key, $default = null)
    {
        if (!$this->hasCustomAttributes()) {
            return $default;
        }

        return $this->custom_attributes[$key] ?? $default;
    }

    // Helper method: Set custom attribute value
    public function setCustomAttribute(string $key, $value): bool
    {
        try {
            $attributes = $this->custom_attributes ?: [];

            if (is_null($value) || $value === '') {
                // Remove empty attributes
                unset($attributes[$key]);
            } else {
                $attributes[$key] = $value;
            }

            $this->custom_attributes = !empty($attributes) ? $attributes : null;
            return $this->save();
        } catch (\Exception $e) {
            return false;
        }
    }

    // Helper method: Generate QR content for individual item
    public function generateQRContent(): string
    {
        return json_encode([
            'type' => 'item_detail',
            'item_detail_id' => $this->item_detail_id,
            'item_id' => $this->item_id,
            'item_code' => $this->item->item_code,
            'serial_number' => $this->serial_number,
            'status' => $this->status,
            'generated_at' => now()->toISOString()
        ]);
    }

    // Helper method: Get usage history
    public function getUsageHistory(): array
    {
        // Check if transactionDetails relationship exists
        if (!method_exists($this, 'transactionDetails')) {
            return [];
        }

        return $this->transactionDetails()
            ->with('transaction.createdBy')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($detail) {
                return [
                    'date' => $detail->created_at->format('Y-m-d H:i'),
                    'action' => $detail->transaction->transaction_type,
                    'user' => $detail->transaction->createdBy->full_name,
                    'status_before' => $detail->status_before,
                    'status_after' => $detail->status_after,
                    'notes' => $detail->notes,
                ];
            })
            ->toArray();
    }

    // Helper method: Check if has custom attributes
    public function hasCustomAttributes(): bool
    {
        return !empty($this->custom_attributes) && is_array($this->custom_attributes);
    }

    // Helper method: Update custom attributes safely
    public function updateCustomAttributes(array $attributes): bool
    {
        try {
            $this->custom_attributes = $attributes;
            return $this->save();
        } catch (\Exception $e) {
            return false;
        }
    }

    // Scope: Filter by status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope: Available items only
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    // Scope: Filter by item
    public function scopeByItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    // Scope: Filter by location
    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    // Static method: Generate item detail ID
    public static function generateItemDetailId(): string
    {
        $lastDetail = self::orderBy('item_detail_id', 'desc')->first();
        $lastNumber = $lastDetail ? (int) substr($lastDetail->item_detail_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        return 'ITD' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
    }

    // Static method: Generate serial number
    public static function generateSerialNumber(string $itemCode): string
    {
        $year = date('Y');
        $lastSerial = self::whereHas('item', function($q) use ($itemCode) {
                $q->where('item_code', $itemCode);
            })
            ->where('serial_number', 'like', "{$itemCode}-{$year}-%")
            ->orderBy('serial_number', 'desc')
            ->first();

        if (!$lastSerial) {
            return "{$itemCode}-{$year}-001";
        }

        $lastNumber = (int) substr($lastSerial->serial_number, -3);
        $newNumber = $lastNumber + 1;

        return "{$itemCode}-{$year}-" . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
