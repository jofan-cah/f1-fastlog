<?php

// ================================================================
// 1. app/Models/Stock.php
// ================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Stock extends Model
{
    protected $table = 'stocks';
    protected $primaryKey = 'stock_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'stock_id',
        'item_id',
        'quantity_available',
        'quantity_used',
        'total_quantity',
        'last_updated',
    ];

    protected $casts = [
        'quantity_available' => 'integer',
        'quantity_used' => 'integer',
        'total_quantity' => 'integer',
        'last_updated' => 'datetime',
    ];

    // Relationship: Stock belongs to Item
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    // Relationship: Stock has many Movements (future)
    // public function movements(): HasMany
    // {
    //     return $this->hasMany(StockMovement::class, 'stock_id', 'stock_id');
    // }

    // Helper method: Calculate total from available + used
    public function calculateTotal(): int
    {
        return $this->quantity_available + $this->quantity_used;
    }

    // Helper method: Get stock status
    public function getStockStatus(): array
    {
        $item = $this->item;
        $minStock = $item ? $item->min_stock : 0;

        if ($this->total_quantity == 0) {
            return [
                'status' => 'empty',
                'text' => 'Stok Habis',
                'class' => 'bg-red-100 text-red-800',
                'badge_class' => 'badge-danger'
            ];
        }

        if ($this->quantity_available <= $minStock) {
            return [
                'status' => 'low',
                'text' => 'Stok Rendah',
                'class' => 'bg-yellow-100 text-yellow-800',
                'badge_class' => 'badge-warning'
            ];
        }

        return [
            'status' => 'sufficient',
            'text' => 'Stok Cukup',
            'class' => 'bg-green-100 text-green-800',
            'badge_class' => 'badge-success'
        ];
    }

    // Helper method: Check if stock is low
    public function isLowStock(): bool
    {
        $item = $this->item;
        $minStock = $item ? $item->min_stock : 0;
        return $this->quantity_available <= $minStock;
    }

    // Helper method: Check if out of stock
    public function isOutOfStock(): bool
    {
        return $this->total_quantity == 0 || $this->quantity_available == 0;
    }

    // Helper method: Get stock percentage
    public function getStockPercentage(): float
    {
        if ($this->total_quantity == 0) return 0;
        return round(($this->quantity_available / $this->total_quantity) * 100, 1);
    }

    // Helper method: Add stock (receiving goods)
    public function addStock(int $quantity, string $reason = 'manual_adjustment', ?string $userId = null): bool
    {
        try {
            $oldAvailable = $this->quantity_available;
            $oldTotal = $this->total_quantity;

            $this->quantity_available += $quantity;
            $this->total_quantity += $quantity;
            $this->last_updated = now();
            $this->save();

            // Log stock movement
            $this->logMovement([
                'type' => 'in',
                'quantity' => $quantity,
                'reason' => $reason,
                'before_available' => $oldAvailable,
                'after_available' => $this->quantity_available,
                'before_total' => $oldTotal,
                'after_total' => $this->total_quantity,
                'user_id' => $userId
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to add stock: ' . $e->getMessage());
            return false;
        }
    }

    // Helper method: Reduce stock (using goods)
    public function reduceStock(int $quantity, string $reason = 'manual_adjustment', ?string $userId = null): bool
    {
        try {
            if ($this->quantity_available < $quantity) {
                throw new \Exception('Stok tidak mencukupi');
            }

            $oldAvailable = $this->quantity_available;
            $oldUsed = $this->quantity_used;

            $this->quantity_available -= $quantity;
            $this->quantity_used += $quantity;
            $this->last_updated = now();
            $this->save();

            // Log stock movement
            $this->logMovement([
                'type' => 'out',
                'quantity' => $quantity,
                'reason' => $reason,
                'before_available' => $oldAvailable,
                'after_available' => $this->quantity_available,
                'before_used' => $oldUsed,
                'after_used' => $this->quantity_used,
                'user_id' => $userId
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to reduce stock: ' . $e->getMessage());
            return false;
        }
    }

    // Helper method: Adjust stock manually
    public function adjustStock(int $newAvailable, int $newUsed, string $reason = 'manual_adjustment', ?string $userId = null): bool
    {
        try {
            $oldAvailable = $this->quantity_available;
            $oldUsed = $this->quantity_used;
            $oldTotal = $this->total_quantity;

            $this->quantity_available = $newAvailable;
            $this->quantity_used = $newUsed;
            $this->total_quantity = $newAvailable + $newUsed;
            $this->last_updated = now();
            $this->save();

            // Calculate movement
            $availableDiff = $newAvailable - $oldAvailable;
            $usedDiff = $newUsed - $oldUsed;
            $totalDiff = $this->total_quantity - $oldTotal;

            // Log stock movement
            $this->logMovement([
                'type' => 'adjustment',
                'quantity' => $totalDiff,
                'reason' => $reason,
                'before_available' => $oldAvailable,
                'after_available' => $this->quantity_available,
                'before_used' => $oldUsed,
                'after_used' => $this->quantity_used,
                'before_total' => $oldTotal,
                'after_total' => $this->total_quantity,
                'available_diff' => $availableDiff,
                'used_diff' => $usedDiff,
                'user_id' => $userId
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to adjust stock: ' . $e->getMessage());
            return false;
        }
    }

    // Helper method: Return stock (from used to available)
    public function returnStock(int $quantity, string $reason = 'return', ?string $userId = null): bool
    {
        try {
            if ($this->quantity_used < $quantity) {
                throw new \Exception('Quantity used tidak mencukupi untuk return');
            }

            $oldAvailable = $this->quantity_available;
            $oldUsed = $this->quantity_used;

            $this->quantity_available += $quantity;
            $this->quantity_used -= $quantity;
            $this->last_updated = now();
            $this->save();

            // Log stock movement
            $this->logMovement([
                'type' => 'return',
                'quantity' => $quantity,
                'reason' => $reason,
                'before_available' => $oldAvailable,
                'after_available' => $this->quantity_available,
                'before_used' => $oldUsed,
                'after_used' => $this->quantity_used,
                'user_id' => $userId
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to return stock: ' . $e->getMessage());
            return false;
        }
    }

    // Scope: Low stock items
    public function scopeLowStock($query)
    {
        return $query->whereHas('item', function($q) {
            $q->whereRaw('stocks.quantity_available <= items.min_stock');
        });
    }

    // Scope: Out of stock items
    public function scopeOutOfStock($query)
    {
        return $query->where('total_quantity', 0)
                    ->orWhere('quantity_available', 0);
    }

    // Scope: Available stock only
    public function scopeAvailable($query)
    {
        return $query->where('quantity_available', '>', 0);
    }

    // Scope: By category
    public function scopeByCategory($query, $categoryId)
    {
        return $query->whereHas('item', function($q) use ($categoryId) {
            $q->where('category_id', $categoryId);
        });
    }

    // Static method: Get stock summary
    public static function getStockSummary(): array
    {
        $total = self::count();
        $lowStock = self::lowStock()->count();
        $outOfStock = self::outOfStock()->count();
        $available = self::available()->count();

        return [
            'total_items' => $total,
            'available_items' => $available,
            'low_stock_items' => $lowStock,
            'out_of_stock_items' => $outOfStock,
            'sufficient_items' => $total - $lowStock - $outOfStock,
        ];
    }

    // Log stock movement (akan diintegrasikan dengan StockMovement model nanti)
    private function logMovement(array $data): void
    {
        try {
            // For now, just log to Laravel log
            Log::info('Stock Movement', array_merge([
                'stock_id' => $this->stock_id,
                'item_id' => $this->item_id,
                'timestamp' => now()->toISOString()
            ], $data));

            // Nanti bisa diintegrasikan dengan StockMovement model:
            /*
            StockMovement::create([
                'movement_id' => $this->generateMovementId(),
                'stock_id' => $this->stock_id,
                'type' => $data['type'],
                'quantity' => $data['quantity'],
                'reason' => $data['reason'],
                'before_available' => $data['before_available'] ?? null,
                'after_available' => $data['after_available'] ?? null,
                'before_used' => $data['before_used'] ?? null,
                'after_used' => $data['after_used'] ?? null,
                'user_id' => $data['user_id'],
                'created_at' => now()
            ]);
            */
        } catch (\Exception $e) {
            Log::error('Failed to log stock movement: ' . $e->getMessage());
        }
    }
}
