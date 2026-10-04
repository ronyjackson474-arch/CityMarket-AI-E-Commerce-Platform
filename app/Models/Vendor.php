<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = [
        'user_id', 'shop_name', 'slug', 'logo_url', 'banner_url',
        'description', 'status', 'rating', 'commission_pct',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'approved';
    }

    /** Scope: publicly visible (approved) vendors. */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
