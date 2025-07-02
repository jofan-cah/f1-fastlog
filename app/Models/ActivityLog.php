<?php

// ================================================================
// 1. app/Models/ActivityLog.php
// ================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';
    protected $primaryKey = 'log_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'log_id',
        'user_id',
        'table_name',
        'record_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    // Relationship: ActivityLog belongs to User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Helper method: Get user name safely
    public function getUserName(): string
    {
        return $this->user?->full_name ?? 'Unknown User';
    }

    // Helper method: Get user level safely
    public function getUserLevel(): string
    {
        return $this->user?->getLevelName() ?? 'Unknown';
    }

    // Helper method: Format action untuk display
    public function getFormattedAction(): string
    {
        $actions = [
            'create' => 'Menambah',
            'update' => 'Mengubah',
            'delete' => 'Menghapus',
            'login' => 'Login',
            'logout' => 'Logout',
            'activate' => 'Mengaktifkan',
            'deactivate' => 'Menonaktifkan',
            'toggle_status' => 'Mengubah Status',
            'generate_qr' => 'Generate QR Code',
            'adjustment' => 'Penyesuaian Stok',
            'bulk_adjustment' => 'Penyesuaian Stok Massal',
            'view_list' => 'Melihat Daftar',
            'view_detail' => 'Melihat Detail',
            'view_create_form' => 'Membuka Form Tambah',
            'view_edit_form' => 'Membuka Form Edit',
        ];

        return $actions[$this->action] ?? ucfirst($this->action);
    }

    // Helper method: Format table name untuk display
    public function getFormattedTableName(): string
    {
        $tables = [
            'users' => 'Pengguna',
            'user_levels' => 'Level Pengguna',
            'categories' => 'Kategori',
            'suppliers' => 'Supplier',
            'items' => 'Barang',
            'stocks' => 'Stok',
            'purchase_orders' => 'Purchase Order',
            'po_details' => 'Detail PO',
            'goods_receiveds' => 'Penerimaan Barang',
            'goods_received_details' => 'Detail Penerimaan',
            'transactions' => 'Transaksi',
            'transaction_details' => 'Detail Transaksi',
            'item_details' => 'Detail Barang',
            'activity_logs' => 'Log Aktivitas',
        ];

        return $tables[$this->table_name] ?? ucfirst($this->table_name);
    }

    // Helper method: Get activity description
    public function getDescription(): string
    {
        $action = $this->getFormattedAction();
        $table = $this->getFormattedTableName();

        if (in_array($this->action, ['login', 'logout'])) {
            return $action . ' ke sistem';
        }

        return $action . ' ' . $table . ($this->record_id ? " (ID: {$this->record_id})" : '');
    }

    // Helper method: Get activity summary for changes
    public function getChangeSummary(): array
    {
        if (!$this->old_values || !$this->new_values) {
            return [];
        }

        $changes = [];
        $newValues = $this->new_values;
        $oldValues = $this->old_values;

        foreach ($newValues as $field => $newValue) {
            $oldValue = $oldValues[$field] ?? null;

            if ($oldValue !== $newValue) {
                $changes[] = [
                    'field' => $this->formatFieldName($field),
                    'old_value' => $this->formatValue($oldValue),
                    'new_value' => $this->formatValue($newValue),
                ];
            }
        }

        return $changes;
    }

    // Helper method: Format field name untuk display
    private function formatFieldName(string $field): string
    {
        $fields = [
            'username' => 'Username',
            'email' => 'Email',
            'full_name' => 'Nama Lengkap',
            'user_level_id' => 'Level Pengguna',
            'is_active' => 'Status Aktif',
            'category_name' => 'Nama Kategori',
            'supplier_name' => 'Nama Supplier',
            'supplier_code' => 'Kode Supplier',
            'item_name' => 'Nama Barang',
            'item_code' => 'Kode Barang',
            'quantity_available' => 'Stok Tersedia',
            'quantity_used' => 'Stok Terpakai',
            'min_stock' => 'Minimum Stok',
            'created_at' => 'Dibuat',
            'updated_at' => 'Diupdate',
        ];

        return $fields[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    // Helper method: Format value untuk display
    private function formatValue($value): string
    {
        if (is_null($value)) {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }

        return (string) $value;
    }

    // Helper method: Get risk level berdasarkan action
    public function getRiskLevel(): array
    {
        $highRiskActions = ['delete', 'deactivate', 'bulk_adjustment'];
        $mediumRiskActions = ['create', 'update', 'adjustment', 'toggle_status'];
        $lowRiskActions = ['login', 'logout', 'view_list', 'view_detail', 'view_create_form', 'view_edit_form'];

        if (in_array($this->action, $highRiskActions)) {
            return ['level' => 'high', 'text' => 'Tinggi', 'class' => 'bg-red-100 text-red-800'];
        }

        if (in_array($this->action, $mediumRiskActions)) {
            return ['level' => 'medium', 'text' => 'Sedang', 'class' => 'bg-yellow-100 text-yellow-800'];
        }

        return ['level' => 'low', 'text' => 'Rendah', 'class' => 'bg-green-100 text-green-800'];
    }

    // Helper method: Check if activity is suspicious
    public function isSuspicious(): bool
    {
        // Multiple failed logins
        if ($this->action === 'login' && isset($this->new_values['status']) && $this->new_values['status'] === 'failed') {
            $recentFailures = self::where('user_id', $this->user_id)
                ->where('action', 'login')
                ->where('created_at', '>=', now()->subMinutes(15))
                ->whereJsonContains('new_values->status', 'failed')
                ->count();

            return $recentFailures >= 3;
        }

        // Mass deletion
        if ($this->action === 'delete') {
            $recentDeletions = self::where('user_id', $this->user_id)
                ->where('action', 'delete')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->count();

            return $recentDeletions >= 5;
        }

        // Unusual hours (outside 7 AM - 10 PM)
        $hour = $this->created_at->hour;
        if ($hour < 7 || $hour > 22) {
            return in_array($this->action, ['create', 'update', 'delete']);
        }

        return false;
    }

    // Scope: Filter by user
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope: Filter by table
    public function scopeByTable($query, $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    // Scope: Filter by action
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    // Scope: Filter by date range
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Scope: High risk activities
    public function scopeHighRisk($query)
    {
        return $query->whereIn('action', ['delete', 'deactivate', 'bulk_adjustment']);
    }

    // Scope: Recent activities
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    // Scope: Suspicious activities
    public function scopeSuspicious($query)
    {
        return $query->where(function($q) {
            // Failed logins
            $q->where('action', 'login')
              ->whereJsonContains('new_values->status', 'failed')
              // Or unusual hours
              ->orWhere(function($q2) {
                  $q2->whereIn('action', ['create', 'update', 'delete'])
                     ->where(function($q3) {
                         $q3->whereTime('created_at', '<', '07:00')
                            ->orWhereTime('created_at', '>', '22:00');
                     });
              });
        });
    }

    // Static method: Generate next log ID
    public static function generateLogId(): string
    {
        $lastLog = self::orderBy('log_id', 'desc')->first();
        $lastNumber = $lastLog ? (int) substr($lastLog->log_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        return 'LOG' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
    }

    // Static method: Log activity (helper untuk controller lain)
    public static function logActivity(string $tableName, string $recordId, string $action, ?array $oldData = null, ?array $newData = null): void
    {
        try {
            self::create([
                'log_id' => self::generateLogId(),
                'user_id' => auth()->id(),
                'table_name' => $tableName,
                'record_id' => $recordId,
                'action' => $action,
                'old_values' => $oldData,
                'new_values' => $newData,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }

    // Static method: Get activity statistics
    public static function getActivityStats(int $days = 7): array
    {
        $startDate = now()->subDays($days);

        return [
            'total_activities' => self::where('created_at', '>=', $startDate)->count(),
            'unique_users' => self::where('created_at', '>=', $startDate)->distinct('user_id')->count(),
            'high_risk_activities' => self::highRisk()->where('created_at', '>=', $startDate)->count(),
            'suspicious_activities' => self::suspicious()->where('created_at', '>=', $startDate)->count(),
            'login_attempts' => self::where('action', 'login')->where('created_at', '>=', $startDate)->count(),
            'failed_logins' => self::where('action', 'login')
                ->whereJsonContains('new_values->status', 'failed')
                ->where('created_at', '>=', $startDate)->count(),
        ];
    }
}

