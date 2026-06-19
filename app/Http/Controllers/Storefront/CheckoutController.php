<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private WalletService $walletService,
    ) {}

    public function index(Request $request)
    {
        $cart = $this->getCart($request);
        $cart->load(['items.product.images' => fn ($q) => $q->where('is_primary', true), 'items.product']);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        $addresses = Auth::check() ? $request->user()->addresses : collect();

        return view('storefront.checkout', compact('cart', 'addresses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string'],
            'payment_method' => ['required', 'in:cod,upi,card,wallet'],
            'notes' => ['nullable', 'string'],
            'coupon_code' => ['nullable', 'string'],
        ]);

        $cart = $this->getCart($request);
        $cart->load('items.product');

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        // Validate stock
        foreach ($cart->items as $item) {
            if ($item->quantity > $item->product->stock) {
                return back()->with('error', "Not enough stock for {$item->product->name}.");
            }
        }

        $subtotal = $cart->subtotal;
        $discount = 0;
        $coupon = null;

        if (! empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', $data['coupon_code'])->first();
            if ($coupon) {
                $discount = $coupon->calculateDiscount($subtotal);
            }
        }

        $shipping = $subtotal >= 500 ? 0 : 40;
        $total = max(0, $subtotal - $discount + $shipping);

        $order = DB::transaction(function () use ($request, $data, $cart, $subtotal, $discount, $shipping, $total, $coupon) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'status' => Order::STATUS_PENDING,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping' => $shipping,
                'total' => $total,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'shipping_address' => $data['shipping_address'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'notes' => $data['notes'] ?? null,
                'coupon_code' => $coupon?->code,
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'price' => $item->product->effective_price,
                    'quantity' => $item->quantity,
                ]);

                $item->product->decrement('stock', $item->quantity);
            }

            if ($coupon) {
                $coupon->increment('used_count');
            }

            $cart->items()->delete();

            return $order;
        });

        // Award 1% cashback to user wallet
        $this->walletService->awardOrderCashback($order);

        return redirect()->route('order.confirmation', $order->order_number)
            ->with('success', 'Order placed successfully! You earned ₹' . number_format((float)$order->total * 0.01) . ' cashback.');
    }

    public function confirmation(string $orderNumber)
    {
        $query = Order::where('order_number', $orderNumber)->with('items.product');

        // Scope to authenticated user to prevent IDOR
        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        }

        $order = $query->firstOrFail();

        return view('storefront.order-confirmation', compact('order'));
    }

    protected function getCart(Request $request)
    {
        if (Auth::check()) {
            return $this->cartService->forUser(Auth::user());
        }

        return $this->cartService->current(null);
    }
}
