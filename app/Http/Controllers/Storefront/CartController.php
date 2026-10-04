<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function index(Request $request)
    {
        $cart = $this->getCart($request);
        $cart->load(['items.product.images' => fn ($q) => $q->where('is_primary', true), 'items.product.category:id,name,slug']);

        return view('storefront.cart', compact('cart'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $qty = (int) ($data['quantity'] ?? 1);

        if ($product->stock < $qty) {
            return $this->ajaxOrRedirect($request, false, "Only {$product->stock} units in stock.", [
                'cart_count' => $this->getCartCount($request),
            ]);
        }

        $cart = $this->getCart($request);
        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item) {
            if ($item->quantity + $qty > $product->stock) {
                return $this->ajaxOrRedirect($request, false, 'Cannot add more — stock limit reached.', [
                    'cart_count' => $cart->items()->sum('quantity'),
                ]);
            }
            $item->increment('quantity', $qty);
        } else {
            $cart->items()->create(['product_id' => $product->id, 'quantity' => $qty]);
        }

        return $this->ajaxOrRedirect($request, true, "{$product->name} added to cart.", [
            'cart_count' => $cart->items()->sum('quantity'),
        ]);
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
            return back()->with('error', 'Item not found in cart.');
        }

        if ($data['quantity'] > $item->product->stock) {
            return back()->with('error', "Only {$item->product->stock} units available.");
        }

        $item->update(['quantity' => $data['quantity']]);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $cart = $this->getCart($request);
        $cart->items()->where('product_id', $data['product_id'])->delete();

        return back()->with('success', 'Item removed.');
    }

    public function count(Request $request)
    {
        $cart = $this->getCart($request);
        $count = $cart->items()->sum('quantity');

        return response()->json(['count' => $count]);
    }

    protected function getCartCount(Request $request): int
    {
        return $this->getCart($request)->items()->sum('quantity');
    }

    protected function ajaxOrRedirect(Request $request, bool $success, string $message, array $extra = [])
    {
        if ($request->expectsJson()) {
            return response()->json(array_merge([
                'success' => $success,
                'message' => $message,
            ], $extra));
        }

        if ($success) {
            return redirect()->route('cart')->with('success', $message);
        }

        return back()->with('error', $message);
    }

    protected function getCart(Request $request)
    {
        if (Auth::check()) {
            return $this->cartService->forUser(Auth::user());
        }

        // Guest cart via session
        return $this->cartService->current(null);
    }
}
