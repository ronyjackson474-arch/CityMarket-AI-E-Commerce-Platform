<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Vendor;
use App\Models\VendorEarning;
use App\Models\VendorWithdrawal;
use Illuminate\Support\Facades\DB;

class VendorEarningsService
{
    /**
     * Record vendor earnings for every vendor-owned item on an order.
     * Call when an order transitions to "delivered".
     */
    public function recordForOrder(Order $order): void
    {
        $order->loadMissing('items.product.vendor');

        foreach ($order->items as $item) {
            VendorEarning::recordForItem($item);
        }
    }

    /** Totals shown on the vendor dashboard. */
    public function summary(Vendor $vendor): array
    {
        $base = VendorEarning::where('vendor_id', $vendor->id);

        // Pending withdrawal requests hold funds: they reduce what's available
        // without touching the ledger rows (which are only locked when paid).
        $pendingHeld = (float) VendorWithdrawal::where('vendor_id', $vendor->id)
            ->where('status', VendorWithdrawal::STATUS_PENDING)
            ->sum('amount');

        $availableRaw = (float) (clone $base)->where('status', VendorEarning::STATUS_AVAILABLE)->sum('net');

        return [
            'total_gross' => (float) (clone $base)->sum('gross'),
            'total_commission' => (float) (clone $base)->sum('commission'),
            'available' => max(0, $availableRaw - $pendingHeld),
            'pending_payouts' => $pendingHeld,
            'withdrawn' => (float) (clone $base)->where('status', VendorEarning::STATUS_WITHDRAWN)->sum('net'),
        ];
    }

    /** Earnings line-items for the vendor panel. */
    public function ledger(Vendor $vendor, int $perPage = 15)
    {
        return VendorEarning::where('vendor_id', $vendor->id)
            ->with('orderItem.order:id,order_number,status,created_at')
            ->latest('id')
            ->paginate($perPage);
    }

    /** Withdrawals for the vendor panel. */
    public function withdrawals(Vendor $vendor, int $perPage = 10)
    {
        return VendorWithdrawal::where('vendor_id', $vendor->id)
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Request a withdrawal from available earnings.
     * Throws InvalidArgumentException with a user-safe message on failure.
     */
    public function requestWithdrawal(Vendor $vendor, float $amount, string $method, string $payoutDetail): VendorWithdrawal
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Withdrawal amount must be greater than zero.');
        }

        $available = $this->summary($vendor)['available'];

        if ($amount > $available) {
            throw new \InvalidArgumentException(
                'Insufficient balance. Available: ₹' . number_format($available, 2) . '.'
            );
        }

        $pending = VendorWithdrawal::where('vendor_id', $vendor->id)
            ->where('status', VendorWithdrawal::STATUS_PENDING)
            ->exists();

        if ($pending) {
            throw new \InvalidArgumentException(
                'You already have a pending withdrawal. Wait for it to be processed.'
            );
        }

        return VendorWithdrawal::create([
            'vendor_id' => $vendor->id,
            'amount' => $amount,
            'method' => in_array($method, ['bank_transfer', 'upi']) ? $method : 'bank_transfer',
            'payout_detail' => $payoutDetail,
            'status' => VendorWithdrawal::STATUS_PENDING,
        ]);
    }

    /** Admin: approve & pay a pending withdrawal (locks the earnings). */
    public function approveWithdrawal(VendorWithdrawal $withdrawal, ?string $note = null): VendorWithdrawal
    {
        if ($withdrawal->status !== VendorWithdrawal::STATUS_PENDING) {
            throw new \InvalidArgumentException('Only pending withdrawals can be approved.');
        }

        DB::transaction(function () use ($withdrawal, $note) {
            // Lock oldest available earnings (FIFO) up to the withdrawal amount.
            $remaining = (float) $withdrawal->amount;
            $earnings = VendorEarning::where('vendor_id', $withdrawal->vendor_id)
                ->where('status', VendorEarning::STATUS_AVAILABLE)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($earnings as $earning) {
                if ($remaining <= 0) {
                    break;
                }
                $earning->update(['status' => VendorEarning::STATUS_WITHDRAWN]);
                $remaining -= (float) $earning->net;
            }

            $withdrawal->update([
                'status' => VendorWithdrawal::STATUS_PAID,
                'admin_note' => $note,
                'processed_at' => now(),
            ]);
        });

        \App\Models\SiteNotification::notify(
            $withdrawal->vendor->user_id,
            'payout',
            "Payout of ₹" . number_format((float) $withdrawal->amount, 2) . " paid",
            'Your withdrawal request has been approved and paid.',
            '/vendor/earnings'
        );

        return $withdrawal->fresh();
    }

    /** Admin: reject a pending withdrawal — earnings become available again. */
    public function rejectWithdrawal(VendorWithdrawal $withdrawal, ?string $note = null): VendorWithdrawal
    {
        DB::transaction(function () use ($withdrawal, $note) {
            // Return the withheld amount to available by un-marking the newest
            // withdrawn rows until the amount is restored.
            $remaining = (float) $withdrawal->amount;
            $earnings = VendorEarning::where('vendor_id', $withdrawal->vendor_id)
                ->where('status', VendorEarning::STATUS_WITHDRAWN)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->get();

            foreach ($earnings as $earning) {
                if ($remaining <= 0) {
                    break;
                }
                $earning->update(['status' => VendorEarning::STATUS_AVAILABLE]);
                $remaining -= (float) $earning->net;
            }

            $withdrawal->update([
                'status' => VendorWithdrawal::STATUS_REJECTED,
                'admin_note' => $note,
                'processed_at' => now(),
            ]);
        });

        \App\Models\SiteNotification::notify(
            $withdrawal->vendor->user_id,
            'payout',
            'Withdrawal request rejected',
            'Your withdrawal was rejected' . ($note ? ": {$note}" : '.') . ' The amount is back in your available balance.',
            '/vendor/earnings'
        );

        return $withdrawal->fresh();
    }
}
