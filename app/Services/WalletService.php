<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Str;

class WalletService
{
    /**
     * Ensure a user has a wallet (create on first access).
     */
    public function forUser(User $user): Wallet
    {
        return Wallet::firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * Award cashback for an order (1% of order total).
     */
    public function awardOrderCashback(Order $order): ?WalletTransaction
    {
        // Guest orders have no user — no wallet, no cashback.
        if (! $order->user) {
            return null;
        }

        $wallet = $this->forUser($order->user);
        $cashback = round((float) $order->total * 0.01, 2); // 1% cashback

        if ($cashback <= 0) {
            return null;
        }

        return $wallet->credit(
            $cashback,
            'cashback',
            "1% cashback on order {$order->order_number}",
            $order
        );
    }

    /**
     * Refund to wallet for a cancelled/returned order.
     */
    public function refundOrder(Order $order, float $amount, string $reason = 'refund'): ?WalletTransaction
    {
        $wallet = $this->forUser($order->user);

        return $wallet->credit(
            $amount,
            $reason,
            "Refund of ₹{$amount} for order {$order->order_number}",
            $order
        );
    }

    /**
     * Apply a referral code during registration.
     * Both the new user and the referrer receive bonus points.
     */
    public function applyReferralCode(User $newUser, string $code): bool
    {
        if (empty($code)) {
            return false;
        }

        $referrerWallet = Wallet::where('referral_code', $code)->first();

        if (! $referrerWallet || $referrerWallet->user_id === $newUser->id) {
            return false;
        }

        $newUserWallet = $this->forUser($newUser);
        $newUserWallet->update(['referred_by' => $referrerWallet->user_id]);

        // Bonus to new user
        $newUserWallet->credit(100, 'referral_bonus', "Welcome bonus for using referral code {$code}");

        // Bonus to referrer
        $referrerWallet->credit(100, 'referral_bonus', "Referral bonus — you invited {$newUserWallet->user->name}");

        return true;
    }
}
