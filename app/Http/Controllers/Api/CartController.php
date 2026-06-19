<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function index(Request $request)
    {
        $cart = $this->getCart($request);
        $cart->load(['items.product.images' => fn ($q) => $q->where('is_primary', true), 'items.product.category:id,name,slug']);

        return response()->json([
            'data' => $cart,
            'summary' => [
                'item_count' => $cart->item_count,
                'subtotal' => $cart->subtotal,
                'shipping' => $cart->shipping,
                'total' => $cart->total,
            ],
        ]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        if (! $product->is_active) {
            return response()->json(['message' => 'This product is not available.'], 400);
        }

        if ($product->stock < ($data['quantity'] ?? 1)) {
            return response()->json([
                'message' => "Only {$product->stock} units of \"{$product->name}\" are in stock.",
            ], 400);
        }

        $cart = $this->getCart($request);
        $quantity = (int) ($data['quantity'] ?? 1);

        $cartItem = $cart->items()->where('product_id', $product->id)->first();

        if ($cartItem) {
            // Check combined quantity against stock
            if (($cartItem->quantity + $quantity) > $product->stock) {
                return response()->json([
                    'message' => "Cannot add more. Stock limit reached for \"{$product->name}\".",
                ], 400);
            }

            $cartItem->increment('quantity', $quantity);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
        }

        $cart->refresh();

        return response()->json([
            'message' => 'Product added to cart.',
            'data' => $cart->load('items.product'),
            'summary' => [
                'item_count' => $cart->item_count,
                'subtotal' => $cart->subtotal,
                'shipping' => $cart->shipping,
                'total' => $cart->total,
            ],
        ], 201);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->getCart($request);
        $item = $cart->items()->where('product_id', $data['product_id'])->first();

        if (! $item) {
            return response()->json(['message' => 'Item not found in cart.'], 404);
        }

        $product = $item->product;

        if ($data['quantity'] > $product->stock) {
            return response()->json([
                'message' => "Only {$product->stock} units of \"{$product->name}\" are in stock.",
            ], 400);
        }

        $item->update(['quantity' => $data['quantity']]);

        $cart->refresh();

        return response()->json([
            'message' => 'Cart updated.',
            'data' => $cart->load('items.product'),
            'summary' => [
                'item_count' => $cart->item_count,
            ],
        ]);
    }

    public function remove(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $cart = $this->getCart($request);
        $deleted = $cart->items()->where('product_id', $data['product_id'])->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Item not found in cart.'], 404);
        }

        $cart->refresh();

        return response()->json([
            'message' => 'Item removed from cart.',
            'data' => $cart->load('items.product'),
        ]);
    }

    public function clear(Request $request)
    {
        $cart = $this->getCart($request);
        $cart->items()->delete();

        return response()->json(['message' => 'Cart cleared.']);
    }

    protected function getCart(Request $request): Cart
    {
        return $this->cartService->forUser($request->user());
    }
}
