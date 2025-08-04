<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'transaction_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'transaction_id',
        'transaction_number',
        'transaction_type',
        'reference_id',
        'reference_type',
        'item_id',
        'quantity',
        'from_location',
        'to_location',
        'notes',
        'status',
        'created_by',
        'approved_by',
        'transaction_date',
        'approved_date',
        'damage_level',        // 🆕 BARU
        'damage_reason',       // 🆕 BARU
        'repair_estimate',     // 🆕 BARU
    ];

    protected $casts = [
        'quantity' => 'integer',
        'transaction_date' => 'datetime',
        'approved_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'repair_estimate' => 'decimal:2',  // 🆕 BARU
    ];

    // Transaction types constants
    const TYPE_IN = 'IN';
    const TYPE_OUT = 'OUT';
    const TYPE_REPAIR = 'REPAIR';
    const TYPE_LOST = 'LOST';
    const TYPE_RETURN = 'RETURN';
    const TYPE_DAMAGED = 'DAMAGED';


    // Transaction statuses constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const DAMAGE_LEVELS = [
        'light' => 'Ringan',
        'medium' => 'Sedang',
        'heavy' => 'Berat',
        'total' => 'Total'
    ];

    const DAMAGE_REASONS = [
        'accident' => 'Kecelakaan/Terjatuh',
        'wear' => 'Keausan Normal',
        'misuse' => 'Pemakaian Salah',
        'environment' => 'Faktor Lingkungan',
        'manufacturing' => 'Cacat Produksi',
        'electrical' => 'Kerusakan Elektrik',
        'mechanical' => 'Kerusakan Mekanik',
        'water_damage' => 'Kerusakan Air',
        'other' => 'Lainnya'
    ];

    // ================================================================
    // MODEL EVENTS - AUTO SYNC WHEN APPROVED
    // ================================================================
    protected static function booted()
    {
        // Auto-execute transaction when approved
        static::updated(function ($transaction) {
            if ($transaction->wasChanged('status') && $transaction->status === self::STATUS_APPROVED) {
                $transaction->executeTransactionSync();
            }
        });

        // Auto-generate IDs when creating
        static::creating(function ($transaction) {
            if (empty($transaction->transaction_id)) {
                $transaction->transaction_id = self::generateTransactionId();
            }
            if (empty($transaction->transaction_number)) {
                $transaction->transaction_number = self::generateTransactionNumber($transaction->transaction_type);
            }
        });
    }

    // ================================================================
    // RELATIONSHIPS
    // ================================================================
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id', 'transaction_id');
    }

    // ================================================================
    // QR SCAN INTEGRATION
    // ================================================================
    public static function createFromQRScan(array $qrData, array $transactionData): array
    {
        try {
            DB::beginTransaction();

            // Validate QR data
            if (!self::validateQRData($qrData)) {
                throw new \Exception('Invalid QR code data');
            }

            // Get item detail from QR
            $itemDetail = ItemDetail::where('item_detail_id', $qrData['item_detail_id'])->first();
            if (!$itemDetail) {
                throw new \Exception('Item detail not found');
            }

            // Validate transaction permission
            if (!self::canUserCreateTransaction($transactionData['transaction_type'])) {
                throw new \Exception('User tidak memiliki permission untuk tipe transaksi ini');
            }

            // Validate item availability for OUT transactions
            if ($transactionData['transaction_type'] === self::TYPE_OUT && !$itemDetail->isAvailable()) {
                throw new \Exception('Item tidak tersedia untuk transaksi keluar');
            }

            // Create transaction
            $transaction = self::create([
                'transaction_type' => $transactionData['transaction_type'],
                'reference_id' => $transactionData['reference_id'] ?? null,
                'reference_type' => $transactionData['reference_type'] ?? null,
                'item_id' => $itemDetail->item_id,
                'quantity' => 1, // Always 1 for individual tracking
                'from_location' => $transactionData['from_location'] ?? $itemDetail->location,
                'to_location' => $transactionData['to_location'] ?? null,
                'notes' => $transactionData['notes'] ?? '',
                'status' => self::STATUS_PENDING,
                'created_by' => auth()->id(),
                'transaction_date' => now(),
            ]);

            // Create transaction detail
            TransactionDetail::create([
                'transaction_id' => $transaction->transaction_id,
                'item_detail_id' => $itemDetail->item_detail_id,
                'status_before' => $itemDetail->status,
                'status_after' => null, // Will be set when approved
                'notes' => $transactionData['detail_notes'] ?? null,
            ]);

            // Log activity
            ActivityLog::logActivity(
                'transactions',
                $transaction->transaction_id,
                'create',
                null,
                [
                    'transaction_type' => $transaction->transaction_type,
                    'item_detail_id' => $itemDetail->item_detail_id,
                    'qr_scan' => true
                ]
            );

            DB::commit();

            return [
                'success' => true,
                'transaction' => $transaction,
                'message' => 'Transaksi berhasil dibuat dan menunggu approval'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create transaction from QR scan: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function approve(?string $approverNotes = null): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        try {
            // Update transaction status
            $this->status = self::STATUS_APPROVED;
            $this->approved_by = auth()->id();
            $this->approved_date = now();

            if ($approverNotes) {
                $this->notes = $this->notes . "\n\nApprover Notes: " . $approverNotes;
            }

            $this->save(); // This will trigger the model event to execute sync

            // Log activity
            ActivityLog::logActivity(
                'transactions',
                $this->transaction_id,
                'approve',
                ['status' => self::STATUS_PENDING],
                ['status' => self::STATUS_APPROVED, 'approved_by' => auth()->id()]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to approve transaction: ' . $e->getMessage());
            return false;
        }
    }

    public function reject(string $reason = ''): bool
    {
        if (!$this->canBeRejected()) {
            return false;
        }

        try {
            $this->status = self::STATUS_REJECTED;
            $this->approved_by = auth()->id();
            $this->approved_date = now();

            if ($reason) {
                $this->notes = $this->notes . "\n\nRejection Reason: " . $reason;
            }

            $this->save();

            // Log activity
            ActivityLog::logActivity(
                'transactions',
                $this->transaction_id,
                'reject',
                ['status' => self::STATUS_PENDING],
                ['status' => self::STATUS_REJECTED, 'reason' => $reason]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to reject transaction: ' . $e->getMessage());
            return false;
        }
    }


    private function getNewStatusForItemDetail(string $currentStatus): string
    {
        Log::info('Determining new status', [
            'transaction_type' => $this->transaction_type,
            'current_status' => $currentStatus
        ]);

        switch ($this->transaction_type) {
            case self::TYPE_OUT:
                // Barang keluar dari sistem - jadi 'used'
                return 'used';

            case self::TYPE_IN:
            case self::TYPE_RETURN:
                // Barang masuk ke sistem atau return - jadi 'available'
                return 'available';

            case self::TYPE_REPAIR:
                // Barang ke repair - jadi 'repair'
                return 'repair';

            case self::TYPE_LOST:
                // Barang hilang - jadi 'lost'
                return 'lost';
            case self::TYPE_DAMAGED:  // 🆕 BARU
                return 'damaged';

            default:
                // Tidak ada perubahan status
                Log::warning('Unknown transaction type, keeping current status', [
                    'transaction_type' => $this->transaction_type,
                    'current_status' => $currentStatus
                ]);
                return $currentStatus;
        }
    }

    public static function getTransactionTypes(): array
    {
        return [
            self::TYPE_IN => 'Barang Masuk',
            self::TYPE_OUT => 'Barang Keluar',
            self::TYPE_REPAIR => 'Barang Repair',
            self::TYPE_LOST => 'Barang Hilang',
            self::TYPE_RETURN => 'Pengembalian',
            self::TYPE_DAMAGED => 'Barang Rusak',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu Approval',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    public function getTypeInfo(): array
    {
        $types = [
            self::TYPE_IN => [
                'text' => 'Barang Masuk',
                'class' => 'bg-green-100 text-green-800',
                'badge_class' => 'badge-success',
                'icon' => 'fas fa-arrow-down'
            ],
            self::TYPE_OUT => [
                'text' => 'Barang Keluar',
                'class' => 'bg-blue-100 text-blue-800',
                'badge_class' => 'badge-primary',
                'icon' => 'fas fa-arrow-up'
            ],
            self::TYPE_REPAIR => [
                'text' => 'Barang Repair',
                'class' => 'bg-yellow-100 text-yellow-800',
                'badge_class' => 'badge-warning',
                'icon' => 'fas fa-wrench'
            ],
            self::TYPE_LOST => [
                'text' => 'Barang Hilang',
                'class' => 'bg-red-100 text-red-800',
                'badge_class' => 'badge-danger',
                'icon' => 'fas fa-exclamation-triangle'
            ],
            self::TYPE_RETURN => [
                'text' => 'Pengembalian',
                'class' => 'bg-purple-100 text-purple-800',
                'badge_class' => 'badge-info',
                'icon' => 'fas fa-undo'
            ],
            self::TYPE_DAMAGED => [  // 🆕 BARU
                'text' => 'Barang Rusak',
                'class' => 'bg-red-100 text-red-800',
                'badge_class' => 'badge-danger',
                'icon' => 'fas fa-exclamation-triangle',
                'gradient' => 'from-red-600 to-red-700'
            ],
        ];

        return $types[$this->transaction_type] ?? $types[self::TYPE_OUT];
    }

    public function getStatusInfo(): array
    {
        $statuses = [
            self::STATUS_PENDING => [
                'text' => 'Menunggu Approval',
                'class' => 'bg-yellow-100 text-yellow-800',
                'badge_class' => 'badge-warning',
                'icon' => 'fas fa-clock'
            ],
            self::STATUS_APPROVED => [
                'text' => 'Disetujui',
                'class' => 'bg-green-100 text-green-800',
                'badge_class' => 'badge-success',
                'icon' => 'fas fa-check'
            ],
            self::STATUS_REJECTED => [
                'text' => 'Ditolak',
                'class' => 'bg-red-100 text-red-800',
                'badge_class' => 'badge-danger',
                'icon' => 'fas fa-times'
            ],
            self::STATUS_COMPLETED => [
                'text' => 'Selesai',
                'class' => 'bg-blue-100 text-blue-800',
                'badge_class' => 'badge-primary',
                'icon' => 'fas fa-check-double'
            ],
            self::STATUS_CANCELLED => [
                'text' => 'Dibatalkan',
                'class' => 'bg-gray-100 text-gray-800',
                'badge_class' => 'badge-secondary',
                'icon' => 'fas fa-ban'
            ],
        ];

        return $statuses[$this->status] ?? $statuses[self::STATUS_PENDING];
    }

    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeRejected(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }


    public static function getUserAllowedTypes(): array
    {
        $user = auth()->user();
        if (!$user) return [];

        $levelName = $user->getUserLevel()->level_name ?? '';

        switch (strtolower($levelName)) {
            case 'admin':
                return [self::TYPE_IN, self::TYPE_OUT, self::TYPE_REPAIR, self::TYPE_LOST, self::TYPE_RETURN,self::TYPE_DAMAGED];
            case 'logistik':
                return [self::TYPE_IN, self::TYPE_OUT, self::TYPE_REPAIR, self::TYPE_LOST, self::TYPE_RETURN,self::TYPE_DAMAGED];
            case 'teknisi':
                return [self::TYPE_IN, self::TYPE_OUT];
            default:
                return [];
        }
    }

    public static function canUserApprove(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        $levelName = strtolower($user->getUserLevel()->level_name ?? '');
        return in_array($levelName, ['admin', 'logistik']);
    }

    private static function canUserCreateTransaction(string $transactionType): bool
    {
        $allowedTypes = self::getUserAllowedTypes();
        return in_array($transactionType, $allowedTypes);
    }

    private static function validateQRData(array $qrData): bool
    {
        $requiredFields = ['type', 'item_detail_id', 'item_id'];

        foreach ($requiredFields as $field) {
            if (!isset($qrData[$field]) || empty($qrData[$field])) {
                return false;
            }
        }

        return $qrData['type'] === 'item_detail';
    }

    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('transaction_date', '>=', now()->subDays($days));
    }


    public static function generateTransactionId(): string
    {
        $lastTransaction = self::orderBy('transaction_id', 'desc')->first();
        $lastNumber = $lastTransaction ? (int) substr($lastTransaction->transaction_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        return 'TXN' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
    }

    public static function generateTransactionNumber(string $type): string
    {
        $prefix = [
            self::TYPE_IN => 'TI',
            self::TYPE_OUT => 'TO',
            self::TYPE_REPAIR => 'TR',
            self::TYPE_LOST => 'TL',
            self::TYPE_RETURN => 'TN',
        ][$type] ?? 'TX';

        $year = date('Y');
        $month = date('m');

        $lastTransaction = self::where('transaction_type', $type)
            ->where('transaction_number', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderBy('transaction_number', 'desc')
            ->first();

        if (!$lastTransaction) {
            return "{$prefix}-{$year}{$month}-001";
        }

        $lastNumber = (int) substr($lastTransaction->transaction_number, -3);
        $newNumber = $lastNumber + 1;

        return "{$prefix}-{$year}{$month}-" . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    public static function getTransactionStats(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return [
            'total_transactions' => self::where('transaction_date', '>=', $startDate)->count(),
            'pending_approvals' => self::where('status', self::STATUS_PENDING)->count(),
            'approved_today' => self::where('status', self::STATUS_APPROVED)
                ->whereDate('approved_date', today())->count(),
            'by_type' => self::where('transaction_date', '>=', $startDate)
                ->groupBy('transaction_type')
                ->selectRaw('transaction_type, COUNT(*) as count')
                ->pluck('count', 'transaction_type')
                ->toArray(),
            'by_status' => self::where('transaction_date', '>=', $startDate)
                ->groupBy('status')
                ->selectRaw('status, COUNT(*) as count')
                ->pluck('count', 'status')
                ->toArray(),
        ];
    }



    private function updateStockQuantities(Stock $stock, string $oldStatus, string $newStatus): void
    {
        $availableChange = 0;  // Perubahan stock gudang
        $usedChange = 0;       // Perubahan stock toko
        $totalChange = 0;      // Perubahan total inventory

        Log::info('Stock update calculation start', [
            'transaction_id' => $this->transaction_id,
            'transaction_type' => $this->transaction_type,
            'status_change' => "$oldStatus → $newStatus",
            'current_stock' => [
                'available' => $stock->quantity_available,  // gudang
                'used' => $stock->quantity_used,           // toko (status available)
                'total' => $stock->total_quantity
            ]
        ]);

        // CASE 1: DARI GUDANG KE TOKO (create ItemDetail dengan status 'available')
        if ($oldStatus === null && $newStatus === 'available') {
            $availableChange = -1;  // Gudang berkurang
            $usedChange = 1;        // Toko bertambah (bisa dipakai)
            $totalChange = 0;       // Total tetap (cuma pindah)
        }

        // CASE 2: DARI TOKO KE GUDANG (return)
        elseif ($oldStatus === 'available' && $newStatus === null) {
            $availableChange = 1;   // Gudang bertambah
            $usedChange = -1;       // Toko berkurang
            $totalChange = 0;       // Total tetap (cuma pindah)
        }

        // CASE 3: TRANSAKSI OUT - BARANG KELUAR DARI SISTEM
        // available → used/lost/repair/sold/damaged
        elseif ($oldStatus === 'available' && in_array($newStatus, ['used', 'lost', 'repair', 'damaged', 'sold'])) {
            $availableChange = 0;   // Gudang tetap
            $usedChange = -1;       // Toko berkurang (tidak bisa dipakai lagi)
            $totalChange = -1;      // Total berkurang (keluar sistem)
        }

        // CASE 4: RECOVERY - BARANG KEMBALI KE SISTEM
        elseif (in_array($oldStatus, ['used', 'lost', 'repair', 'damaged']) && $newStatus === 'available') {
            $availableChange = 0;   // Gudang tetap
            $usedChange = 1;        // Toko bertambah (bisa dipakai lagi)
            $totalChange = 1;       // Total bertambah (masuk sistem lagi)
        }

        // CASE 5: PERUBAHAN ANTAR STATUS OUT (tidak ada perubahan stock)
        elseif (
            in_array($oldStatus, ['used', 'lost', 'repair', 'damaged']) &&
            in_array($newStatus, ['used', 'lost', 'repair', 'damaged'])
        ) {
            // Tidak ada perubahan stock - hanya perubahan status
            $availableChange = 0;
            $usedChange = 0;
            $totalChange = 0;
        }

        // TERAPKAN PERUBAHAN KE DATABASE
        if ($availableChange !== 0 || $usedChange !== 0 || $totalChange !== 0) {
            $oldValues = [
                'available' => $stock->quantity_available,
                'used' => $stock->quantity_used,
                'total' => $stock->total_quantity
            ];

            // Apply changes
            $stock->quantity_available += $availableChange;
            $stock->quantity_used += $usedChange;
            $stock->total_quantity += $totalChange;
            $stock->last_updated = now();

            // Validasi: tidak boleh negatif
            $stock->quantity_available = max(0, $stock->quantity_available);
            $stock->quantity_used = max(0, $stock->quantity_used);
            $stock->total_quantity = max(0, $stock->total_quantity);

            $stock->save();

            $newValues = [
                'available' => $stock->quantity_available,
                'used' => $stock->quantity_used,
                'total' => $stock->total_quantity
            ];

            Log::info('Stock updated successfully', [
                'transaction_id' => $this->transaction_id,
                'stock_id' => $stock->stock_id,
                'status_change' => "$oldStatus → $newStatus",
                'changes_applied' => [
                    'available_change' => $availableChange,
                    'used_change' => $usedChange,
                    'total_change' => $totalChange
                ],
                'before' => $oldValues,
                'after' => $newValues,
                'explanation' => $this->getChangeExplanation($oldStatus, $newStatus)
            ]);

            // Log untuk audit trail
            ActivityLog::logActivity(
                'stocks',
                $stock->stock_id,
                'auto_update_from_transaction',
                $oldValues,
                array_merge($newValues, [
                    'transaction_id' => $this->transaction_id,
                    'transaction_type' => $this->transaction_type,
                    'status_change' => "$oldStatus → $newStatus",
                    'changes' => compact('availableChange', 'usedChange', 'totalChange')
                ])
            );
        } else {
            Log::info('No stock changes needed', [
                'transaction_id' => $this->transaction_id,
                'status_change' => "$oldStatus → $newStatus",
                'reason' => 'Status change does not affect stock quantities'
            ]);
        }
    }


    private function executeTransactionSync(): void
    {
        try {
            DB::beginTransaction();

            Log::info('Executing transaction sync', [
                'transaction_id' => $this->transaction_id,
                'transaction_type' => $this->transaction_type,
                'status' => $this->status,
                'details_count' => $this->transactionDetails->count()
            ]);

            foreach ($this->transactionDetails as $detail) {
                $itemDetail = $detail->itemDetail;
                $stock = $this->item->stock;

                if (!$itemDetail) {
                    Log::warning('ItemDetail not found for transaction detail', [
                        'transaction_detail_id' => $detail->transaction_detail_id,
                        'item_detail_id' => $detail->item_detail_id
                    ]);
                    continue;
                }

                if (!$stock) {
                    Log::warning('Stock not found for item', [
                        'transaction_id' => $this->transaction_id,
                        'item_id' => $this->item_id
                    ]);
                    continue;
                }

                $oldStatus = $itemDetail->status;
                $oldKondisi = $itemDetail->kondisi ??  '';
                $newStatus = $this->getNewStatusForItemDetail($oldStatus);
                $newKondisi = $this->getNewKondisi(); // Simple method

                Log::info('Processing item detail status change', [
                    'item_detail_id' => $itemDetail->item_detail_id,
                    'serial_number' => $itemDetail->serial_number,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'transaction_type' => $this->transaction_type
                ]);

                // 1. UPDATE ITEM DETAIL STATUS
                $itemDetail->update([
                    'status' => $newStatus,
                    'kondisi' => $newKondisi, // Auto sync kondisi
                    'location' => $this->to_location ?: $itemDetail->location // Update lokasi jika ada
                ]);

                // 2. UPDATE TRANSACTION DETAIL dengan status changes
                // 2. UPDATE TRANSACTION DETAIL (audit trail)
                $detail->update([
                    'status_before' => $oldStatus,
                    'status_after' => $newStatus,
                    'kondisi_before' => $oldKondisi,
                    'kondisi_after' => $newKondisi
                ]);


                // 3. UPDATE STOCK QUANTITIES dengan logika yang sudah diperbaiki
                $this->updateStockQuantities($stock, $oldStatus, $newStatus);

                Log::info('Item detail updated successfully', [
                    'item_detail_id' => $itemDetail->item_detail_id,
                    'status_updated' => $oldStatus . ' → ' . $newStatus,
                    'location_updated' => $itemDetail->location
                ]);
            }

            DB::commit();

            Log::info('Transaction sync completed successfully', [
                'transaction_id' => $this->transaction_id,
                'transaction_type' => $this->transaction_type,
                'items_processed' => $this->transactionDetails->count()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to execute transaction sync', [
                'transaction_id' => $this->transaction_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    // Get kondisi options (simple)
    public static function getKondisiOptions(): array
    {
        return [
            'good' => [
                'text' => 'Good',
                'class' => 'bg-green-100 text-green-800',
                'icon' => 'fas fa-check-circle'
            ],
            'no_good' => [
                'text' => 'No Good',
                'class' => 'bg-red-100 text-red-800',
                'icon' => 'fas fa-times-circle'
            ]
        ];
    }

    // SIMPLE: Auto determine kondisi based on transaction type
    private function getNewKondisi(): string
    {
        switch ($this->transaction_type) {
            case self::TYPE_IN:
            case self::TYPE_RETURN:
                // Barang masuk/return = good (default)
                return 'good';

            case self::TYPE_REPAIR:
                // Kalau ke repair = no_good
                return 'no_good';
            case self::TYPE_DAMAGED:  // 🆕 BARU
                // Auto-sync kondisi berdasarkan damage level
                switch ($this->damage_level) {
                    case 'light':
                        return 'fair';
                    case 'medium':
                        return 'no_good';
                    case 'heavy':
                    case 'total':
                        return 'broken';
                    default:
                        return 'no_good';
                }

            case self::TYPE_OUT:
            case self::TYPE_LOST:
            default:
                // Barang keluar/hilang = kondisi tetap
                return $this->transactionDetails->first()->itemDetail->kondisi ?? 'good';
        }
    }

    public static function getDamageLevels(): array
    {
        return self::DAMAGE_LEVELS;
    }

    public static function getDamageReasons(): array
    {
        return self::DAMAGE_REASONS;
    }
    public function getDamageLevelInfo(): array
    {
        $levels = [
            'light' => [
                'text' => 'Ringan',
                'description' => 'Kerusakan kecil, mudah diperbaiki',
                'class' => 'bg-yellow-100 text-yellow-800',
                'icon' => 'fas fa-tools'
            ],
            'medium' => [
                'text' => 'Sedang',
                'description' => 'Kerusakan menengah, perlu repair khusus',
                'class' => 'bg-orange-100 text-orange-800',
                'icon' => 'fas fa-wrench'
            ],
            'heavy' => [
                'text' => 'Berat',
                'description' => 'Kerusakan parah, repair mahal',
                'class' => 'bg-red-100 text-red-800',
                'icon' => 'fas fa-exclamation-triangle'
            ],
            'total' => [
                'text' => 'Total',
                'description' => 'Rusak total, tidak bisa diperbaiki',
                'class' => 'bg-gray-100 text-gray-800',
                'icon' => 'fas fa-times-circle'
            ]
        ];

        return $levels[$this->damage_level] ?? $levels['medium'];
    }

      public function validateDamagedTransaction(): array
    {
        $errors = [];

        if ($this->transaction_type === self::TYPE_DAMAGED) {
            if (empty($this->damage_level)) {
                $errors[] = 'Damage level wajib untuk transaksi barang rusak';
            }

            if (empty($this->damage_reason)) {
                $errors[] = 'Alasan kerusakan wajib untuk transaksi barang rusak';
            }

            if (empty($this->notes) || strlen($this->notes) < 10) {
                $errors[] = 'Catatan detail minimal 10 karakter untuk transaksi barang rusak';
            }

            // Repair estimate wajib untuk heavy damage
            if ($this->damage_level === 'heavy' && empty($this->repair_estimate)) {
                $errors[] = 'Estimasi biaya repair wajib untuk kerusakan berat';
            }
        }

        return $errors;
    }

    /**
     * Helper method: Get penjelasan perubahan untuk logging
     */
    private function getChangeExplanation(string $oldStatus, string $newStatus): string
    {
        if ($oldStatus === null && $newStatus === 'available') {
            return 'Barang pindah dari gudang ke toko (siap dipakai)';
        }

        if ($oldStatus === 'available' && $newStatus === null) {
            return 'Barang return dari toko ke gudang';
        }

        if ($oldStatus === 'available' && in_array($newStatus, ['used', 'lost', 'repair', 'damaged'])) {
            return 'Barang keluar dari toko dan sistem (tidak bisa dipakai lagi)';
        }

        if (in_array($oldStatus, ['used', 'lost', 'repair', 'damaged']) && $newStatus === 'available') {
            return 'Barang recovery kembali ke toko (bisa dipakai lagi)';
        }

        if (
            in_array($oldStatus, ['used', 'lost', 'repair', 'damaged']) &&
            in_array($newStatus, ['used', 'lost', 'repair', 'damaged'])
        ) {
            return 'Perubahan status tanpa impact stock (tetap di luar sistem)';
        }

        return 'Perubahan status lainnya';
    }

    /**
     * Public method: Get stock impact summary untuk transaction
     * Bisa dipanggil dari Controller untuk preview
     */
    public function getStockImpactSummary(): array
    {
        $impacts = [];
        $totalImpact = [
            'available_change' => 0,  // Perubahan stock gudang
            'used_change' => 0,       // Perubahan stock toko
            'total_change' => 0       // Perubahan total inventory
        ];

        foreach ($this->transactionDetails as $detail) {
            $impact = $this->calculateStockImpact($detail->status_before, $detail->status_after);

            $impacts[] = [
                'item_detail_id' => $detail->item_detail_id,
                'serial_number' => $detail->itemDetail->serial_number ?? 'N/A',
                'status_change' => ($detail->status_before ?? 'null') . ' → ' . ($detail->status_after ?? 'null'),
                'impact' => $impact,
                'explanation' => $this->getChangeExplanation($detail->status_before, $detail->status_after)
            ];

            $totalImpact['available_change'] += $impact['available_change'];
            $totalImpact['used_change'] += $impact['used_change'];
            $totalImpact['total_change'] += $impact['total_change'];
        }

        return [
            'transaction_id' => $this->transaction_id,
            'transaction_type' => $this->transaction_type,
            'item_impacts' => $impacts,
            'total_impact' => $totalImpact,
            'summary' => $this->getImpactSummaryText($totalImpact)
        ];
    }

    /**
     * Calculate impact per item (private helper)
     */
    private function calculateStockImpact(?string $oldStatus, ?string $newStatus): array
    {
        $impact = ['available_change' => 0, 'used_change' => 0, 'total_change' => 0];

        // Gudang ke toko
        if ($oldStatus === null && $newStatus === 'available') {
            $impact['available_change'] = -1;
            $impact['used_change'] = 1;
            $impact['total_change'] = 0;
        }
        // Toko ke gudang
        elseif ($oldStatus === 'available' && $newStatus === null) {
            $impact['available_change'] = 1;
            $impact['used_change'] = -1;
            $impact['total_change'] = 0;
        }
        // Keluar dari sistem
        elseif ($oldStatus === 'available' && in_array($newStatus, ['used', 'lost', 'repair', 'damaged'])) {
            $impact['available_change'] = 0;
            $impact['used_change'] = -1;
            $impact['total_change'] = -1;
        }
        // Recovery ke sistem
        elseif (in_array($oldStatus, ['used', 'lost', 'repair', 'damaged']) && $newStatus === 'available') {
            $impact['available_change'] = 0;
            $impact['used_change'] = 1;
            $impact['total_change'] = 1;
        }

        return $impact;
    }

    /**
     * Generate summary text (private helper)
     */
    private function getImpactSummaryText(array $totalImpact): string
    {
        $summaries = [];

        if ($totalImpact['available_change'] !== 0) {
            $direction = $totalImpact['available_change'] > 0 ? 'bertambah' : 'berkurang';
            $summaries[] = "Stock gudang {$direction} " . abs($totalImpact['available_change']);
        }

        if ($totalImpact['used_change'] !== 0) {
            $direction = $totalImpact['used_change'] > 0 ? 'bertambah' : 'berkurang';
            $summaries[] = "Stock toko {$direction} " . abs($totalImpact['used_change']);
        }

        if ($totalImpact['total_change'] !== 0) {
            $direction = $totalImpact['total_change'] > 0 ? 'bertambah' : 'berkurang';
            $summaries[] = "Total inventory {$direction} " . abs($totalImpact['total_change']);
        }

        return empty($summaries) ? 'Tidak ada perubahan stock' : implode(', ', $summaries);
    }
}
