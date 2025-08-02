<?php

// ================================================================
// 1. app/Models/Stock.php
// ================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
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

    /**
     * Synchronize stock with actual item_details status - FIXED VERSION
     */
    public function syncWithItemDetails(): array
    {
        try {
            return DB::transaction(function () {
                $item = $this->item;
                if (!$item) {
                    throw new \Exception('Item not found');
                }

                // ✅ FIXED: Fresh query dengan DB table langsung
                $actualQuantities = $this->calculateActualQuantitiesFromItemDetailsFixed($item);

                // Store old values for logging
                $oldValues = [
                    'quantity_available' => $this->quantity_available,
                    'quantity_used' => $this->quantity_used,
                    'total_quantity' => $this->total_quantity,
                ];

                // ✅ FIXED: Update stock berdasarkan status item_details
                $this->quantity_available = $actualQuantities['stock_status']; // status 'stock'
                $this->quantity_used = $actualQuantities['available_status']; // status 'available'
                $this->total_quantity = $actualQuantities['total_trackable'];
                $this->last_updated = now();
                $this->save();

                // Verify update berhasil
                $this->refresh();

                Log::info('Stock synced successfully', [
                    'stock_id' => $this->stock_id,
                    'item_code' => $item->item_code,
                    'changes' => [
                        'available_diff' => $this->quantity_available - $oldValues['quantity_available'],
                        'used_diff' => $this->quantity_used - $oldValues['quantity_used'],
                        'total_diff' => $this->total_quantity - $oldValues['total_quantity'],
                    ],
                    'breakdown' => $actualQuantities['detailed']
                ]);

                return [
                    'success' => true,
                    'old_values' => $oldValues,
                    'new_values' => [
                        'quantity_available' => $this->quantity_available,
                        'quantity_used' => $this->quantity_used,
                        'total_quantity' => $this->total_quantity
                    ],
                    'changes' => [
                        'available_diff' => $this->quantity_available - $oldValues['quantity_available'],
                        'used_diff' => $this->quantity_used - $oldValues['quantity_used'],
                        'total_diff' => $this->total_quantity - $oldValues['total_quantity'],
                    ],
                    'breakdown' => $actualQuantities['detailed']
                ];
            });
        } catch (\Exception $e) {
            Log::error('Sync failed', [
                'stock_id' => $this->stock_id,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * FIXED: Calculate quantities dengan fresh DB query
     */
    private function calculateActualQuantitiesFromItemDetailsFixed(Item $item): array
    {
        // ✅ FIXED: Direct DB query untuk hindari cache
        $itemDetails = DB::table('item_details')
            ->where('item_id', $item->item_id)
            ->select('detail_id', 'serial_number', 'status')
            ->get();

        $quantities = [
            'stock' => 0,      // status 'stock' = di gudang
            'available' => 0,  // status 'available' = siap pakai
            'used' => 0,       // status 'used' = sedang digunakan
            'damaged' => 0,    // status 'damaged'
            'maintenance' => 0, // status 'maintenance'
            'reserved' => 0,   // status 'reserved'
            'repair' => 0,     // status 'repair'
            'lost' => 0,       // status 'lost'
            'total' => 0
        ];

        $serialNumbers = [
            'stock' => [],
            'available' => [],
            'others' => []
        ];

        foreach ($itemDetails as $detail) {
            $status = $detail->status;

            // Count by exact status
            if (isset($quantities[$status])) {
                $quantities[$status]++;
            }

            // Track serial numbers
            if ($status === 'stock') {
                $serialNumbers['stock'][] = $detail->serial_number;
            } elseif ($status === 'available') {
                $serialNumbers['available'][] = $detail->serial_number;
            } else {
                $serialNumbers['others'][] = $detail->serial_number . '(' . $status . ')';
            }

            // Total count (exclude lost/damaged)
            if (!in_array($status, ['lost', 'damaged'])) {
                $quantities['total']++;
            }
        }

        // ✅ FIXED: Proper mapping sesuai konsep Anda
        $result = [
            'stock_status' => $quantities['stock'],        // quantity_available = barang di gudang
            'available_status' => $quantities['available'], // quantity_used = barang siap pakai
            'total_trackable' => $quantities['stock'] + $quantities['available'],
            'detailed' => $quantities,
            'serial_numbers' => $serialNumbers
        ];

        Log::debug('Stock calculation from item details', [
            'item_id' => $item->item_id,
            'total_item_details' => $itemDetails->count(),
            'breakdown_by_status' => $quantities,
            'mapping' => [
                'quantity_available' => $result['stock_status'] . ' (status=stock)',
                'quantity_used' => $result['available_status'] . ' (status=available)'
            ]
        ]);

        return $result;
    }

    /**
     * FIXED: Enhanced validation
     */
    public function validateConsistencyFixed(): array
    {
        try {
            $item = $this->item;
            if (!$item) {
                throw new \Exception('Item not found');
            }

            $actualQuantities = $this->calculateActualQuantitiesFromItemDetailsFixed($item);
            $discrepancies = [];

            // Check stock (quantity_available vs status 'stock')
            if ($this->quantity_available !== $actualQuantities['stock_status']) {
                $discrepancies[] = [
                    'field' => 'quantity_available',
                    'description' => 'Gudang: Stock Table vs Item Details status "stock"',
                    'stock_table' => $this->quantity_available,
                    'item_details' => $actualQuantities['stock_status'],
                    'difference' => $this->quantity_available - $actualQuantities['stock_status']
                ];
            }

            // Check available (quantity_used vs status 'available')
            if ($this->quantity_used !== $actualQuantities['available_status']) {
                $discrepancies[] = [
                    'field' => 'quantity_used',
                    'description' => 'Siap Pakai: Stock Table vs Item Details status "available"',
                    'stock_table' => $this->quantity_used,
                    'item_details' => $actualQuantities['available_status'],
                    'difference' => $this->quantity_used - $actualQuantities['available_status']
                ];
            }

            // Check total
            $expectedTotal = $actualQuantities['stock_status'] + $actualQuantities['available_status'];
            if ($this->total_quantity !== $expectedTotal) {
                $discrepancies[] = [
                    'field' => 'total_quantity',
                    'description' => 'Total: Stock Table vs Expected',
                    'stock_table' => $this->total_quantity,
                    'item_details' => $expectedTotal,
                    'difference' => $this->total_quantity - $expectedTotal
                ];
            }

            return [
                'consistent' => empty($discrepancies),
                'message' => empty($discrepancies) ? 'Stock konsisten' : 'Stock tidak konsisten',
                'actual_from_item_details' => $actualQuantities,
                'current_stock_values' => [
                    'quantity_available' => $this->quantity_available,
                    'quantity_used' => $this->quantity_used,
                    'total_quantity' => $this->total_quantity,
                ],
                'discrepancies' => $discrepancies,
                'recommendations' => empty($discrepancies) ? [] : [
                    'Use syncWithItemDetails() to fix inconsistencies',
                    'Check item_details status values',
                    'Verify transaction logs'
                ]
            ];
        } catch (\Exception $e) {
            return [
                'consistent' => false,
                'message' => 'Validation error: ' . $e->getMessage(),
                'discrepancies' => [['error' => $e->getMessage()]]
            ];
        }
    }

    /**
     * FIXED: Debug method untuk troubleshooting
     */
    public function debugSyncIssues(): array
    {
        try {
            $item = $this->item;
            if (!$item) {
                return ['error' => 'Item not found'];
            }

            // Current stock values
            $currentStock = [
                'quantity_available' => $this->quantity_available,
                'quantity_used' => $this->quantity_used,
                'total_quantity' => $this->total_quantity
            ];

            // Fresh calculation
            $actualQuantities = $this->calculateActualQuantitiesFromItemDetailsFixed($item);

            // Validation
            $validation = $this->validateConsistencyFixed();

            // Recent changes in item_details
            $recentChanges = DB::table('item_details')
                ->where('item_id', $item->item_id)
                ->where('updated_at', '>=', now()->subHours(24))
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get(['detail_id', 'serial_number', 'status', 'updated_at']);

            return [
                'stock_id' => $this->stock_id,
                'item_info' => [
                    'item_id' => $item->item_id,
                    'item_code' => $item->item_code,
                    'item_name' => $item->item_name
                ],
                'current_stock_table' => $currentStock,
                'calculated_from_item_details' => [
                    'stock_status_count' => $actualQuantities['stock_status'],
                    'available_status_count' => $actualQuantities['available_status'],
                    'total_trackable' => $actualQuantities['total_trackable'],
                    'detailed_breakdown' => $actualQuantities['detailed']
                ],
                'consistency_check' => $validation,
                'recent_item_details_changes' => $recentChanges->toArray(),
                'recommendations' => [
                    'consistent' => $validation['consistent'],
                    'action_needed' => !$validation['consistent'] ? 'Run syncWithItemDetails()' : 'No action needed',
                    'sync_command' => '$stock->syncWithItemDetails()'
                ],
                'debug_timestamp' => now()
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'debug_failed' => true
            ];
        }
    }
    /**
     * Calculate quantities berdasarkan status item_details
     * Konsep: stock=gudang (quantity_available), available=siap pakai (quantity_used)
     */
    private function calculateActualQuantitiesFromItemDetails(Item $item): array
    {
        // **CRITICAL: Fresh query untuk memastikan data terbaru**
        $itemDetails = $item->itemDetails()->get(); // Fresh query instead of relationship

        $quantities = [
            'stock' => 0,      // status 'stock' = di gudang
            'available' => 0,  // status 'available' = siap pakai di kantor
            'used' => 0,       // status 'used' = sedang digunakan
            'damaged' => 0,    // status 'damaged'
            'maintenance' => 0, // status 'maintenance'
            'reserved' => 0,   // status 'reserved'
            'total' => 0
        ];

        foreach ($itemDetails as $detail) {
            $status = $detail->status;

            // Count by exact status
            if (isset($quantities[$status])) {
                $quantities[$status]++;
            }

            // Total count (exclude lost/damaged dari total)
            if (!in_array($status, ['lost', 'damaged'])) {
                $quantities['total']++;
            }
        }

        // **DEBUG LOG untuk troubleshooting**
        Log::debug('Stock calculation from item details', [
            'item_id' => $item->item_id,
            'total_item_details' => $itemDetails->count(),
            'breakdown_by_status' => $quantities,
            'mapping' => [
                'quantity_available' => $quantities['stock'] . ' (status=stock)',
                'quantity_used' => $quantities['available'] . ' (status=available)'
            ]
        ]);

        // Return mapping sesuai konsep Anda:
        // quantity_available = barang di gudang (status 'stock')
        // quantity_used = barang siap pakai (status 'available')
        return [
            'stock_status' => $quantities['stock'],        // quantity_available = barang di gudang
            'available_status' => $quantities['available'], // quantity_used = barang siap pakai
            'total_trackable' => $quantities['stock'] + $quantities['available'], // Total yang bisa ditrack
            'detailed' => $quantities // Detail breakdown semua status
        ];
    }

    /**
     * Validate consistency between stock table dan item_details
     */
    public function validateConsistency(): array
    {
        try {
            $item = $this->item;
            if (!$item) {
                throw new \Exception('Item not found');
            }

            $actualQuantities = $this->calculateActualQuantitiesFromItemDetails($item);
            $discrepancies = [];

            // Check stock (quantity_available vs status 'stock')
            if ($this->quantity_available !== $actualQuantities['stock_status']) {
                $discrepancies[] = "Gudang: Stock Table({$this->quantity_available}) vs Item Details Status 'stock'({$actualQuantities['stock_status']})";
            }

            // Check available (quantity_used vs status 'available')
            if ($this->quantity_used !== $actualQuantities['available_status']) {
                $discrepancies[] = "Siap Pakai: Stock Table({$this->quantity_used}) vs Item Details Status 'available'({$actualQuantities['available_status']})";
            }

            // Check total
            $expectedTotal = $actualQuantities['stock_status'] + $actualQuantities['available_status'];
            if ($this->total_quantity !== $expectedTotal) {
                $discrepancies[] = "Total: Stock Table({$this->total_quantity}) vs Expected({$expectedTotal})";
            }

            return [
                'consistent' => empty($discrepancies),
                'message' => empty($discrepancies) ? 'Stock konsisten dengan item details' : 'Stock tidak konsisten',
                'actual_from_item_details' => $actualQuantities,
                'current_stock_values' => [
                    'quantity_available' => $this->quantity_available, // Gudang
                    'quantity_used' => $this->quantity_used,           // Siap pakai
                    'total_quantity' => $this->total_quantity,
                ],
                'discrepancies' => $discrepancies,
                'explanation' => [
                    'quantity_available' => 'Barang di gudang (status stock)',
                    'quantity_used' => 'Barang siap pakai di kantor (status available)',
                    'total_quantity' => 'Total barang yang bisa ditrack'
                ]
            ];
        } catch (\Exception $e) {
            return [
                'consistent' => false,
                'message' => 'Validation error: ' . $e->getMessage(),
                'discrepancies' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Update item_details status berdasarkan perubahan stock
     * Method ini untuk sinkronkan item_details ketika stock diubah manual
     */
    public function syncItemDetailsToStock(int $targetGudang, int $targetSiapPakai, string $reason = 'Stock adjustment'): array
    {
        try {
            $item = $this->item;
            $itemDetails = $item->itemDetails;

            // Hitung current status
            $currentGudang = $itemDetails->where('status', 'stock')->count();
            $currentSiapPakai = $itemDetails->where('status', 'available')->count();

            $updated = 0;
            $changes = [];

            // Jika perlu tambah barang di gudang (pindah dari available ke stock)
            $needMoveToGudang = $targetGudang - $currentGudang;
            if ($needMoveToGudang > 0) {
                $itemsToMove = $itemDetails
                    ->where('status', 'available')
                    ->take($needMoveToGudang);

                foreach ($itemsToMove as $itemDetail) {
                    $itemDetail->update([
                        'status' => 'stock',
                        'location' => 'Warehouse - Stock',
                        'notes' => "Moved to gudang: {$reason}"
                    ]);
                    $updated++;
                    $changes[] = "{$itemDetail->serial_number}: available → stock";
                }
            }

            // Jika perlu tambah barang siap pakai (pindah dari stock ke available)
            $needMoveToSiapPakai = $targetSiapPakai - $currentSiapPakai;
            if ($needMoveToSiapPakai > 0) {
                $itemsToMove = $itemDetails
                    ->where('status', 'stock')
                    ->take($needMoveToSiapPakai);

                foreach ($itemsToMove as $itemDetail) {
                    $itemDetail->update([
                        'status' => 'available',
                        'location' => 'Office - Ready',
                        'notes' => "Moved to siap pakai: {$reason}"
                    ]);
                    $updated++;
                    $changes[] = "{$itemDetail->serial_number}: stock → available";
                }
            }

            // Jika perlu kurangi dari gudang (lebih dari target)
            $needReduceFromGudang = $currentGudang - $targetGudang;
            if ($needReduceFromGudang > 0 && $needMoveToGudang <= 0) {
                $itemsToMove = $itemDetails
                    ->where('status', 'stock')
                    ->take($needReduceFromGudang);

                foreach ($itemsToMove as $itemDetail) {
                    $itemDetail->update([
                        'status' => 'available',
                        'location' => 'Office - Ready',
                        'notes' => "Excess stock moved to siap pakai: {$reason}"
                    ]);
                    $updated++;
                    $changes[] = "{$itemDetail->serial_number}: stock → available (excess)";
                }
            }

            // Jika perlu kurangi dari siap pakai (lebih dari target)
            $needReduceFromSiapPakai = $currentSiapPakai - $targetSiapPakai;
            if ($needReduceFromSiapPakai > 0 && $needMoveToSiapPakai <= 0) {
                $itemsToMove = $itemDetails
                    ->where('status', 'available')
                    ->take($needReduceFromSiapPakai);

                foreach ($itemsToMove as $itemDetail) {
                    $itemDetail->update([
                        'status' => 'stock',
                        'location' => 'Warehouse - Stock',
                        'notes' => "Excess available moved to gudang: {$reason}"
                    ]);
                    $updated++;
                    $changes[] = "{$itemDetail->serial_number}: available → stock (excess)";
                }
            }

            Log::info('Item details synced to match stock values', [
                'stock_id' => $this->stock_id,
                'updated_items' => $updated,
                'target_gudang' => $targetGudang,
                'target_siap_pakai' => $targetSiapPakai,
                'changes' => $changes
            ]);

            return [
                'success' => true,
                'updated_count' => $updated,
                'changes' => $changes,
                'summary' => [
                    'target_gudang' => $targetGudang,
                    'target_siap_pakai' => $targetSiapPakai,
                    'current_gudang_after' => $itemDetails->fresh()->where('status', 'stock')->count(),
                    'current_siap_pakai_after' => $itemDetails->fresh()->where('status', 'available')->count()
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Failed to sync item details to stock: ' . $e->getMessage());
            throw $e;
        }
    }

    // ================================================================
    // EXISTING METHODS (kept as is)
    // ================================================================


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







    // Scope: By category
    public function scopeByCategory($query, $categoryId)
    {
        return $query->whereHas('item', function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId);
        });
    }

    // Static method: Get stock summary
    // public static function getStockSummary(): array
    // {
    //     $total = self::count();
    //     $lowStock = self::lowStock()->count();
    //     $outOfStock = self::outOfStock()->count();
    //     $available = self::available()->count();

    //     return [
    //         'total_items' => $total,
    //         'available_items' => $available,
    //         'low_stock_items' => $lowStock,
    //         'out_of_stock_items' => $outOfStock,
    //         'sufficient_items' => $total - $lowStock - $outOfStock,
    //     ];
    // }

    /**
     * Get stock summary - UPDATED: Available = Sufficient only
     */
    public static function getStockSummary(): array
    {
        $total = self::count();

        // Get detailed stock status for each item
        $stockItems = self::with('item')->get();

        $sufficient = 0;
        $lowStock = 0;
        $outOfStock = 0;

        foreach ($stockItems as $stock) {
            $item = $stock->item;
            $minStock = $item ? $item->min_stock : 0;

            // Determine stock status
            if ($stock->total_quantity == 0 || $stock->quantity_available == 0) {
                // Out of stock
                $outOfStock++;
            } elseif ($stock->quantity_available <= $minStock) {
                // Low stock (available ada tapi <= minimum)
                $lowStock++;
            } else {
                // Sufficient stock (available > minimum)
                $sufficient++;
            }
        }

        return [
            'total_items' => $total,
            'available_items' => $sufficient,           // ✅ CHANGED: Hanya yang sufficient
            'sufficient_items' => $sufficient,          // Sama dengan available
            'low_stock_items' => $lowStock,            // Yang stock rendah (tidak masuk available)
            'out_of_stock_items' => $outOfStock,       // Yang stock habis

            // Additional breakdown untuk debugging
            'breakdown' => [
                'sufficient' => $sufficient,
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
                'total_check' => $sufficient + $lowStock + $outOfStock, // Harus = total
            ]
        ];
    }

    /**
     * Updated available scope - hanya yang sufficient
     */
    public function scopeAvailable($query)
    {
        return $query->whereHas('item', function ($q) {
            $q->whereRaw('stocks.quantity_available > items.min_stock'); // ✅ CHANGED: > min_stock
        })
            ->where('quantity_available', '>', 0);
    }
    /**
     * Enhanced method untuk debugging stock issues
     */
    public static function getDetailedStockAnalysis(): array
    {
        $stockItems = self::with('item')->get();
        $analysis = [
            'sufficient' => [],
            'low_stock' => [],
            'out_of_stock' => [],
            'available' => []
        ];

        foreach ($stockItems as $stock) {
            $item = $stock->item;
            $minStock = $item ? $item->min_stock : 0;

            $stockData = [
                'stock_id' => $stock->stock_id,
                'item_name' => $item->item_name ?? 'Unknown',
                'quantity_available' => $stock->quantity_available,
                'quantity_used' => $stock->quantity_used,
                'total_quantity' => $stock->total_quantity,
                'min_stock' => $minStock,
                'status' => $stock->getStockStatus()['status']
            ];

            // Available check
            if ($stock->quantity_available > 0) {
                $analysis['available'][] = $stockData;
            }

            // Status categorization
            if ($stock->total_quantity == 0 || $stock->quantity_available == 0) {
                $analysis['out_of_stock'][] = $stockData;
            } elseif ($stock->quantity_available <= $minStock) {
                $analysis['low_stock'][] = $stockData;
            } else {
                $analysis['sufficient'][] = $stockData;
            }
        }

        return [
            'counts' => [
                'total' => $stockItems->count(),
                'available' => count($analysis['available']),
                'sufficient' => count($analysis['sufficient']),
                'low_stock' => count($analysis['low_stock']),
                'out_of_stock' => count($analysis['out_of_stock'])
            ],
            'detailed_items' => $analysis,
            'validation' => [
                'total_check' => count($analysis['sufficient']) + count($analysis['low_stock']) + count($analysis['out_of_stock']),
                'should_equal_total' => $stockItems->count()
            ]
        ];
    }

    /**
     * Enhanced scope methods untuk memastikan konsistensi
     */

    // Enhanced lowStock scope
    public function scopeLowStock($query)
    {
        return $query->whereHas('item', function ($q) {
            $q->whereRaw('stocks.quantity_available <= items.min_stock');
        })
            ->where('quantity_available', '>', 0); // Pastikan masih ada stock
    }

    // Enhanced outOfStock scope
    public function scopeOutOfStock($query)
    {
        return $query->where(function ($q) {
            $q->where('total_quantity', '<=', 0)
                ->orWhere('quantity_available', '<=', 0);
        });
    }

    // NEW: Sufficient stock scope
    public function scopeSufficientStock($query)
    {
        return $query->whereHas('item', function ($q) {
            $q->whereRaw('stocks.quantity_available > items.min_stock');
        })
            ->where('quantity_available', '>', 0);
    }

    /**
     * Debug method untuk cek individual stock
     */
    public function debugStockStatus(): array
    {
        $item = $this->item;
        $minStock = $item ? $item->min_stock : 0;

        $status = 'unknown';
        $reason = '';

        if ($this->total_quantity <= 0) {
            $status = 'out_of_stock';
            $reason = 'total_quantity <= 0';
        } elseif ($this->quantity_available <= 0) {
            $status = 'out_of_stock';
            $reason = 'quantity_available <= 0';
        } elseif ($this->quantity_available <= $minStock) {
            $status = 'low_stock';
            $reason = "quantity_available ({$this->quantity_available}) <= min_stock ({$minStock})";
        } else {
            $status = 'sufficient';
            $reason = "quantity_available ({$this->quantity_available}) > min_stock ({$minStock})";
        }

        return [
            'stock_id' => $this->stock_id,
            'item_name' => $item->item_name ?? 'Unknown',
            'quantities' => [
                'available' => $this->quantity_available,
                'used' => $this->quantity_used,
                'total' => $this->total_quantity
            ],
            'min_stock' => $minStock,
            'calculated_status' => $status,
            'reason' => $reason,
            'stock_method_result' => $this->getStockStatus(),
            'scope_results' => [
                'is_low_stock' => $this->isLowStock(),
                'is_out_of_stock' => $this->isOutOfStock()
            ]
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


    // ================================================================
    // SYNC METHODS - Add these to existing Stock.php model
    // ================================================================

    /**
     * Synchronize stock with actual item_details status
     */
    // public function syncWithItemDetails(): array
    // {
    //     try {
    //         $item = $this->item;
    //         if (!$item) {
    //             throw new \Exception('Item not found');
    //         }

    //         // Calculate actual quantities from item_details
    //         $actualQuantities = $this->calculateActualQuantities($item);

    //         // Store old values for logging
    //         $oldValues = [
    //             'quantity_available' => $this->quantity_available,
    //             'quantity_used' => $this->quantity_used,
    //             'total_quantity' => $this->total_quantity,
    //         ];

    //         // Update stock with actual quantities
    //         $this->quantity_available = $actualQuantities['available'];
    //         $this->quantity_used = $actualQuantities['used'];
    //         $this->total_quantity = $actualQuantities['total'];
    //         $this->last_updated = now();
    //         $this->save();

    //         // Log the sync
    //         ActivityLog::logActivity(
    //             'stocks',
    //             $this->stock_id,
    //             'sync',
    //             $oldValues,
    //             [
    //                 'quantity_available' => $this->quantity_available,
    //                 'quantity_used' => $this->quantity_used,
    //                 'total_quantity' => $this->total_quantity,
    //                 'sync_method' => 'auto'
    //             ]
    //         );

    //         return [
    //             'success' => true,
    //             'old_values' => $oldValues,
    //             'new_values' => $actualQuantities,
    //             'changes' => [
    //                 'available_diff' => $actualQuantities['available'] - $oldValues['quantity_available'],
    //                 'used_diff' => $actualQuantities['used'] - $oldValues['quantity_used'],
    //                 'total_diff' => $actualQuantities['total'] - $oldValues['total_quantity'],
    //             ]
    //         ];

    //     } catch (\Exception $e) {
    //         Log::error('Failed to sync stock with item details: ' . $e->getMessage());
    //         return [
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ];
    //     }
    // }

    /**
     * Calculate actual quantities based on item_details status
     */
    private function calculateActualQuantities(Item $item): array
    {
        $itemDetails = $item->itemDetails;

        $quantities = [
            'available' => 0,
            'used' => 0,
            'repair' => 0,
            'lost' => 0,
            'damaged' => 0,
            'maintenance' => 0,
            'reserved' => 0,
            'total' => 0
        ];

        foreach ($itemDetails as $detail) {
            $status = $detail->status;

            // Count by status
            if (isset($quantities[$status])) {
                $quantities[$status]++;
            }

            // Available count (status = 'available')
            if ($status === 'available') {
                // Already counted above
            }

            // Used count (status = 'used', 'repair', 'maintenance', 'reserved')
            elseif (in_array($status, ['used', 'repair', 'maintenance', 'reserved'])) {
                $quantities['used']++;
            }

            // Total count (exclude 'lost' and 'damaged' from total)
            if (!in_array($status, ['lost', 'damaged'])) {
                $quantities['total']++;
            }
        }

        // Return the main quantities needed for stock table
        return [
            'available' => $quantities['available'],
            'used' => $quantities['used'],
            'total' => $quantities['total'],
            'detailed' => $quantities // For detailed reporting
        ];
    }

    /**
     * Validate stock consistency with item_details
     */
    // public function validateConsistency(): array
    // {
    //     try {
    //         $item = $this->item;
    //         if (!$item) {
    //             throw new \Exception('Item not found');
    //         }

    //         $actualQuantities = $this->calculateActualQuantities($item);
    //         $discrepancies = [];

    //         if ($this->quantity_available !== $actualQuantities['available']) {
    //             $discrepancies[] = "Available: Stock({$this->quantity_available}) vs Actual({$actualQuantities['available']})";
    //         }

    //         if ($this->quantity_used !== $actualQuantities['used']) {
    //             $discrepancies[] = "Used: Stock({$this->quantity_used}) vs Actual({$actualQuantities['used']})";
    //         }

    //         if ($this->total_quantity !== $actualQuantities['total']) {
    //             $discrepancies[] = "Total: Stock({$this->total_quantity}) vs Actual({$actualQuantities['total']})";
    //         }

    //         return [
    //             'consistent' => empty($discrepancies),
    //             'message' => empty($discrepancies) ? 'Stock consistent' : 'Stock inconsistent',
    //             'actual_quantities' => $actualQuantities,
    //             'stock_quantities' => [
    //                 'available' => $this->quantity_available,
    //                 'used' => $this->quantity_used,
    //                 'total' => $this->total_quantity,
    //             ],
    //             'discrepancies' => $discrepancies
    //         ];

    //     } catch (\Exception $e) {
    //         return [
    //             'consistent' => false,
    //             'message' => 'Validation error: ' . $e->getMessage(),
    //             'discrepancies' => [$e->getMessage()]
    //         ];
    //     }
    // }

    /**
     * Auto-fix stock inconsistencies
     */
    public function autoFixInconsistency(): array
    {
        $validation = $this->validateConsistency();

        if ($validation['consistent']) {
            return [
                'success' => true,
                'message' => 'Stock already consistent',
                'fixed' => false
            ];
        }

        $syncResult = $this->syncWithItemDetails();

        return [
            'success' => $syncResult['success'],
            'message' => $syncResult['success'] ? 'Stock inconsistency fixed' : 'Failed to fix inconsistency',
            'fixed' => $syncResult['success'],
            'changes' => $syncResult['changes'] ?? []
        ];
    }

    // ================================================================
    // STATIC METHODS FOR BULK OPERATIONS
    // ================================================================

    /**
     * Sync all stocks with their item_details
     */
    public static function syncAllStocks(): array
    {
        $stocks = self::with('item.itemDetails')->get();
        $syncedCount = 0;
        $errors = [];

        foreach ($stocks as $stock) {
            $result = $stock->syncWithItemDetails();

            if ($result['success']) {
                $syncedCount++;
            } else {
                $errors[] = [
                    'stock_id' => $stock->stock_id,
                    'item_name' => $stock->item->item_name ?? 'Unknown',
                    'error' => $result['message'] ?? 'Unknown error'
                ];
            }
        }

        return [
            'success' => true,
            'total_stocks' => $stocks->count(),
            'synced_count' => $syncedCount,
            'errors_count' => count($errors),
            'errors' => $errors,
            'message' => "Synced {$syncedCount} of {$stocks->count()} stocks"
        ];
    }

    /**
     * Get inconsistencies report for all stocks
     */
    public static function getInconsistenciesReport(): array
    {
        $stocks = self::with('item.itemDetails')->get();
        $inconsistencies = [];
        $consistentCount = 0;

        foreach ($stocks as $stock) {
            $validation = $stock->validateConsistency();

            if (!$validation['consistent']) {
                $inconsistencies[] = [
                    'stock_id' => $stock->stock_id,
                    'item_code' => $stock->item->item_code ?? 'N/A',
                    'item_name' => $stock->item->item_name ?? 'Unknown',
                    'validation' => $validation
                ];
            } else {
                $consistentCount++;
            }
        }

        return [
            'total_stocks' => $stocks->count(),
            'consistent_count' => $consistentCount,
            'inconsistent_count' => count($inconsistencies),
            'inconsistencies' => $inconsistencies,
            'consistency_rate' => $stocks->count() > 0 ? round(($consistentCount / $stocks->count()) * 100, 2) : 0,
            'needs_sync' => count($inconsistencies) > 0
        ];
    }

    /**
     * Auto-fix all stock inconsistencies
     */
    public static function autoFixAllInconsistencies(): array
    {
        $report = self::getInconsistenciesReport();

        if ($report['inconsistent_count'] === 0) {
            return [
                'success' => true,
                'message' => 'No inconsistencies found',
                'fixed_count' => 0,
                'total_checked' => $report['total_stocks']
            ];
        }

        $fixedCount = 0;
        $errors = [];

        foreach ($report['inconsistencies'] as $inconsistency) {
            try {
                $stock = self::find($inconsistency['stock_id']);
                if ($stock) {
                    $result = $stock->autoFixInconsistency();
                    if ($result['success'] && $result['fixed']) {
                        $fixedCount++;
                    }
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'stock_id' => $inconsistency['stock_id'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'success' => true,
            'message' => "Fixed {$fixedCount} of {$report['inconsistent_count']} inconsistencies",
            'total_checked' => $report['total_stocks'],
            'inconsistent_found' => $report['inconsistent_count'],
            'fixed_count' => $fixedCount,
            'errors_count' => count($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get stock movement summary for an item
     */
    public function getMovementSummary(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        // Get item details with their transaction history
        $itemDetails = $this->item->itemDetails()
            ->with(['transactionDetails.transaction.createdBy'])
            ->get();

        $movements = [];
        $totalIn = 0;
        $totalOut = 0;

        foreach ($itemDetails as $detail) {
            foreach ($detail->transactionDetails as $transactionDetail) {
                $transaction = $transactionDetail->transaction;

                if ($transaction->transaction_date >= $startDate && $transaction->status === 'approved') {
                    $movementType = $this->getMovementType($transactionDetail->status_before, $transactionDetail->status_after);

                    $movements[] = [
                        'date' => $transaction->transaction_date,
                        'type' => $transaction->transaction_type,
                        'movement' => $movementType,
                        'status_change' => $transactionDetail->status_before . ' → ' . $transactionDetail->status_after,
                        'serial_number' => $detail->serial_number,
                        'created_by' => $transaction->createdBy->full_name ?? 'Unknown',
                        'transaction_number' => $transaction->transaction_number
                    ];

                    if ($movementType === 'in') {
                        $totalIn++;
                    } elseif ($movementType === 'out') {
                        $totalOut++;
                    }
                }
            }
        }

        return [
            'item_id' => $this->item_id,
            'item_name' => $this->item->item_name ?? 'Unknown',
            'period_days' => $days,
            'total_movements' => count($movements),
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'net_movement' => $totalIn - $totalOut,
            'current_stock' => [
                'available' => $this->quantity_available,
                'used' => $this->quantity_used,
                'total' => $this->total_quantity
            ],
            'movements' => collect($movements)->sortByDesc('date')->values()->toArray()
        ];
    }

    /**
     * Determine movement type based on status change
     */
    private function getMovementType(string $statusBefore, string $statusAfter): string
    {
        // In movements (increasing available stock)
        if ($statusBefore !== 'available' && $statusAfter === 'available') {
            return 'in';
        }

        // Out movements (decreasing available stock)
        if ($statusBefore === 'available' && $statusAfter !== 'available') {
            return 'out';
        }

        // Neutral movements (no stock impact)
        return 'neutral';
    }

    /**
     * Get low stock alerts
     */
    public static function getLowStockAlerts(): array
    {
        $lowStockItems = self::lowStock()
            ->with('item')
            ->get()
            ->map(function ($stock) {
                $item = $stock->item;
                return [
                    'stock_id' => $stock->stock_id,
                    'item_code' => $item->item_code ?? 'N/A',
                    'item_name' => $item->item_name ?? 'Unknown',
                    'current_available' => $stock->quantity_available,
                    'min_stock' => $item->min_stock ?? 0,
                    'shortage' => max(0, ($item->min_stock ?? 0) - $stock->quantity_available),
                    'status' => $stock->getStockStatus(),
                    'last_updated' => $stock->last_updated
                ];
            });

        return [
            'total_low_stock' => $lowStockItems->count(),
            'items' => $lowStockItems->toArray(),
            'total_shortage' => $lowStockItems->sum('shortage')
        ];
    }

    /**
     * Get stock dashboard summary
     */
    public static function getDashboardSummary(): array
    {
        $stockSummary = self::getStockSummary();
        $lowStockAlerts = self::getLowStockAlerts();
        $inconsistencies = self::getInconsistenciesReport();

        return [
            'stock_overview' => $stockSummary,
            'low_stock_alerts' => [
                'count' => $lowStockAlerts['total_low_stock'],
                'items' => array_slice($lowStockAlerts['items'], 0, 5) // Top 5 for dashboard
            ],
            'consistency_status' => [
                'consistent_rate' => $inconsistencies['consistency_rate'],
                'needs_sync' => $inconsistencies['needs_sync'],
                'inconsistent_count' => $inconsistencies['inconsistent_count']
            ],
            'recent_movements' => self::getRecentMovements(10)
        ];
    }

    /**
     * Get recent stock movements across all items
     */
    public static function getRecentMovements(int $limit = 20): array
    {
        // This would need to be implemented with a proper stock_movements table
        // For now, we'll return a placeholder
        return [
            'message' => 'Recent movements tracking requires stock_movements table implementation',
            'suggestion' => 'Consider implementing dedicated stock_movements table for detailed movement tracking'
        ];
    }
}
