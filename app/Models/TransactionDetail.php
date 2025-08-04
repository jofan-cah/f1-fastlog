<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class TransactionDetail extends Model
{
    protected $table = 'transaction_details';
    protected $primaryKey = 'transaction_detail_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'transaction_detail_id',
        'transaction_id',
        'item_detail_id',
        'status_before',
        'kondisi_before',
        'status_after',
        'kondisi_after',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ================================================================
    // MODEL EVENTS - AUTO GENERATE ID
    // ================================================================
    protected static function booted()
    {
        static::creating(function ($transactionDetail) {
            if (empty($transactionDetail->transaction_detail_id)) {
                $transactionDetail->transaction_detail_id = self::generateTransactionDetailId();
            }
        });
    }

    // ================================================================
    // RELATIONSHIPS
    // ================================================================
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'transaction_id');
    }

    public function itemDetail(): BelongsTo
    {
        return $this->belongsTo(ItemDetail::class, 'item_detail_id', 'item_detail_id');
    }

    // ================================================================
    // STATUS CHANGE METHODS
    // ================================================================
    public function getStatusChangeInfo(): array
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
            'repair' => [
                'text' => 'Repair',
                'class' => 'bg-yellow-100 text-yellow-800',
                'badge_class' => 'badge-warning'
            ],
            'lost' => [
                'text' => 'Hilang',
                'class' => 'bg-red-100 text-red-800',
                'badge_class' => 'badge-danger'
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

        return [
            'before' => $statuses[$this->status_before] ?? ['text' => $this->status_before, 'class' => 'bg-gray-100 text-gray-800'],
            'after' => $statuses[$this->status_after] ?? ['text' => $this->status_after, 'class' => 'bg-gray-100 text-gray-800'],
        ];
    }

    public function hasStatusChanged(): bool
    {
        return $this->status_before !== $this->status_after;
    }

    public function getChangeDirection(): string
    {
        if (!$this->hasStatusChanged()) {
            return 'none';
        }

        $statusHierarchy = [
            'lost' => 0,
            'damaged' => 1,
            'repair' => 2,
            'maintenance' => 3,
            'used' => 4,
            'reserved' => 5,
            'available' => 6,
        ];

        $beforeLevel = $statusHierarchy[$this->status_before] ?? 3;
        $afterLevel = $statusHierarchy[$this->status_after] ?? 3;

        if ($afterLevel > $beforeLevel) {
            return 'positive';
        } elseif ($afterLevel < $beforeLevel) {
            return 'negative';
        }

        return 'neutral';
    }

    public function getChangeImpact(): array
    {
        $direction = $this->getChangeDirection();

        switch ($direction) {
            case 'positive':
                return [
                    'impact' => 'positive',
                    'text' => 'Peningkatan Status',
                    'icon' => 'fas fa-arrow-right',
                    'class' => 'text-green-600'
                ];
            case 'negative':
                return [
                    'impact' => 'negative',
                    'text' => 'Penurunan Status',
                    'icon' => 'fas fa-arrow-right',
                    'class' => 'text-red-600'
                ];
            case 'neutral':
                return [
                    'impact' => 'neutral',
                    'text' => 'Perubahan Status',
                    'icon' => 'fas fa-exchange-alt',
                    'class' => 'text-blue-600'
                ];
            default:
                return [
                    'impact' => 'none',
                    'text' => 'Tidak Ada Perubahan',
                    'icon' => 'fas fa-minus',
                    'class' => 'text-gray-600'
                ];
        }
    }

    public function getChangeSummary(): string
    {
        if (!$this->hasStatusChanged()) {
            return 'Status tidak berubah';
        }

        $statusInfo = $this->getStatusChangeInfo();
        return "Status berubah dari '{$statusInfo['before']['text']}' menjadi '{$statusInfo['after']['text']}'";
    }

    // ================================================================
    // ITEM & TRANSACTION INFO METHODS
    // ================================================================
    public function getItemInfo(): array
    {
        $itemDetail = $this->itemDetail;
        if (!$itemDetail) {
            return [
                'item_name' => 'Unknown Item',
                'item_code' => 'N/A',
                'serial_number' => 'N/A'
            ];
        }

        return [
            'item_name' => $itemDetail->item->item_name ?? 'Unknown Item',
            'item_code' => $itemDetail->item->item_code ?? 'N/A',
            'serial_number' => $itemDetail->serial_number ?? 'N/A',
            'item_detail_id' => $itemDetail->item_detail_id
        ];
    }

    public function getTransactionType(): string
    {
        return $this->transaction->transaction_type ?? 'Unknown';
    }

    public function getTransactionStatus(): string
    {
        return $this->transaction->status ?? 'Unknown';
    }

    public function isTransactionType(string $type): bool
    {
        return $this->getTransactionType() === $type;
    }

    // ================================================================
    // TRANSACTION WORKFLOW METHODS
    // ================================================================
    public function executeStatusChange(): bool
    {
        try {
            $itemDetail = $this->itemDetail;
            if (!$itemDetail) {
                throw new \Exception('Item detail not found');
            }

            $oldStatus = $itemDetail->status;
            $newStatus = $this->getExpectedStatusAfter();

            $itemDetail->status = $newStatus;
            $itemDetail->save();

            $this->status_before = $oldStatus;
            $this->status_after = $newStatus;
            $this->save();

            ActivityLog::logActivity(
                'item_details',
                $itemDetail->item_detail_id,
                'status_change',
                ['status' => $oldStatus],
                ['status' => $newStatus, 'via_transaction' => $this->transaction_id]
            );

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to execute status change: ' . $e->getMessage());
            return false;
        }
    }

    private function getExpectedStatusAfter(): string
    {
        $transaction = $this->transaction;
        if (!$transaction) {
            return $this->itemDetail->status;
        }

        switch ($transaction->transaction_type) {
            case Transaction::TYPE_OUT:
                return 'used';
            case Transaction::TYPE_IN:
            case Transaction::TYPE_RETURN:
                return 'available';
            case Transaction::TYPE_REPAIR:
                return 'repair';
            case Transaction::TYPE_LOST:
                return 'lost';
            default:
                return $this->itemDetail->status;
        }
    }

    public function rollbackStatusChange(): bool
    {
        try {
            $itemDetail = $this->itemDetail;
            if (!$itemDetail || !$this->status_before) {
                return true;
            }

            $itemDetail->status = $this->status_before;
            $itemDetail->save();

            $this->status_after = null;
            $this->save();

            ActivityLog::logActivity(
                'item_details',
                $itemDetail->item_detail_id,
                'status_rollback',
                ['status' => $itemDetail->status],
                ['status' => $this->status_before, 'transaction_rollback' => $this->transaction_id]
            );

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to rollback status change: ' . $e->getMessage());
            return false;
        }
    }

    // ================================================================
    // REPORTING METHODS
    // ================================================================
    public function toHistoryEntry(): array
    {
        $itemInfo = $this->getItemInfo();
        $changeImpact = $this->getChangeImpact();
        $statusInfo = $this->getStatusChangeInfo();

        return [
            'id' => $this->transaction_detail_id,
            'transaction_id' => $this->transaction_id,
            'transaction_type' => $this->getTransactionType(),
            'transaction_status' => $this->getTransactionStatus(),
            'item_name' => $itemInfo['item_name'],
            'item_code' => $itemInfo['item_code'],
            'serial_number' => $itemInfo['serial_number'],
            'status_before' => $this->status_before,
            'status_after' => $this->status_after,
            'status_info' => $statusInfo,
            'change_impact' => $changeImpact,
            'change_summary' => $this->getChangeSummary(),
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'created_by' => $this->transaction->createdBy->full_name ?? 'Unknown',
            'approved_by' => $this->transaction->approvedBy->full_name ?? null,
            'approved_date' => $this->transaction->approved_date,
        ];
    }

    public function getSummaryForReport(): array
    {
        $itemInfo = $this->getItemInfo();
        $statusInfo = $this->getStatusChangeInfo();
        $changeImpact = $this->getChangeImpact();

        return [
            'transaction_detail_id' => $this->transaction_detail_id,
            'transaction_info' => [
                'id' => $this->transaction_id,
                'number' => $this->transaction->transaction_number ?? 'N/A',
                'type' => $this->transaction->transaction_type ?? 'Unknown',
                'status' => $this->transaction->status ?? 'Unknown',
                'date' => $this->transaction->transaction_date ?? null,
                'approved_date' => $this->transaction->approved_date ?? null,
            ],
            'item_info' => $itemInfo,
            'status_change' => [
                'before' => $this->status_before,
                'after' => $this->status_after,
                'changed' => $this->hasStatusChanged(),
                'summary' => $this->getChangeSummary(),
                'impact' => $changeImpact,
                'direction' => $this->getChangeDirection()
            ],
            'audit_info' => [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'created_by' => $this->transaction->createdBy->full_name ?? 'Unknown',
                'approved_by' => $this->transaction->approvedBy->full_name ?? null,
            ],
            'notes' => $this->notes
        ];
    }

    public function getTimelineEntry(): array
    {
        $statusInfo = $this->getStatusChangeInfo();
        $changeImpact = $this->getChangeImpact();

        return [
            'id' => $this->transaction_detail_id,
            'date' => $this->created_at,
            'title' => $this->getChangeSummary(),
            'description' => $this->notes ?: 'No additional notes',
            'type' => $changeImpact['impact'],
            'icon' => $changeImpact['icon'],
            'color_class' => $changeImpact['class'],
            'transaction_info' => [
                'number' => $this->transaction->transaction_number ?? 'N/A',
                'type' => $this->transaction->getTypeInfo() ?? [],
                'created_by' => $this->transaction->createdBy->full_name ?? 'Unknown'
            ],
            'status_badges' => [
                'before' => $statusInfo['before'],
                'after' => $statusInfo['after']
            ]
        ];
    }

    // ================================================================
    // ANALYSIS METHODS
    // ================================================================
    public function isCriticalChange(): bool
    {
        $criticalTransitions = [
            'available_to_lost',
            'available_to_damaged',
            'used_to_lost',
            'repair_to_lost',
        ];

        $transition = $this->status_before . '_to_' . $this->status_after;

        return in_array($transition, $criticalTransitions) ||
               $this->status_after === 'lost' ||
               $this->status_after === 'damaged';
    }

    public function getStockImpact(): array
    {
        $impact = [
            'available_change' => 0,
            'used_change' => 0,
            'total_change' => 0,
            'description' => 'No stock impact'
        ];

        if (!$this->hasStatusChanged()) {
            return $impact;
        }

        // From available
        if ($this->status_before === 'available') {
            if (in_array($this->status_after, ['used', 'repair'])) {
                $impact['available_change'] = -1;
                $impact['used_change'] = 1;
                $impact['description'] = 'Moved from available to used';
            } elseif ($this->status_after === 'lost') {
                $impact['available_change'] = -1;
                $impact['total_change'] = -1;
                $impact['description'] = 'Item lost - removed from inventory';
            }
        }
        // To available
        elseif ($this->status_after === 'available') {
            if (in_array($this->status_before, ['used', 'repair'])) {
                $impact['available_change'] = 1;
                $impact['used_change'] = -1;
                $impact['description'] = 'Returned to available stock';
            }
        }
        // Lost to available (recovery)
        elseif ($this->status_before === 'lost' && $this->status_after === 'available') {
            $impact['available_change'] = 1;
            $impact['total_change'] = 1;
            $impact['description'] = 'Lost item recovered';
        }

        return $impact;
    }

    // ================================================================
    // PERMISSION METHODS
    // ================================================================
    public function canUserView(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        $levelName = strtolower($user->getUserLevel()->level_name ?? '');

        if (in_array($levelName, ['admin', 'logistik'])) {
            return true;
        }

        if ($levelName === 'teknisi') {
            return $this->transaction->created_by === $user->user_id;
        }

        return false;
    }

    // ================================================================
    // SCOPES
    // ================================================================
    public function scopeByTransaction($query, $transactionId)
    {
        return $query->where('transaction_id', $transactionId);
    }

    public function scopeByItemDetail($query, $itemDetailId)
    {
        return $query->where('item_detail_id', $itemDetailId);
    }

    public function scopeByStatusBefore($query, $status)
    {
        return $query->where('status_before', $status);
    }

    public function scopeByStatusAfter($query, $status)
    {
        return $query->where('status_after', $status);
    }

    public function scopeStatusChanged($query)
    {
        return $query->whereColumn('status_before', '!=', 'status_after');
    }

    public function scopePositiveChanges($query)
    {
        return $query->statusChanged()->where(function($q) {
            $q->where(function($q2) {
                $q2->where('status_after', 'available')
                   ->whereIn('status_before', ['used', 'repair', 'maintenance']);
            })->orWhere(function($q2) {
                $q2->whereIn('status_before', ['lost', 'damaged'])
                   ->whereNotIn('status_after', ['lost', 'damaged']);
            });
        });
    }

    public function scopeNegativeChanges($query)
    {
        return $query->statusChanged()->where(function($q) {
            $q->where(function($q2) {
                $q2->whereIn('status_after', ['lost', 'damaged']);
            })->orWhere(function($q2) {
                $q2->where('status_before', 'available')
                   ->whereIn('status_after', ['used', 'repair', 'maintenance']);
            });
        });
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ================================================================
    // STATIC METHODS
    // ================================================================
    public static function generateTransactionDetailId(): string
    {
        $lastDetail = self::orderBy('transaction_detail_id', 'desc')->first();
        $lastNumber = $lastDetail ? (int) substr($lastDetail->transaction_detail_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        return 'TXD' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
    }

    public static function getStatusChangeStats(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $stats = self::where('created_at', '>=', $startDate)
            ->statusChanged()
            ->get()
            ->groupBy(function($detail) {
                return $detail->status_before . '_to_' . $detail->status_after;
            })
            ->map(function($group) {
                return $group->count();
            })
            ->toArray();

        return [
            'total_status_changes' => self::where('created_at', '>=', $startDate)->statusChanged()->count(),
            'positive_changes' => self::where('created_at', '>=', $startDate)->positiveChanges()->count(),
            'negative_changes' => self::where('created_at', '>=', $startDate)->negativeChanges()->count(),
            'detailed_changes' => $stats,
        ];
    }

    public static function getMostCommonTransitions(int $limit = 10): array
    {
        return self::statusChanged()
            ->selectRaw('status_before, status_after, COUNT(*) as count')
            ->groupBy(['status_before', 'status_after'])
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($item) {
                return [
                    'from' => $item->status_before,
                    'to' => $item->status_after,
                    'count' => $item->count,
                    'transition' => $item->status_before . ' → ' . $item->status_after
                ];
            })->toArray();
    }

    public static function getStatusChangeTrends(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $trends = self::where('created_at', '>=', $startDate)
            ->statusChanged()
            ->selectRaw('
                DATE(created_at) as date,
                status_before,
                status_after,
                COUNT(*) as count
            ')
            ->groupBy(['date', 'status_before', 'status_after'])
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        $formattedTrends = [];
        foreach ($trends as $date => $changes) {
            $formattedTrends[$date] = [
                'date' => $date,
                'total_changes' => $changes->sum('count'),
                'changes_detail' => $changes->map(function($change) {
                    return [
                        'from' => $change->status_before,
                        'to' => $change->status_after,
                        'count' => $change->count
                    ];
                })->toArray()
            ];
        }

        return $formattedTrends;
    }

    public static function getCriticalChangesReport(int $days = 7): array
    {
        $startDate = now()->subDays($days);

        $criticalChanges = self::where('created_at', '>=', $startDate)
            ->where(function($query) {
                $query->where('status_after', 'lost')
                      ->orWhere('status_after', 'damaged')
                      ->orWhere(function($q) {
                          $q->where('status_before', 'available')
                            ->whereIn('status_after', ['lost', 'damaged']);
                      });
            })
            ->with(['transaction.createdBy', 'itemDetail.item'])
            ->get();

        return [
            'period_days' => $days,
            'total_critical_changes' => $criticalChanges->count(),
            'lost_items' => $criticalChanges->where('status_after', 'lost')->count(),
            'damaged_items' => $criticalChanges->where('status_after', 'damaged')->count(),
            'details' => $criticalChanges->map(function($detail) {
                return $detail->getSummaryForReport();
            })->toArray()
        ];
    }

    public static function getEfficiencyMetrics(): array
    {
        $returnTimeQuery = self::where('status_before', 'used')
            ->where('status_after', 'available')
            ->with('transaction')
            ->get();

        $repairItems = self::where('status_after', 'repair')
            ->whereHas('transaction', function($query) {
                $query->where('status', Transaction::STATUS_APPROVED);
            })
            ->count();

        return [
            'items_in_repair' => $repairItems,
            'recent_returns' => $returnTimeQuery->count(),
            'repair_completion_rate' => 'Requires implementation with repair completion tracking',
            'utilization_efficiency' => 'Requires implementation with usage duration tracking'
        ];
    }


    /**
     * Get transaction warnings
     */
    public function getTransactionWarnings()
    {
        $warnings = [];
        $currentStatus = $this->status_before;
        $expectedStatus = $this->getExpectedStatusAfter();

        // Check for potential issues
        if ($currentStatus === 'lost' && $expectedStatus !== 'lost') {
            $warnings[] = 'Attempting to change status of lost item';
        }

        if ($currentStatus === 'damaged' && !in_array($expectedStatus, ['repair', 'lost'])) {
            $warnings[] = 'Damaged item should be repaired or marked as lost';
        }

        if ($this->transaction->transaction_type === 'OUT' && $currentStatus !== 'available') {
            $warnings[] = 'Item is not available for checkout';
        }

        if ($this->transaction->transaction_type === 'IN' && $currentStatus === 'available') {
            $warnings[] = 'Item is already available';
        }

        return $warnings;
    }

    /**
     * Get location change summary
     */
    public function getLocationChangeSummary()
    {
        return [
            'from_location' => $this->location_before ?? $this->itemDetail->location ?? 'Unknown',
            'to_location' => $this->location_after ?? $this->transaction->to_location ?? 'Unknown',
            'location_changed' => ($this->location_before ?? $this->itemDetail->location) !== ($this->location_after ?? $this->transaction->to_location)
        ];
    }
}
