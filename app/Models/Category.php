<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'icon', 'image', 'is_active', 'position',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            $category->slug ??= Str::slug($category->name) . '-' . Str::random(6);
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('position');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get this category's ID plus all descendant IDs (recursive).
     * Used for filtering products across a category hierarchy.
     */
    public function descendantIds(): array
    {
        $ids = [$this->id];
        $childIds = static::where('parent_id', $this->id)->pluck('id')->toArray();

        foreach ($childIds as $childId) {
            $child = static::find($childId);
            if ($child) {
                $ids = array_merge($ids, $child->descendantIds());
            }
        }

        return $ids;
    }
}
