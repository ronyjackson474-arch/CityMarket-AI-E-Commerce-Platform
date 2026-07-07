<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'description', 'specifications',
        'sku', 'price', 'sale_price', 'stock', 'rating', 'review_count',
        'is_featured', 'is_flash_deal', 'is_active', 'flash_deal_ends_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'rating' => 'decimal:1',
        'is_featured' => 'boolean',
        'is_flash_deal' => 'boolean',
        'is_active' => 'boolean',
        'flash_deal_ends_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            $product->slug ??= Str::slug($product->name) . '-' . Str::random(6);
            $product->sku ??= strtoupper(Str::random(10));
        });
    }

    /** Effective price (sale_price if set and product is on sale, else price). */
    public function getEffectivePriceAttribute(): float
    {
        if ($this->sale_price && $this->sale_price > 0 && $this->sale_price < $this->price) {
            return (float) $this->sale_price;
        }

        return (float) $this->price;
    }

    /** Percentage discount based on current sale (0 if no active sale). */
    public function getDiscountPercentAttribute(): int
    {
        if ($this->sale_price && $this->sale_price > 0 && $this->sale_price < $this->price) {
            return (int) round((1 - $this->sale_price / $this->price) * 100);
        }

        return 0;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** Scope: only active products. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Scope: only featured products. */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true)->where('is_active', true);
    }

    /** Scope: flash deal products. */
    public function scopeFlashDeals(Builder $query): Builder
    {
        return $query->where('is_flash_deal', true)
            ->where('is_active', true)
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'price');
    }

    public function wishlistedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists')->withTimestamps();
    }

    /** Primary image URL (first image, or a placeholder). */
    public function getPrimaryImageAttribute(): ?string
    {
        return $this->relationLoaded('images')
            ? optional($this->images->firstWhere('is_primary', true))->image_path
                ?? optional($this->images->first())->image_path
            : null;
    }

    /**
     * Scope: apply product listing filters.
     */
    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(data_get($filters, 'category'), function ($q, $v) {
                $cat = Category::find($v);
                $q->when($cat, fn ($qq) => $qq->whereIn('category_id', $cat->descendantIds()));
            })
            ->when(data_get($filters, 'category_slug'), function ($q, $slug) {
                $cat = Category::where('slug', $slug)->first();
                $q->when($cat, fn ($qq) => $qq->whereIn('category_id', $cat->descendantIds()));
            })
            ->when(data_get($filters, 'brand'), fn ($q, $v) => $q->where('brand_id', $v))
            ->when(data_get($filters, 'price_min'), fn ($q, $v) => $q->where('price', '>=', $v))
            ->when(data_get($filters, 'price_max'), fn ($q, $v) => $q->where('price', '<=', $v))
            ->when(data_get($filters, 'min_rating'), fn ($q, $v) => $q->where('rating', '>=', $v))
            ->when(data_get($filters, 'search'), function ($q, $term) {
                $terms = array_filter(preg_split('/\s+/', trim($term)));
                $q->where(function ($qq) use ($term, $terms) {
                    // Match the full phrase first (higher relevance)
                    $qq->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%");
                    // Then match any individual word for broader recall
                    foreach ($terms as $word) {
                        if (strlen($word) >= 3) {
                            $qq->orWhere('name', 'like', "%{$word}%")
                                ->orWhere('description', 'like', "%{$word}%");
                        }
                    }
                });
            })
            ->when(data_get($filters, 'sort'), function ($q, $sort) {
                match ($sort) {
                    'price_asc' => $q->orderBy('price', 'asc'),
                    'price_desc' => $q->orderBy('price', 'desc'),
                    'rating' => $q->orderBy('rating', 'desc'),
                    'newest' => $q->latest(),
                    default => $q->orderBy('is_featured', 'desc')->orderBy('rating', 'desc'),
                };
            }, fn ($q) => $q->orderBy('is_featured', 'desc')->orderBy('rating', 'desc'))
            ->when(
                data_get($filters, 'on_sale'),
                fn ($q) => $q->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price')
            );
    }
}
