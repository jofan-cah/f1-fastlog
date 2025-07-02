<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserLevel extends Model
{
    protected $table = 'user_levels';
    protected $primaryKey = 'user_level_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_level_id',
        'level_name',
        'description',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    // Relationship: UserLevel has many Users
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'user_level_id', 'user_level_id');
    }

    // Helper method untuk check permission
    public function hasPermission(string $module, string $action): bool
    {
        if (!$this->permissions) {
            return false;
        }

        return isset($this->permissions[$module]) &&
               in_array($action, $this->permissions[$module]);
    }

    // Helper method untuk get all permissions sebagai array
    public function getAllPermissions(): array
    {
        return $this->permissions ?? [];
    }
}
