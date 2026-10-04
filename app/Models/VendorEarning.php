<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorEarning extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_WITHDRAWN = 'withdrawn';

    protected $fillable = [
        'vendor_id', 'order_item_id', 'gross', 'commission', 'net', 'status',
    ];

    protected $casts = [
        'gross' => 'decimal:2',
        'commission' => 'decimal:2',
        'net' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Record earnings for an order item (idempotent — one row per item).
     */
    public static function recordForItem(OrderItem $item): ?self
    {
        if (! $item->product?->vendor_id) {
            return null; // platform-owned product — no vendor earning
        }

        $vendor = $item->product->vendor;
        $gross = (float) $item->price * (int) $item->quantity;
        $commission = round($gross * ((int) $vendor->commission_pct / 100), 2);

        return static::updateOrCreate(
            ['order_item_id' => $item->id],
            [
                'vendor_id' => $vendor->id,
                'gross' => $gross,
                'commission' => $commission,
                'net' => $gross - $commission,
            ]
        );
    }
}
