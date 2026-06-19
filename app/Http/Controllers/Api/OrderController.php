<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with([
                'items.product:id,name,slug',
                'items.product.images' => fn ($q) => $q->where('is_primary', true),
            ])
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }

    public function show(Request $request, string $orderNumber)
    {
        $order = $request->user()
            ->orders()
            ->where('order_number', $orderNumber)
            ->with('items.product')
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return response()->json(['data' => $order]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string'],
            'payment_method' => ['required', 'in:cod,upi,card,wallet'],
            'notes' => ['nullable', 'string'],
            'coupon_code' => ['nullable', 'string', 'exists:coupons,code'],
        ]);

        $cart = $this->cartService->forUser($request->user());
        $cart->load('items.product');

        if ($cart->items->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 400);
        }

        // Validate stock
        foreach ($cart->items as $item) {
            if ($item->quantity > $item->product->stock) {
                return response()->json([
                    'message' => "Not enough stock for \"{$item->product->name}\". Available: {$item->product->stock}.",
                ], 400);
            }
        }

        $subtotal = $cart->subtotal;
        $discount = 0.0;
        $coupon = null;

        if (! empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', $data['coupon_code'])->first();
            $discount = $coupon->calculateDiscount($subtotal);
        }

        $shipping = $subtotal >= 500 ? 0 : 40;
        $total = max(0, $subtotal - $discount + $shipping);

        $order = DB::transaction(function () use (
            $request, $data, $cart, $subtotal, $discount, $shipping, $total, $coupon
        ) {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'status' => Order::STATUS_PENDING,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping' => $shipping,
                'total' => $total,
                'payment_method' => $data['payment_method'],
                'payment_status' => $data['payment_method'] === 'cod' ? 'pending' : 'pending',
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

                // Decrement stock
                $item->product->decrement('stock', $item->quantity);
            }

            // Mark coupon used
            if ($coupon) {
                $coupon->increment('used_count');
            }

            // Clear the cart
            $cart->items()->delete();

            return $order;
        });

        return response()->json([
            'message' => 'Order placed successfully.',
            'data' => $order->load('items.product'),
        ], 201);
    }

    public function cancel(Request $request, string $orderNumber)
    {
        $order = $request->user()
            ->orders()
            ->where('order_number', $orderNumber)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if (! in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED])) {
            return response()->json([
                'message' => 'This order cannot be cancelled at its current stage.',
            ], 400);
        }

        $order->update(['status' => Order::STATUS_CANCELLED]);

        // Restore product stock
        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        return response()->json([
            'message' => 'Order cancelled.',
            'data' => $order->fresh(),
        ]);
    }
}
