<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'category_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'category_id',
        'category_name',
        'description',
        'parent_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationship: Category belongs to Parent Category
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id', 'category_id');
    }

    // Relationship: Category has many Child Categories
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id', 'category_id')
                   ->where('is_active', true)
                   ->orderBy('category_name');
    }

    // Relationship: Category has many Items
    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'category_id', 'category_id');
    }

    // Recursive relationship untuk get all children
    public function allChildren(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id', 'category_id')
                   ->with('allChildren');
    }

    // Helper method: Check if category is root (no parent)
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    // Helper method: Check if category has children
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    // Helper method: Get category level/depth
    public function getLevel(): int
    {
        $level = 0;
        $parent = $this->parent;

        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }

        return $level;
    }

    // Helper method: Get full category path
    public function getFullPath(): string
    {
        $path = [$this->category_name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->category_name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }

    // Helper method: Get breadcrumb array
    public function getBreadcrumb(): array
    {
        $breadcrumb = [];
        $current = $this;

        while ($current) {
            array_unshift($breadcrumb, [
                'id' => $current->category_id,
                'name' => $current->category_name
            ]);
            $current = $current->parent;
        }

        return $breadcrumb;
    }

    // Scope: Only active categories
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: Only root categories (no parent)
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    // Scope: Categories with specific parent
    public function scopeByParent($query, $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    // Static method: Get tree structure for dropdown
    public static function getTreeOptions($selectedId = null, $excludeId = null): array
    {
        $categories = self::active()
            ->with('allChildren')
            ->root()
            ->orderBy('category_name')
            ->get();

        $options = ['' => '-- Pilih Kategori Induk --'];

        foreach ($categories as $category) {
            self::buildTreeOptions($category, $options, 0, $selectedId, $excludeId);
        }

        return $options;
    }

    // Helper untuk build tree options recursively
    private static function buildTreeOptions($category, &$options, $level, $selectedId = null, $excludeId = null): void
    {
        // Skip jika category ini yang dikecualikan
        if ($excludeId && $category->category_id === $excludeId) {
            return;
        }

        $prefix = str_repeat('-- ', $level);
        $options[$category->category_id] = $prefix . $category->category_name;

        foreach ($category->allChildren as $child) {
            self::buildTreeOptions($child, $options, $level + 1, $selectedId, $excludeId);
        }
    }
}
