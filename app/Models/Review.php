<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'user_id', 'rating', 'title', 'body', 'is_verified_purchase',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * After saving a review, recalculate the product's aggregate rating and review count.
     */
    protected static function booted(): void
    {
        static::saved(function (Review $review) {
            $review->recalculateProductRating();
        });

        static::deleted(function (Review $review) {
            $review->recalculateProductRating();
        });
    }

    protected function recalculateProductRating(): void
    {
        $aggregate = self::where('product_id', $this->product_id)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
            ->first();

        if ($aggregate) {
            Product::where('id', $this->product_id)->update([
                'rating' => round((float) $aggregate->avg_rating, 1),
                'review_count' => (int) $aggregate->count,
            ]);
        }
    }
}
