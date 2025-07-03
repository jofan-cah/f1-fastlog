<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'username',
        'email',
        'password',
        'full_name',
        'user_level_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // Relationship: User belongs to UserLevel
    public function userLevel(): BelongsTo
    {
        return $this->belongsTo(UserLevel::class, 'user_level_id', 'user_level_id');
    }

    // Relationship: User has many PurchaseOrders (as creator)
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'created_by', 'user_id');
    }

    // Relationship: User has many GoodsReceived (as receiver)
    public function goodsReceived(): HasMany
    {
        return $this->hasMany(GoodsReceived::class, 'received_by', 'user_id');
    }

    // Relationship: User has many Transactions (as creator)
    public function createdTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'created_by', 'user_id');
    }

    // Relationship: User has many Transactions (as approver)
    public function approvedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'approved_by', 'user_id');
    }

    // Relationship: User has many ActivityLogs
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id', 'user_id');
    }

    // Helper method untuk check apakah user aktif
    public function isActive(): bool
    {
        return $this->is_active;
    }

    // Helper method untuk get level name
    public function getLevelName(): string
    {
        return $this->userLevel?->level_name ?? 'Unknown';
    }

    // Helper method untuk check permission via user level
    public function hasPermission(string $module, string $action): bool
    {
        return $this->userLevel?->hasPermission($module, $action) ?? false;
    }

    // Helper method untuk check apakah user adalah admin
    public function isAdmin(): bool
    {
        return $this->userLevel?->level_name === 'Admin';
    }

    // Helper method untuk check apakah user adalah logistik
    public function isLogistik(): bool
    {
        return $this->userLevel?->level_name === 'Logistik';
    }

    // Helper method untuk check apakah user adalah teknisi
    public function isTeknisi(): bool
    {
        return $this->userLevel?->level_name === 'Teknisi';
    }

    // Scope untuk filter user aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk filter by level
    public function scopeByLevel($query, string $levelName)
    {
        return $query->whereHas('userLevel', function ($q) use ($levelName) {
            $q->where('level_name', $levelName);
        });
    }
      // Alternative: atau bisa juga return UserLevel object dengan fallback
    public function getUserLevel()
    {
        return $this->userLevel ?? new \App\Models\UserLevel(['level_name' => 'Unknown']);
    }
}
