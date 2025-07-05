<?php

// ================================================================
// 1. app/Models/Item.php
// ================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    protected $table = 'items';
    protected $primaryKey = 'item_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'item_id',
        'item_code',
        'item_name',
        'category_id',
        'unit',
        'min_stock',
        'description',
        'qr_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_stock' => 'integer',
    ];

    // Relationship: Item belongs to Category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    // Relationship: Item has one Stock record
    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class, 'item_id', 'item_id');
    }

    // Relationship: Item has many PO Details
    public function poDetails(): HasMany
    {
        return $this->hasMany(PoDetail::class, 'item_id', 'item_id');
    }

    // Relationship: Item has many Item Details (individual tracking)
    public function itemDetails(): HasMany
    {
        return $this->hasMany(ItemDetail::class, 'item_id', 'item_id');
    }

    // Relationship: Item has many Transactions
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'item_id', 'item_id');
    }

    // Helper method: Check if item is active
    public function isActive(): bool
    {
        return $this->is_active;
    }

    // Helper method: Get category path (Elektronik > Modem > ZTE F960)
    public function getCategoryPath(): string
    {
        return $this->category ? $this->category->getFullPath() : 'Tidak ada kategori';
    }

    // Helper method: Get current stock info
    public function getStockInfo(): array
    {
        $stock = $this->stock;

        if (!$stock) {
            return [
                'available' => 0,
                'used' => 0,
                'total' => 0,
                'status' => 'no_stock',
                'status_text' => 'Belum ada stok',
                'status_class' => 'badge-secondary'
            ];
        }

        $available = $stock->quantity_available ?? 0;
        $used = $stock->quantity_used ?? 0;
        $total = $stock->total_quantity ?? 0;

        // Determine stock status
        $status = 'sufficient';
        $statusText = 'Stok Cukup';
        $statusClass = 'badge-success';

        if ($total == 0) {
            $status = 'empty';
            $statusText = 'Stok Habis';
            $statusClass = 'badge-danger';
        } elseif ($available <= $this->min_stock) {
            $status = 'low';
            $statusText = 'Stok Rendah';
            $statusClass = 'badge-warning';
        }

        return [
            'available' => $available,
            'used' => $used,
            'total' => $total,
            'min_stock' => $this->min_stock,
            'status' => $status,
            'status_text' => $statusText,
            'status_class' => $statusClass
        ];
    }

    // Helper method: Check if stock is low
    public function isLowStock(): bool
    {
        $stock = $this->stock;
        return $stock && $stock->quantity_available <= $this->min_stock;
    }

    // Helper method: Check if item has stock
    public function hasStock(): bool
    {
        $stock = $this->stock;
        return $stock && $stock->total_quantity > 0;
    }

    // Helper method: Generate QR code content
    public function generateQRContent(): string
    {
        return json_encode([
            'type' => 'item',
            'item_id' => $this->item_id,
            'item_code' => $this->item_code,
            'item_name' => $this->item_name,
            'generated_at' => now()->toISOString()
        ]);
    }

    // Helper method: Get QR code URL/path
    public function getQRCodePath(): string
    {
        return $this->qr_code ? "/storage/qr-codes/{$this->qr_code}" : '';
    }

    // Helper method: Check if QR code exists
    public function hasQRCode(): bool
    {
        return !empty($this->qr_code) && file_exists(storage_path("app/public/qr-codes/{$this->qr_code}"));
    }

    // Helper method: Get item status badge
    public function getStatusBadgeClass(): string
    {
        return $this->is_active ? 'badge-success' : 'badge-danger';
    }

    // Helper method: Get item status text
    public function getStatusText(): string
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    // Scope: Only active items
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: Search items
    public function scopeSearch($query, $term)
    {
        if (!$term) return $query;

        return $query->where(function($q) use ($term) {
            $q->where('item_code', 'like', "%{$term}%")
              ->orWhere('item_name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    // Scope: Filter by category
    public function scopeByCategory($query, $categoryId)
    {
        if (!$categoryId) return $query;

        return $query->where('category_id', $categoryId);
    }

    // Scope: Filter by status
    public function scopeByStatus($query, $status)
    {
        if ($status === null) return $query;

        return $query->where('is_active', $status === 'active');
    }

    // Scope: Low stock items
    public function scopeLowStock($query)
    {
        return $query->whereHas('stock', function($q) {
            $q->whereRaw('quantity_available <= items.min_stock');
        });
    }

    // Scope: Items without stock
    public function scopeWithoutStock($query)
    {
        return $query->whereDoesntHave('stock')
                    ->orWhereHas('stock', function($q) {
                        $q->where('total_quantity', 0);
                    });
    }

    // Static method: Generate next item code
    public static function generateItemCode(): string
{
    do {
        $letters = '';
        for ($i = 0; $i < 3; $i++) {
            $letters .= chr(random_int(65, 90)); // ASCII A-Z
        }
    } while (Item::where('item_id', $letters)->exists());

    return $letters;
}


    // Static method: Get common units
    public static function getCommonUnits(): array
    {
        return [
            'pcs' => 'Pieces (pcs)',
            'unit' => 'Unit',
            'set' => 'Set',
            'box' => 'Box',
            'pack' => 'Pack',
            'meter' => 'Meter (m)',
            'kg' => 'Kilogram (kg)',
            'gram' => 'Gram (g)',
            'liter' => 'Liter (L)',
            'roll' => 'Roll',
            'sheet' => 'Sheet',
            'bottle' => 'Bottle',
            'pair' => 'Pair',
        ];
    }
}
