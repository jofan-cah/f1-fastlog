<?php

namespace App\Constants;

class PurchaseOrderConstants
{
    // Workflow Status Constants
    const WORKFLOW_STATUS_DRAFT_LOGISTIC = 'draft_logistic';
    const WORKFLOW_STATUS_PENDING_FINANCE_F1 = 'pending_finance_f1';
    const WORKFLOW_STATUS_PENDING_FINANCE_F2 = 'pending_finance_f2';
    const WORKFLOW_STATUS_APPROVED = 'approved';
    const WORKFLOW_STATUS_REJECTED_F1 = 'rejected_f1';
    const WORKFLOW_STATUS_REJECTED_F2 = 'rejected_f2';
    const WORKFLOW_STATUS_SENT = 'sent';
    const WORKFLOW_STATUS_PARTIAL = 'partial';
    const WORKFLOW_STATUS_RECEIVED = 'received';
    const WORKFLOW_STATUS_CANCELLED = 'cancelled';

    // Rejected By Level Constants
    const REJECTED_BY_F1 = 'f1';
    const REJECTED_BY_F2 = 'f2';

    // Payment Method Constants
    const PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';
    const PAYMENT_METHOD_VIRTUAL_ACCOUNT = 'virtual_account';
    const PAYMENT_METHOD_CASH = 'cash';
    const PAYMENT_METHOD_CHECK = 'check';
    const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';

    // Payment Status Constants
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_PARTIAL_PAID = 'partial_paid';
    const PAYMENT_STATUS_OVERDUE = 'overdue';
    const PAYMENT_STATUS_CANCELLED = 'cancelled';

    // Arrays for validation and dropdown options
    public static function getWorkflowStatuses(): array
    {
        return [
            self::WORKFLOW_STATUS_DRAFT_LOGISTIC => 'Draft - Logistik',
            self::WORKFLOW_STATUS_PENDING_FINANCE_F1 => 'Menunggu Finance F1',
            self::WORKFLOW_STATUS_PENDING_FINANCE_F2 => 'Menunggu FINANCE RBP',
            self::WORKFLOW_STATUS_APPROVED => 'Disetujui',
            self::WORKFLOW_STATUS_REJECTED_F1 => 'Ditolak Finance F1',
            self::WORKFLOW_STATUS_REJECTED_F2 => 'Ditolak FINANCE RBP',
            self::WORKFLOW_STATUS_SENT => 'Terkirim',
            self::WORKFLOW_STATUS_PARTIAL => 'Sebagian Diterima',
            self::WORKFLOW_STATUS_RECEIVED => 'Selesai',
            self::WORKFLOW_STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    public static function getPaymentMethods(): array
    {
        return [
            self::PAYMENT_METHOD_BANK_TRANSFER => 'Transfer Bank',
            self::PAYMENT_METHOD_VIRTUAL_ACCOUNT => 'Virtual Account',
            self::PAYMENT_METHOD_CASH => 'Tunai',
            self::PAYMENT_METHOD_CHECK => 'Cek',
            self::PAYMENT_METHOD_CREDIT_CARD => 'Kartu Kredit',
        ];
    }

    public static function getPaymentStatuses(): array
    {
        return [
            self::PAYMENT_STATUS_PENDING => 'Menunggu Pembayaran',
            self::PAYMENT_STATUS_PAID => 'Lunas',
            self::PAYMENT_STATUS_PARTIAL_PAID => 'Dibayar Sebagian',
            self::PAYMENT_STATUS_OVERDUE => 'Terlambat',
            self::PAYMENT_STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    public static function getRejectionLevels(): array
    {
        return [
            self::REJECTED_BY_F1 => 'Finance F1',
            self::REJECTED_BY_F2 => 'FINANCE RBP',
        ];
    }

    // Status styling helper methods
    public static function getWorkflowStatusStyle(string $status): array
    {
        $styles = [
            self::WORKFLOW_STATUS_DRAFT_LOGISTIC => [
                'text' => 'Draft - Logistik',
                'class' => 'bg-gray-100 text-gray-800',
                'badge_class' => 'badge-secondary',
                'description' => 'Logistik sedang memilih barang'
            ],
            self::WORKFLOW_STATUS_PENDING_FINANCE_F1 => [
                'text' => 'Menunggu Finance F1',
                'class' => 'bg-blue-100 text-blue-800',
                'badge_class' => 'badge-primary',
                'description' => 'Menunggu Finance F1 pilih supplier & payment options'
            ],
            self::WORKFLOW_STATUS_PENDING_FINANCE_F2 => [
                'text' => 'Menunggu FINANCE RBP',
                'class' => 'bg-yellow-100 text-yellow-800',
                'badge_class' => 'badge-warning',
                'description' => 'Menunggu FINANCE RBP approve & input payment'
            ],
            self::WORKFLOW_STATUS_APPROVED => [
                'text' => 'Disetujui',
                'class' => 'bg-green-100 text-green-800',
                'badge_class' => 'badge-success',
                'description' => 'PO sudah disetujui semua level'
            ],
            self::WORKFLOW_STATUS_REJECTED_F1 => [
                'text' => 'Ditolak Finance F1',
                'class' => 'bg-red-100 text-red-800',
                'badge_class' => 'badge-danger',
                'description' => 'Ditolak oleh Finance F1'
            ],
            self::WORKFLOW_STATUS_REJECTED_F2 => [
                'text' => 'Ditolak FINANCE RBP',
                'class' => 'bg-red-100 text-red-800',
                'badge_class' => 'badge-danger',
                'description' => 'Ditolak oleh FINANCE RBP'
            ],
            self::WORKFLOW_STATUS_SENT => [
                'text' => 'Terkirim',
                'class' => 'bg-blue-100 text-blue-800',
                'badge_class' => 'badge-primary',
                'description' => 'PO telah dikirim ke supplier'
            ],
            self::WORKFLOW_STATUS_PARTIAL => [
                'text' => 'Sebagian Diterima',
                'class' => 'bg-yellow-100 text-yellow-800',
                'badge_class' => 'badge-warning',
                'description' => 'Sebagian barang sudah diterima'
            ],
            self::WORKFLOW_STATUS_RECEIVED => [
                'text' => 'Selesai',
                'class' => 'bg-green-100 text-green-800',
                'badge_class' => 'badge-success',
                'description' => 'Semua barang sudah diterima'
            ],
            self::WORKFLOW_STATUS_CANCELLED => [
                'text' => 'Dibatalkan',
                'class' => 'bg-red-100 text-red-800',
                'badge_class' => 'badge-danger',
                'description' => 'PO dibatalkan'
            ],
        ];

        return $styles[$status] ?? $styles[self::WORKFLOW_STATUS_DRAFT_LOGISTIC];
    }

    public static function getPaymentStatusStyle(string $status): array
    {
        $styles = [
            self::PAYMENT_STATUS_PENDING => [
                'text' => 'Menunggu Pembayaran',
                'class' => 'bg-yellow-100 text-yellow-800',
                'badge_class' => 'badge-warning'
            ],
            self::PAYMENT_STATUS_PAID => [
                'text' => 'Lunas',
                'class' => 'bg-green-100 text-green-800',
                'badge_class' => 'badge-success'
            ],
            self::PAYMENT_STATUS_PARTIAL_PAID => [
                'text' => 'Dibayar Sebagian',
                'class' => 'bg-blue-100 text-blue-800',
                'badge_class' => 'badge-primary'
            ],
            self::PAYMENT_STATUS_OVERDUE => [
                'text' => 'Terlambat',
                'class' => 'bg-red-100 text-red-800',
                'badge_class' => 'badge-danger'
            ],
            self::PAYMENT_STATUS_CANCELLED => [
                'text' => 'Dibatalkan',
                'class' => 'bg-gray-100 text-gray-800',
                'badge_class' => 'badge-secondary'
            ],
        ];

        return $styles[$status] ?? $styles[self::PAYMENT_STATUS_PENDING];
    }

    // Validation rules
    public static function getWorkflowStatusValidationRule(): string
    {
        return 'in:' . implode(',', array_keys(self::getWorkflowStatuses()));
    }

    public static function getPaymentMethodValidationRule(): string
    {
        return 'in:' . implode(',', array_keys(self::getPaymentMethods()));
    }

    public static function getPaymentStatusValidationRule(): string
    {
        return 'in:' . implode(',', array_keys(self::getPaymentStatuses()));
    }
}
