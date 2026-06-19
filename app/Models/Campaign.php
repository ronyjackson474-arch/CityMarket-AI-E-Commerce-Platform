<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'type', 'discount_percentage',
        'starts_at', 'ends_at', 'is_active',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('campaign_price')->withTimestamps();
    }

    public function getIsActiveNowAttribute(): bool
    {
        $now = now();

        return $this->is_active
            && $this->starts_at <= $now
            && $this->ends_at >= $now;
    }
}
