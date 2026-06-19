<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'session_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /** Total quantity of all items in the cart. */
    public function getItemCountAttribute(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    /** Subtotal of all items at their effective price. */
    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(function (CartItem $item) {
            return $item->quantity * $item->product->effective_price;
        });
    }

    /** Just a simple fixed shipping rate for Phase 1. */
    public function getShippingAttribute(): float
    {
        if ($this->items->isEmpty()) {
            return 0.0;
        }

        return $this->subtotal >= 500 ? 0.0 : 40.0;
    }

    public function getTotalAttribute(): float
    {
        return $this->subtotal + $this->shipping;
    }
}
