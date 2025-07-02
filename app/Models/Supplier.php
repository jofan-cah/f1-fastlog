<?php

// ================================================================
// 1. app/Models/Supplier.php
// ================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'suppliers';
    protected $primaryKey = 'supplier_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'supplier_id',
        'supplier_code',
        'supplier_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationship: Supplier has many Purchase Orders
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id', 'supplier_id');
    }

    // Relationship: Supplier has many Goods Received
    public function goodsReceived(): HasMany
    {
        return $this->hasMany(GoodsReceived::class, 'supplier_id', 'supplier_id');
    }

    // Helper method: Check if supplier is active
    public function isActive(): bool
    {
        return $this->is_active;
    }

    // Helper method: Get formatted phone number
    public function getFormattedPhone(): string
    {
        if (!$this->phone) {
            return '-';
        }

        // Format phone number (misal: 08123456789 → 0812-3456-789)
        $phone = preg_replace('/\D/', '', $this->phone); // Remove non-digits

        if (strlen($phone) >= 10) {
            return substr($phone, 0, 4) . '-' . substr($phone, 4, 4) . '-' . substr($phone, 8);
        }

        return $this->phone;
    }

    // Helper method: Get contact info summary
    public function getContactSummary(): array
    {
        return [
            'person' => $this->contact_person ?? 'Tidak ada',
            'phone' => $this->getFormattedPhone(),
            'email' => $this->email ?? 'Tidak ada',
        ];
    }

    // Helper method: Check if supplier has transactions
    public function hasTransactions(): bool
    {
        return $this->purchaseOrders()->exists() || $this->goodsReceived()->exists();
    }

    // Helper method: Get total purchase orders
    public function getTotalPurchaseOrders(): int
    {
        return $this->purchaseOrders()->count();
    }

    // Helper method: Get active purchase orders count
    public function getActivePurchaseOrders(): int
    {
        return $this->purchaseOrders()
                   ->whereNotIn('status', ['cancelled', 'completed'])
                   ->count();
    }

    // Helper method: Get supplier status badge class
    public function getStatusBadgeClass(): string
    {
        return $this->is_active ? 'badge-success' : 'badge-danger';
    }

    // Helper method: Get supplier status text
    public function getStatusText(): string
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    // Scope: Only active suppliers
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: Search suppliers
    public function scopeSearch($query, $term)
    {
        if (!$term) return $query;

        return $query->where(function($q) use ($term) {
            $q->where('supplier_code', 'like', "%{$term}%")
              ->orWhere('supplier_name', 'like', "%{$term}%")
              ->orWhere('contact_person', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%");
        });
    }

    // Scope: Filter by status
    public function scopeByStatus($query, $status)
    {
        if ($status === null) return $query;

        return $query->where('is_active', $status === 'active');
    }

    // Static method: Generate next supplier code
    public static function generateSupplierCode(): string
    {
        $lastSupplier = self::orderBy('supplier_code', 'desc')->first();

        if (!$lastSupplier) {
            return 'SUP001';
        }

        $lastNumber = (int) substr($lastSupplier->supplier_code, 3);
        $newNumber = $lastNumber + 1;

        return 'SUP' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    // Static method: Check if code is unique
    public static function isCodeUnique(string $code, string $excludeId = null): bool
    {
        $query = self::where('supplier_code', $code);

        if ($excludeId) {
            $query->where('supplier_id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    // Static method: Check if email is unique
    public static function isEmailUnique(string $email, string $excludeId = null): bool
    {
        if (!$email) return true;

        $query = self::where('email', $email);

        if ($excludeId) {
            $query->where('supplier_id', '!=', $excludeId);
        }

        return !$query->exists();
    }
}
