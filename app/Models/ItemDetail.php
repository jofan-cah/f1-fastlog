<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Transaction;

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
        'kondisi'
    ];

    protected $casts = [
        'custom_attributes' => 'array',
    ];
    const STATUS_DAMAGED = 'damaged';


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
            'stock' => [
                'text' => 'Stock',
                'class' => 'bg-gray-100 text-gray-800',
                'badge_class' => 'badge-info'
            ],
        ];

        return $statuses[$this->status] ?? $statuses['available'];
    }

    // Helper methods untuk damaged
    public function isDamaged(): bool
    {
        return $this->status === self::STATUS_DAMAGED;
    }

    public function canBeRepaired(): bool
    {
        return in_array($this->status, [self::STATUS_DAMAGED, 'repair']);
    }

    public function getDamageInfo(): ?array
    {
        if (!$this->isDamaged()) {
            return null;
        }

        // Cari transaction DAMAGED terakhir untuk item ini
        $damageTransaction = Transaction::where('transaction_type', Transaction::TYPE_DAMAGED)
            ->whereHas('transactionDetails', function ($query) {
                $query->where('item_detail_id', $this->item_detail_id);
            })
            ->latest()
            ->first();

        if (!$damageTransaction) {
            return [
                'damage_level' => 'unknown',
                'damage_reason' => 'unknown',
                'damage_date' => null,
                'repair_estimate' => null
            ];
        }

        return [
            'damage_level' => $damageTransaction->damage_level,
            'damage_reason' => $damageTransaction->damage_reason,
            'damage_date' => $damageTransaction->transaction_date,
            'repair_estimate' => $damageTransaction->repair_estimate,
            'transaction_id' => $damageTransaction->transaction_id,
            'level_info' => $damageTransaction->getDamageLevelInfo()
        ];
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
            ->with(['transaction.createdBy', 'transaction.approvedBy'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($detail) {
                $transaction = $detail->transaction;
                $typeInfo = $transaction->getTypeInfo();

                return [
                    // EXISTING FORMAT (tidak berubah - kompatibel dengan view lama)
                    'date' => $detail->created_at->format('Y-m-d H:i'),
                    'action' => $detail->transaction->transaction_type,
                    'user' => $detail->transaction->createdBy->full_name ?? 'Unknown',
                    'status_before' => $detail->status_before,
                    'status_after' => $detail->status_after,
                    'notes' => $detail->notes,

                    // NEW ENHANCED FIELDS (tambahan untuk view baru)
                    'transaction_id' => $transaction->transaction_id,
                    'transaction_number' => $transaction->transaction_number,
                    'transaction_status' => $transaction->status,
                    'approved_date' => $transaction->approved_date?->format('Y-m-d H:i'),
                    'approved_by' => $transaction->approvedBy->full_name ?? null,
                    'relative_time' => $detail->created_at->diffForHumans(),

                    // Type & Status Info (dengan icon dan class)
                    'action_text' => $typeInfo['text'], // "Barang Keluar" instead of "OUT"
                    'action_icon' => $typeInfo['icon'], // "fas fa-arrow-up"
                    'action_class' => $typeInfo['class'], // "bg-blue-100 text-blue-800"

                    // Kondisi info (jika ada)
                    'kondisi_before' => $detail->kondisi_before ?? null,
                    'kondisi_after' => $detail->kondisi_after ?? null,
                    'kondisi_changed' => ($detail->kondisi_before ?? '') !== ($detail->kondisi_after ?? ''),

                    // Location info
                    'from_location' => $transaction->from_location,
                    'to_location' => $transaction->to_location,
                    'location_changed' => $transaction->from_location !== $transaction->to_location,

                    // Enhanced notes
                    'transaction_notes' => $transaction->notes,
                    'detail_notes' => $detail->notes,
                    'all_notes' => trim(($transaction->notes ?? '') . ' ' . ($detail->notes ?? '')),

                    // Status summary
                    'status_summary' => $this->generateStatusSummary($detail),
                    'is_critical' => in_array($detail->status_after, ['lost', 'damaged']) ||
                        ($detail->kondisi_after ?? '') === 'no_good',
                ];
            })
            ->toArray();
    }

    /**
     * Helper method: Generate status summary
     */
    private function generateStatusSummary($detail): string
    {
        $parts = [];

        // Status change
        if ($detail->status_before && $detail->status_after && $detail->status_before !== $detail->status_after) {
            $parts[] = "Status: {$detail->status_before} → {$detail->status_after}";
        }

        // Kondisi change
        if (($detail->kondisi_before ?? '') !== ($detail->kondisi_after ?? '') && $detail->kondisi_after) {
            $parts[] = "Kondisi: " . ($detail->kondisi_before ?? 'unknown') . " → {$detail->kondisi_after}";
        }

        return implode(', ', $parts) ?: 'No changes';
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



    // Static method: Generate serial number
    public static function generateSerialNumber(string $itemCode): string
    {
        $year = date('Y');
        $lastSerial = self::whereHas('item', function ($q) use ($itemCode) {
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

    // ================================================================
    // QR & TRANSACTION METHODS - Add these to existing ItemDetail.php model
    // ================================================================

    /**
     * Generate QR content for transaction
     */
    public function generateQRForTransaction(): array
    {
        return [
            'type' => 'item_detail',
            'item_detail_id' => $this->item_detail_id,
            'serial_number' => $this->serial_number,
            'item_id' => $this->item_id,
            'item_code' => $this->item->item_code ?? '',
            'item_name' => $this->item->item_name ?? '',
            'current_status' => $this->status,
            'location' => $this->location,
            'generated_at' => now()->toISOString(),
            'api_url' => url("/item-details/scan/{$this->item_detail_id}"),
            'view_url' => url("/item-details/{$this->item_detail_id}"),
            'transaction_ready' => $this->isTransactionReady()
        ];
    }

    /**
     * Check if item is ready for transaction
     */
    public function isTransactionReady(): bool
    {
        return !in_array($this->status, ['lost', 'damaged']);
    }

    /**
     * Get available transaction types for this item
     */
    public function getAvailableTransactionTypes(): array
    {
        $availableTypes = [];
        $userAllowedTypes = Transaction::getUserAllowedTypes();

        switch ($this->status) {
            case 'available':
                $availableTypes = [
                    Transaction::TYPE_OUT,
                    Transaction::TYPE_REPAIR,
                    Transaction::TYPE_LOST
                ];
                break;

            case 'used':
                $availableTypes = [
                    Transaction::TYPE_IN,
                    Transaction::TYPE_RETURN,
                    Transaction::TYPE_REPAIR,
                    Transaction::TYPE_LOST
                ];
                break;

            case 'repair':
                $availableTypes = [
                    Transaction::TYPE_IN,
                    Transaction::TYPE_RETURN,
                    Transaction::TYPE_LOST
                ];
                break;

            default:
                $availableTypes = [Transaction::TYPE_LOST];
                break;
        }

        // Filter by user permissions
        return array_intersect($availableTypes, $userAllowedTypes);
    }


    public function getStatusPriority(): int
    {
        $priorities = [
            'available' => 1,
            'reserved' => 2,
            'used' => 3,
            'maintenance' => 4,
            'repair' => 5,
            'damaged' => 6,
            'lost' => 7
        ];

        return $priorities[$this->status] ?? 8;
    }


    /**
     * Check if item can be moved to specific status
     */
    public function canMoveToStatus(string $targetStatus): bool
    {
        $allowedTransitions = [
            'available' => ['used', 'repair', 'maintenance', 'reserved', 'lost', 'damaged'],
            'used' => ['available', 'repair', 'maintenance', 'lost', 'damaged'],
            'repair' => ['available', 'used', 'damaged', 'lost'],
            'maintenance' => ['available', 'used', 'repair', 'damaged', 'lost'],
            'reserved' => ['available', 'used', 'lost', 'damaged'],
            'damaged' => ['repair', 'lost'],
            'lost' => [] // Cannot transition from lost
        ];

        return in_array($targetStatus, $allowedTransitions[$this->status] ?? []);
    }

    public function getAvailableTransactionTypesWithLabels(): array
    {
        $availableTypes = $this->getAvailableTransactionTypes();
        $allTypes = Transaction::getTransactionTypes();

        $result = [];
        foreach ($availableTypes as $type) {
            if (isset($allTypes[$type])) {
                $result[$type] = $allTypes[$type];
            }
        }

        return $result;
    }

    /**
     * Create transaction from QR scan
     */
    public function createTransactionFromScan(string $transactionType, array $additionalData = []): array
    {
        $qrData = $this->generateQRForTransaction();

        $transactionData = array_merge([
            'transaction_type' => $transactionType,
            'from_location' => $this->location,
            'notes' => 'Created from QR scan'
        ], $additionalData);

        return Transaction::createFromQRScan($qrData, $transactionData);
    }

    /**
     * Get transaction history for this item
     */
    public function getTransactionHistory(): array
    {
        return $this->transactionDetails()
            ->with(['transaction.createdBy', 'transaction.approvedBy'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($detail) {
                return $detail->toHistoryEntry();
            })->toArray();
    }

    /**
     * Get pending transactions for this item
     */
    public function getPendingTransactions(): array
    {
        return $this->transactionDetails()
            ->whereHas('transaction', function ($query) {
                $query->where('status', Transaction::STATUS_PENDING);
            })
            ->with(['transaction.createdBy'])
            ->get()
            ->map(function ($detail) {
                $transaction = $detail->transaction;
                return [
                    'id' => $transaction->transaction_id,
                    'number' => $transaction->transaction_number,
                    'type' => $transaction->getTypeInfo(),
                    'notes' => $transaction->notes,
                    'created_by' => $transaction->createdBy->full_name ?? 'Unknown',
                    'created_at' => $transaction->created_at,
                    'can_approve' => Transaction::canUserApprove()
                ];
            })->toArray();
    }

    /**
     * Check if item has pending transactions
     */
    public function hasPendingTransactions(): bool
    {
        return $this->transactionDetails()
            ->whereHas('transaction', function ($query) {
                $query->where('status', Transaction::STATUS_PENDING);
            })
            ->exists();
    }

    /**
     * Get last transaction for this item
     */
    public function getLastTransaction(): ?array
    {
        $lastDetail = $this->transactionDetails()
            ->with(['transaction.createdBy', 'transaction.approvedBy'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastDetail) {
            return null;
        }

        return $lastDetail->toHistoryEntry();
    }

    /**
     * Check if status can be changed to target status
     */
    public function canChangeStatusTo(string $targetStatus): bool
    {
        $allowedTransitions = [
            'available' => ['used', 'repair', 'lost', 'damaged'],
            'used' => ['available', 'repair', 'lost', 'damaged'],
            'repair' => ['available', 'used', 'lost', 'damaged'],
            'lost' => [], // Cannot transition from lost
            'damaged' => ['repair'], // Can only repair damaged items
            'maintenance' => ['available', 'repair'],
            'reserved' => ['available', 'used']
        ];

        return in_array($targetStatus, $allowedTransitions[$this->status] ?? []);
    }

    /**
     * Update status with transaction tracking
     */
    public function updateStatusWithTransaction(string $newStatus, string $transactionType, array $transactionData = []): array
    {
        if (!$this->canChangeStatusTo($newStatus)) {
            return [
                'success' => false,
                'message' => "Cannot change status from '{$this->status}' to '{$newStatus}'"
            ];
        }

        // Create transaction for this status change
        $result = $this->createTransactionFromScan($transactionType, array_merge([
            'notes' => "Status change: {$this->status} → {$newStatus}"
        ], $transactionData));

        return $result;
    }

    /**
     * Get item utilization rate
     */
    public function getUtilizationRate(int $days = 30): array
    {
        $totalDays = $days;
        $usedDays = 0;

        // Get transaction history for the period
        $startDate = now()->subDays($days);
        $transactions = $this->transactionDetails()
            ->whereHas('transaction', function ($query) use ($startDate) {
                $query->where('status', Transaction::STATUS_APPROVED)
                    ->where('approved_date', '>=', $startDate);
            })
            ->with('transaction')
            ->orderBy('created_at')
            ->get();

        // Calculate days in 'used' status
        $currentStatus = 'available'; // Assume starting status
        $lastDate = $startDate;

        foreach ($transactions as $detail) {
            $transactionDate = $detail->transaction->approved_date;

            // If was in 'used' status, count the days
            if ($currentStatus === 'used') {
                $usedDays += $lastDate->diffInDays($transactionDate);
            }

            $currentStatus = $detail->status_after;
            $lastDate = $transactionDate;
        }

        // Count remaining days if currently in 'used' status
        if ($currentStatus === 'used') {
            $usedDays += $lastDate->diffInDays(now());
        }

        $utilizationRate = $totalDays > 0 ? round(($usedDays / $totalDays) * 100, 2) : 0;

        return [
            'total_days' => $totalDays,
            'used_days' => $usedDays,
            'available_days' => $totalDays - $usedDays,
            'utilization_rate' => $utilizationRate,
            'current_status' => $this->status,
            'is_currently_used' => $this->status === 'used'
        ];
    }

    // ================================================================
    // STATIC METHODS FOR BULK OPERATIONS
    // ================================================================

    /**
     * Get items available for specific transaction type
     */
    public static function getAvailableForTransaction(string $transactionType): array
    {
        $query = self::with(['item', 'item.stock']);

        switch ($transactionType) {
            case Transaction::TYPE_OUT:
                $query->where('status', 'available');
                break;
            case Transaction::TYPE_IN:
            case Transaction::TYPE_RETURN:
                $query->whereIn('status', ['used', 'repair']);
                break;
            case Transaction::TYPE_REPAIR:
                $query->whereIn('status', ['available', 'used']);
                break;
            case Transaction::TYPE_LOST:
                $query->where('status', '!=', 'lost');
                break;
        }

        return $query->get()->map(function ($itemDetail) {
            return [
                'item_detail_id' => $itemDetail->item_detail_id,
                'serial_number' => $itemDetail->serial_number,
                'item_name' => $itemDetail->item->item_name ?? 'Unknown',
                'item_code' => $itemDetail->item->item_code ?? 'N/A',
                'current_status' => $itemDetail->status,
                'location' => $itemDetail->location,
                'qr_data' => $itemDetail->generateQRForTransaction()
            ];
        })->toArray();
    }

    /**
     * Generate bulk QR codes for multiple items
     */
    public static function generateBulkQRCodes(array $itemDetailIds): array
    {
        $items = self::whereIn('item_detail_id', $itemDetailIds)
            ->with('item')
            ->get();

        return $items->map(function ($itemDetail) {
            return [
                'item_detail_id' => $itemDetail->item_detail_id,
                'serial_number' => $itemDetail->serial_number,
                'item_name' => $itemDetail->item->item_name ?? 'Unknown',
                'qr_content' => json_encode($itemDetail->generateQRForTransaction()),
                'qr_data' => $itemDetail->generateQRForTransaction()
            ];
        })->toArray();
    }

    /**
     * Get items requiring attention (pending transactions, repairs, etc.)
     */
    public static function getItemsRequiringAttention(): array
    {
        return [
            'pending_transactions' => self::whereHas('transactionDetails.transaction', function ($query) {
                $query->where('status', Transaction::STATUS_PENDING);
            })->with(['item', 'transactionDetails.transaction'])->get()->map(function ($item) {
                return [
                    'item_detail_id' => $item->item_detail_id,
                    'serial_number' => $item->serial_number,
                    'item_name' => $item->item->item_name ?? 'Unknown',
                    'status' => $item->status,
                    'pending_count' => $item->getPendingTransactions()
                ];
            })->toArray(),

            'repair_items' => self::where('status', 'repair')
                ->with('item')
                ->get()
                ->map(function ($item) {
                    return [
                        'item_detail_id' => $item->item_detail_id,
                        'serial_number' => $item->serial_number,
                        'item_name' => $item->item->item_name ?? 'Unknown',
                        'location' => $item->location,
                        'last_transaction' => $item->getLastTransaction()
                    ];
                })->toArray(),

            'lost_items' => self::where('status', 'lost')
                ->with('item')
                ->get()
                ->map(function ($item) {
                    return [
                        'item_detail_id' => $item->item_detail_id,
                        'serial_number' => $item->serial_number,
                        'item_name' => $item->item->item_name ?? 'Unknown',
                        'last_known_location' => $item->location,
                        'last_transaction' => $item->getLastTransaction()
                    ];
                })->toArray()
        ];
    }

    public static function generateItemDetailId($itemId = null): string
    {
        if ($itemId) {
            $item = Item::with('category')->find($itemId);
            $prefix = ($item->category->code_category ?? 'XXX') . ($item->item_code ?? 'XXX');
        } else {
            $prefix = 'UNKNOWN';
        }

        // Cari nomor terakhir dengan prefix yang sama
        $lastDetail = self::where('item_detail_id', 'like', $prefix . '%')
            ->orderBy('item_detail_id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastDetail) {
            $lastNumber = (int) substr($lastDetail->item_detail_id, -5);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    // ================================================================
    // Update di Controller jadi lebih simpel
    // ================================================================

    private function generateItemDetailsForReceived(GoodsReceivedDetail $grDetail, array $itemData, array $serialNumbers = [])
    {
        $quantityReceived = (int)$itemData['quantity_received'];
        $item = Item::find($itemData['item_id']);
        $itemCode = $item->item_code;

        $itemsGenerated = 0;

        for ($i = 1; $i <= $quantityReceived; $i++) {
            $serialNumber = $this->getSerialNumber($serialNumbers, $i - 1, $itemCode, $i);

            ItemDetail::create([
                'item_detail_id' => ItemDetail::generateItemDetailId($itemData['item_id']),
                'gr_detail_id' => $grDetail->gr_detail_id,
                'item_id' => $itemData['item_id'],
                'serial_number' => $serialNumber,
                'custom_attributes' => null,
                'qr_code' => null,
                'status' => 'available',
                'location' => 'Warehouse - Stock',
                'notes' => "Received from GR: {$grDetail->gr_detail_id}",
            ]);

            $itemsGenerated++;
        }

        return $itemsGenerated;
    }
}
