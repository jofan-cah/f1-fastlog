<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use App\Constants\PurchaseOrderConstants;

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
        'workflow_status',
        'total_amount',
        'notes',
        'created_by',
        'logistic_user_id',
        'finance_f1_user_id',
        'finance_f2_user_id',
        'logistic_approved_at',
        'finance_f1_approved_at',
        'finance_f2_approved_at',
        'rejection_reason',
        'rejected_by_level',
        'rejected_at',
        'payment_method',
        'virtual_account_number',
        'payment_amount',
        'payment_status',
        'available_payment_options',
        'bank_name',
        'account_number',
        'account_holder',
        'payment_due_date',
        'finance_f1_notes',
        'finance_f2_notes',
    ];

    protected $casts = [
        'po_date' => 'date',
        'expected_date' => 'date',
        'payment_due_date' => 'date',
        'total_amount' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'logistic_approved_at' => 'datetime',
        'finance_f1_approved_at' => 'datetime',
        'finance_f2_approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'available_payment_options' => 'array',
    ];

    // Existing relationships
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function poDetails(): HasMany
    {
        return $this->hasMany(PoDetail::class, 'po_id', 'po_id');
    }

    public function goodsReceived(): HasMany
    {
        return $this->hasMany(GoodsReceived::class, 'po_id', 'po_id');
    }

    // New approval workflow relationships
    public function logisticUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logistic_user_id', 'user_id');
    }

    public function financeF1User(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_f1_user_id', 'user_id');
    }

    public function financeF2User(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_f2_user_id', 'user_id');
    }

    // Workflow Status Helper Methods
    public function getWorkflowStatusInfo(): array
    {
        return PurchaseOrderConstants::getWorkflowStatusStyle($this->workflow_status);
    }

    // Permission Helper Methods
    public function canBeEditedByLogistic(): bool
    {
        return $this->workflow_status === PurchaseOrderConstants::WORKFLOW_STATUS_DRAFT_LOGISTIC;
    }

    public function canBeProcessedByFinanceF1(): bool
    {
        return $this->workflow_status === PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F1;
    }

    public function canBeProcessedByFinanceF2(): bool
    {
        return $this->workflow_status === PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F2;
    }

    public function canBeRejectedByFinanceF1(): bool
    {
        return $this->workflow_status === PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F1;
    }

    public function canBeRejectedByFinanceF2(): bool
    {
        return $this->workflow_status === PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F2;
    }

    public function canBeCancelled(): bool
    {
        return !in_array($this->workflow_status, [
            PurchaseOrderConstants::WORKFLOW_STATUS_RECEIVED,
            PurchaseOrderConstants::WORKFLOW_STATUS_CANCELLED
        ]);
    }

    public function canReceiveGoods(): bool
    {
        return in_array($this->workflow_status, [
            PurchaseOrderConstants::WORKFLOW_STATUS_SENT,
            PurchaseOrderConstants::WORKFLOW_STATUS_PARTIAL
        ]);
    }

    // Workflow Action Methods
    public function submitToFinanceF1(string $logisticUserId): bool
    {
        if (!$this->canBeEditedByLogistic()) {
            return false;
        }

        return $this->update([
            'workflow_status' => PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F1,
            'logistic_user_id' => $logisticUserId,
            'logistic_approved_at' => now(),
        ]);
    }

    public function processFinanceF1(string $financeF1UserId, array $data): bool
    {
        if (!$this->canBeProcessedByFinanceF1()) {
            return false;
        }

        return $this->update([
            // Basic F1 data
            'workflow_status' => PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F2,
            'finance_f1_user_id' => $financeF1UserId,
            'finance_f1_approved_at' => now(),
            'supplier_id' => $data['supplier_id'] ?? $this->supplier_id,
            'finance_f1_notes' => $data['notes'] ?? null,

            // Payment details - ALL set by F1 now
            'payment_method' => $data['payment_method'] ?? null,
            'payment_amount' => $data['payment_amount'] ?? $this->total_amount,
            'virtual_account_number' => $data['virtual_account_number'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'account_number' => $data['account_number'] ?? null,
            'account_holder' => $data['account_holder'] ?? null,
            'payment_due_date' => $data['payment_due_date'] ?? null,

            // Set initial payment status
            'payment_status' => PurchaseOrderConstants::PAYMENT_STATUS_PENDING,
        ]);
    }

   // SIMPLIFIED: Approve FINANCE RBP - JUST APPROVAL NOW
    public function approveFinanceF2(string $financeF2UserId, array $data): bool
    {
        if (!$this->canBeProcessedByFinanceF2()) {
            return false;
        }

        // Validate that payment details are already set by F1
        if (!$this->payment_method || !$this->payment_amount) {
            throw new \Exception('Payment details must be set by Finance F1 before F2 approval.');
        }

        return $this->update([
            'workflow_status' => PurchaseOrderConstants::WORKFLOW_STATUS_APPROVED,
            'finance_f2_user_id' => $financeF2UserId,
            'finance_f2_approved_at' => now(),
            'finance_f2_notes' => $data['notes'] ?? null,

            // Payment status remains pending until actual payment
            // 'payment_status' stays as PENDING until payment is made
        ]);
    }

     // NEW: Helper method to check if PO is ready for F2 approval
    public function hasCompletePaymentDetails(): bool
    {
        if (!$this->payment_method || !$this->payment_amount) {
            return false;
        }

        // Check required fields based on payment method
        switch ($this->payment_method) {
            case PurchaseOrderConstants::PAYMENT_METHOD_BANK_TRANSFER:
                return !empty($this->bank_name) && !empty($this->account_number) && !empty($this->account_holder);

            case PurchaseOrderConstants::PAYMENT_METHOD_VIRTUAL_ACCOUNT:
                return !empty($this->bank_name) && !empty($this->virtual_account_number);

            case PurchaseOrderConstants::PAYMENT_METHOD_CHECK:
            case PurchaseOrderConstants::PAYMENT_METHOD_CREDIT_CARD:
                return !empty($this->bank_name);

            case PurchaseOrderConstants::PAYMENT_METHOD_CASH:
                return true; // Cash doesn't need additional details

            default:
                return true;
        }
    }



    public function rejectByFinanceF1(string $financeF1UserId, string $reason): bool
    {
        if (!$this->canBeRejectedByFinanceF1()) {
            return false;
        }

        return $this->update([
            'workflow_status' => PurchaseOrderConstants::WORKFLOW_STATUS_REJECTED_F1,
            'finance_f1_user_id' => $financeF1UserId,
            'rejected_by_level' => PurchaseOrderConstants::REJECTED_BY_F1,
            'rejection_reason' => $reason,
            'rejected_at' => now(),
        ]);
    }

    public function rejectByFinanceF2(string $financeF2UserId, string $reason): bool
    {
        if (!$this->canBeRejectedByFinanceF2()) {
            return false;
        }

        return $this->update([
            'workflow_status' => PurchaseOrderConstants::WORKFLOW_STATUS_REJECTED_F2,
            'finance_f2_user_id' => $financeF2UserId,
            'rejected_by_level' => PurchaseOrderConstants::REJECTED_BY_F2,
            'rejection_reason' => $reason,
            'rejected_at' => now(),
        ]);
    }

    public function returnToLogistic(): bool
    {
        if (in_array($this->workflow_status, [
            PurchaseOrderConstants::WORKFLOW_STATUS_REJECTED_F1,
            PurchaseOrderConstants::WORKFLOW_STATUS_REJECTED_F2
        ])) {
            return false;
        }

        return $this->update([
            'workflow_status' => PurchaseOrderConstants::WORKFLOW_STATUS_DRAFT_LOGISTIC,
            'rejected_by_level' => null,
            'rejection_reason' => null,
            'rejected_at' => null,
        ]);
    }

    // Payment Helper Methods
    public function getPaymentStatusInfo(): array
    {
        return PurchaseOrderConstants::getPaymentStatusStyle($this->payment_status);
    }

    public function isPaymentOverdue(): bool
    {
        return $this->payment_due_date &&
            $this->payment_due_date->isPast() &&
            $this->payment_status === PurchaseOrderConstants::PAYMENT_STATUS_PENDING;
    }

    // Existing helper methods (unchanged)
    public function calculateTotalAmount(): float
    {
        return $this->poDetails()->sum('total_price');
    }

    public function updateTotalAmount(): void
    {
        $this->update(['total_amount' => $this->calculateTotalAmount()]);
    }

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

    public function isOverdue(): bool
    {
        return $this->expected_date &&
            $this->expected_date->isPast() &&
            !in_array($this->workflow_status, [
                PurchaseOrderConstants::WORKFLOW_STATUS_RECEIVED,
                PurchaseOrderConstants::WORKFLOW_STATUS_CANCELLED
            ]);
    }

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
            if ($this->workflow_status === PurchaseOrderConstants::WORKFLOW_STATUS_PARTIAL) {
                $this->update(['workflow_status' => PurchaseOrderConstants::WORKFLOW_STATUS_SENT]);
            }
        } elseif ($totalReceived >= $totalOrdered) {
            // All items received
            $this->update(['workflow_status' => PurchaseOrderConstants::WORKFLOW_STATUS_RECEIVED]);
        } else {
            // Partially received
            $this->update(['workflow_status' => PurchaseOrderConstants::WORKFLOW_STATUS_PARTIAL]);
        }
    }

    // Updated scopes
    public function scopeByWorkflowStatus($query, $status)
    {
        if (!$status) return $query;
        return $query->where('workflow_status', $status);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        if (!$supplierId) return $query;
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('po_date', [$startDate, $endDate]);
    }

    public function scopePendingFinanceF1($query)
    {
        return $query->where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F1);
    }

    public function scopePendingFinanceF2($query)
    {
        return $query->where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F2);
    }

    public function scopeApproved($query)
    {
        return $query->where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->whereIn('workflow_status', [
            PurchaseOrderConstants::WORKFLOW_STATUS_REJECTED_F1,
            PurchaseOrderConstants::WORKFLOW_STATUS_REJECTED_F2
        ]);
    }

    public function scopePaymentOverdue($query)
    {
        return $query->where('payment_due_date', '<', now())
            ->where('payment_status', PurchaseOrderConstants::PAYMENT_STATUS_PENDING);
    }

    // Scope: Overdue POs
    public function scopeOverdue($query)
    {
        return $query->where('expected_date', '<', now())
            ->whereNotIn('workflow_status', [
                PurchaseOrderConstants::WORKFLOW_STATUS_RECEIVED,
                PurchaseOrderConstants::WORKFLOW_STATUS_CANCELLED
            ]);
    }

    // Scope: Active POs (not cancelled or completed)
    public function scopeActive($query)
    {
        return $query->whereNotIn('workflow_status', [
            PurchaseOrderConstants::WORKFLOW_STATUS_RECEIVED,
            PurchaseOrderConstants::WORKFLOW_STATUS_CANCELLED
        ]);
    }

    // Static methods
    public static function generatePONumber(): string
    {
        $prefix = 'PO' . date('Ym');
        $lastPO = self::where('po_number', 'like', $prefix . '%')
            ->orderBy('po_number', 'desc')
            ->first();

        if (!$lastPO) {
            return $prefix . '0001';
        }

        $lastNumber = (int) substr($lastPO->po_number, -4);
        $newNumber = $lastNumber + 1;

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function getWorkflowStatistics(): array
    {
        return [
            'total' => self::count(),
            'draft_logistic' => self::where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_DRAFT_LOGISTIC)->count(),
            'pending_finance_f1' => self::where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F1)->count(),
            'pending_finance_f2' => self::where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_PENDING_FINANCE_F2)->count(),
            'approved' => self::where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_APPROVED)->count(),
            'rejected' => self::whereIn('workflow_status', [
                PurchaseOrderConstants::WORKFLOW_STATUS_REJECTED_F1,
                PurchaseOrderConstants::WORKFLOW_STATUS_REJECTED_F2
            ])->count(),
            'sent' => self::where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_SENT)->count(),
            'received' => self::where('workflow_status', PurchaseOrderConstants::WORKFLOW_STATUS_RECEIVED)->count(),
            'overdue' => self::overdue()->count(),
            'payment_overdue' => self::paymentOverdue()->count(),
        ];
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
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft']);
    }

     // UPDATED: Payment information getter - now shows F1 setup
    public function getPaymentInfo(): array
    {
        if (!$this->payment_method) {
            return [
                'status' => 'not_setup',
                'message' => 'Payment details belum di-setup oleh Finance F1'
            ];
        }

        $info = [
            'method' => PurchaseOrderConstants::getPaymentMethods()[$this->payment_method] ?? $this->payment_method,
            'amount' => $this->payment_amount,
            'status' => $this->payment_status,
            'due_date' => $this->payment_due_date,
            'setup_by' => $this->financeF1User->name ?? 'Finance F1',
            'setup_at' => $this->finance_f1_approved_at,
        ];

        // Add method-specific details
        switch ($this->payment_method) {
            case PurchaseOrderConstants::PAYMENT_METHOD_BANK_TRANSFER:
                $info['details'] = [
                    'bank_name' => $this->bank_name,
                    'account_number' => $this->account_number,
                    'account_holder' => $this->account_holder,
                ];
                break;

            case PurchaseOrderConstants::PAYMENT_METHOD_VIRTUAL_ACCOUNT:
                $info['details'] = [
                    'bank_name' => $this->bank_name,
                    'virtual_account_number' => $this->virtual_account_number,
                ];
                break;

            case PurchaseOrderConstants::PAYMENT_METHOD_CHECK:
            case PurchaseOrderConstants::PAYMENT_METHOD_CREDIT_CARD:
                $info['details'] = [
                    'bank_name' => $this->bank_name,
                    'account_holder' => $this->account_holder,
                ];
                break;
        }

        return $info;
    }
}
