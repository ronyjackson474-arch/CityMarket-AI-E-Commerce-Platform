<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'balance', 'referral_code', 'referred_by',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Wallet $wallet) {
            $wallet->referral_code ??= 'CM' . strtoupper(Str::random(8));
            $wallet->balance ??= 0;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function credit(float $amount, string $reason, ?string $description = null, ?Model $source = null): WalletTransaction
    {
        $this->increment('balance', $amount);

        return $this->transactions()->create([
            'type' => 'credit',
            'amount' => $amount,
            'reason' => $reason,
            'description' => $description,
            'source_type' => $source ? get_class($source) : null,
            'source_id' => $source?->id,
        ]);
    }

    public function debit(float $amount, string $reason, ?string $description = null, ?Model $source = null): ?WalletTransaction
    {
        if ((float) $this->balance < $amount) {
            return null;
        }

        $this->decrement('balance', $amount);

        return $this->transactions()->create([
            'type' => 'debit',
            'amount' => $amount,
            'reason' => $reason,
            'description' => $description,
            'source_type' => $source ? get_class($source) : null,
            'source_id' => $source?->id,
        ]);
    }
}
