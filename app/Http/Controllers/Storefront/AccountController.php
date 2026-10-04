<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\SiteNotification;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $orders = $user->orders()->latest()->limit(5)->get();
        $wallet = app(WalletService::class)->forUser($user);
        $wishlistCount = $user->wishlist()->count();

        return view('storefront.account.index', compact('user', 'orders', 'wallet', 'wishlistCount'));
    }

    public function notifications()
    {
        $user = Auth::user();
        $notifications = SiteNotification::forUser($user->id)->latest('created_at')->paginate(15);

        return view('storefront.account.notifications', compact('notifications'));
    }

    public function readNotification(string $id)
    {
        SiteNotification::forUser(Auth::id())->where('id', $id)->first()?->markRead();

        return back();
    }

    public function readAllNotifications()
    {
        SiteNotification::forUser(Auth::id())->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function orders()
    {
        $orders = Auth::user()->orders()->latest()->paginate(10);

        return view('storefront.account.orders', compact('orders'));
    }

    public function orderDetail(string $orderNumber)
    {
        $order = Auth::user()
            ->orders()
            ->where('order_number', $orderNumber)
            ->with('items.product.images')
            ->firstOrFail();

        $returnRequest = ReturnRequest::where('order_id', $order->id)->latest()->first();

        return view('storefront.account.order-detail', compact('order', 'returnRequest'));
    }

    public function cancelOrder(Request $request, string $orderNumber)
    {
        $order = Auth::user()
            ->orders()
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if (! in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED])) {
            return back()->with('error', 'This order can no longer be cancelled.');
        }

        $order->update(['status' => Order::STATUS_CANCELLED]);

        // Restore stock
        foreach ($order->items as $item) {
            $item->product?->increment('stock', $item->quantity);
        }

        return redirect()->route('account.order-detail', $orderNumber)
            ->with('success', 'Order cancelled successfully.');
    }

    public function requestReturn(Request $request, string $orderNumber)
    {
        $validated = $request->validate([
            'reason' => ['required', 'in:damaged,wrong_item,not_as_described,changed_mind'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = Auth::user()
            ->orders()
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if ($order->status !== Order::STATUS_DELIVERED) {
            return back()->with('error', 'Returns can only be requested for delivered orders.');
        }

        $existing = ReturnRequest::where('order_id', $order->id)
            ->whereIn('status', [ReturnRequest::STATUS_PENDING, ReturnRequest::STATUS_APPROVED])
            ->exists();

        if ($existing) {
            return back()->with('error', 'A return request already exists for this order.');
        }

        ReturnRequest::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'refund_amount' => $order->total,
            'status' => ReturnRequest::STATUS_PENDING,
        ]);

        return redirect()->route('account.order-detail', $orderNumber)
            ->with('success', 'Return request submitted. We will review it shortly.');
    }

    public function addresses()
    {
        $addresses = Auth::user()->addresses()->latest()->get();

        return view('storefront.account.addresses', compact('addresses'));
    }

    public function storeAddress(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address_line1' => ['required', 'string'],
            'address_line2' => ['nullable', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:10'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['is_default'])) {
            Auth::user()->addresses()->update(['is_default' => false]);
        }

        Auth::user()->addresses()->create($validated);

        return back()->with('success', 'Address saved.');
    }

    public function deleteAddress(Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $address->delete();

        return back()->with('success', 'Address removed.');
    }

    public function wallet(WalletService $walletService)
    {
        $wallet = $walletService->forUser(Auth::user());
        $transactions = $wallet->transactions()->latest()->paginate(15);

        return view('storefront.account.wallet', compact('wallet', 'transactions'));
    }

    public function referrals(WalletService $walletService)
    {
        $wallet = $walletService->forUser(Auth::user());
        $referredUsers = Order::whereHas('user', function ($q) use ($wallet) {
            $q->where('id', '!=', Auth::id());
        })->where('user_id', '!=', Auth::id())->limit(5)->get();

        // Users referred by current user
        $referrals = \App\Models\Wallet::where('referred_by', Auth::id())
            ->with('user:id,name,email')
            ->limit(10)
            ->get();

        return view('storefront.account.referrals', compact('wallet', 'referrals'));
    }
}
