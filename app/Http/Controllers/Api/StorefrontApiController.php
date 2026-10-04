<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;

class StorefrontApiController extends Controller
{
    /** GET /api/home — everything the mobile home screen needs in one call. */
    public function home()
    {
        $categories = Category::whereNull('parent_id')->where('is_active', true)
            ->withCount(['products' => fn ($p) => $p->where('is_active', true)])
            ->orderBy('name')->limit(8)->get(['id', 'name', 'slug']);

        $productWith = [
            'images' => fn ($q) => $q->where('is_primary', true),
            'category:id,name,slug',
            'brand:id,name,slug',
        ];

        $flashDeals = Product::visibleToStorefront()->flashDeals()
            ->with($productWith)->latest()->limit(10)->get();

        $featured = Product::visibleToStorefront()->where('is_featured', true)->where('is_active', true)
            ->with($productWith)->latest()->limit(10)->get();

        $trending = Product::visibleToStorefront()->where('is_active', true)
            ->with($productWith)->orderByDesc('review_count')->limit(10)->get();

        $brands = Brand::where('is_active', true)->orderBy('name')->limit(8)->get(['id', 'name', 'slug']);

        return response()->json([
            'data' => [
                'banners' => $this->banners(),
                'categories' => $categories,
                'flash_deals' => $flashDeals,
                'featured_products' => $featured,
                'trending_products' => $trending,
                'brands' => $brands,
            ],
        ]);
    }

    /** GET /api/vendors — approved vendor directory. */
    public function vendors(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $vendors = Vendor::approved()
            ->withCount(['products' => fn ($p) => $p->where('is_active', true)])
            ->when($q !== '', fn ($query) => $query->where('shop_name', 'like', "%{$q}%"))
            ->orderByDesc('rating')
            ->paginate((int) $request->query('per_page', 12));

        return response()->json(['data' => $vendors->items(), 'meta' => [
            'current_page' => $vendors->currentPage(),
            'last_page' => $vendors->lastPage(),
            'total' => $vendors->total(),
        ]]);
    }

    /** GET /api/vendors/{slug} — vendor profile + their active products. */
    public function vendorShow(Request $request, string $slug)
    {
        $vendor = Vendor::approved()->where('slug', $slug)->firstOrFail();

        $q = trim((string) $request->query('q', ''));

        $products = $vendor->products()
            ->where('is_active', true)
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->with([
                'images' => fn ($i) => $i->where('is_primary', true),
                'category:id,name,slug',
                'brand:id,name,slug',
            ])
            ->latest()
            ->paginate((int) $request->query('per_page', 12));

        return response()->json([
            'data' => [
                'vendor' => [
                    'id' => $vendor->id,
                    'shop_name' => $vendor->shop_name,
                    'slug' => $vendor->slug,
                    'logo_url' => $vendor->logo_url,
                    'banner_url' => $vendor->banner_url,
                    'description' => $vendor->description,
                    'rating' => $vendor->rating,
                ],
                'products' => $products->items(),
            ],
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /** GET /api/products/{slug}/related */
    public function related(string $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        $related = Product::visibleToStorefront()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with([
                'images' => fn ($q) => $q->where('is_primary', true),
                'category:id,name,slug',
                'brand:id,name,slug',
            ])
            ->inRandomOrder()
            ->limit(8)
            ->get();

        return response()->json(['data' => $related]);
    }

    /** GET /api/cart/count — lightweight badge count. */
    public function cartCount(Request $request, \App\Services\CartService $cartService)
    {
        $cart = $cartService->forUser($request->user());

        return response()->json(['data' => ['item_count' => $cart->item_count]]);
    }

    /** POST /api/cart/toggle-wishlist */
    public function toggleWishlist(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required_without:product_slug', 'integer', 'exists:products,id'],
            'product_slug' => ['required_without:product_id', 'string', 'exists:products,slug'],
        ]);

        $product = isset($data['product_slug'])
            ? Product::where('slug', $data['product_slug'])->firstOrFail()
            : Product::findOrFail($data['product_id']);

        $attached = $request->user()->wishlist()->toggle($product->id);
        $wishlisted = ! empty($attached['attached']);

        return response()->json([
            'message' => $wishlisted ? 'Added to wishlist.' : 'Removed from wishlist.',
            'data' => ['wishlisted' => $wishlisted],
        ]);
    }

    /** POST /api/coupons/validate — preview a coupon against the cart subtotal. */
    public function validateCoupon(Request $request, \App\Services\CartService $cartService)
    {
        $data = $request->validate(['code' => ['required', 'string']]);

        $cart = $cartService->forUser($request->user());
        $subtotal = (float) $cart->subtotal;

        $coupon = Coupon::where('code', $data['code'])->first();

        if (! $coupon || ! $coupon->is_valid) {
            return response()->json(['message' => 'This coupon is invalid or expired.'], 422);
        }

        if ($subtotal < (float) $coupon->min_order_amount) {
            return response()->json([
                'message' => 'Minimum order amount of ₹' . number_format((float) $coupon->min_order_amount) . ' required for this coupon.',
            ], 422);
        }

        $discount = $coupon->calculateDiscount($subtotal);
        $shipping = $subtotal >= 500 ? 0.0 : 40.0;
        $total = max(0, $subtotal - $discount + $shipping);

        return response()->json(['data' => [
            'code' => $coupon->code,
            'type' => $coupon->type,
            'discount' => $discount,
            'subtotal' => round($subtotal, 2),
            'shipping' => $shipping,
            'total' => round($total, 2),
        ]]);
    }

    /** GET /api/checkout/summary — everything the checkout screen needs. */
    public function checkoutSummary(Request $request, \App\Services\CartService $cartService)
    {
        $cart = $cartService->forUser($request->user());
        $cart->load(['items.product.images' => fn ($q) => $q->where('is_primary', true), 'items.product.category:id,name,slug']);

        if ($cart->items->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 400);
        }

        $subtotal = (float) $cart->subtotal;
        $discount = 0;
        $couponCode = $request->query('coupon_code');

        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon && $coupon->is_valid) {
                $discount = $coupon->calculateDiscount($subtotal);
            }
        }

        $shipping = $subtotal >= 500 ? 0.0 : 40.0;
        $total = max(0, $subtotal - $discount + $shipping);

        $addresses = $request->user()->addresses()->get();

        return response()->json(['data' => [
            'items' => $cart->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'slug' => $item->product->slug,
                'image' => $item->product->primary_image,
                'unit_price' => (float) $item->product->effective_price,
                'quantity' => $item->quantity,
                'line_total' => round($item->quantity * (float) $item->product->effective_price, 2),
                'in_stock' => $item->quantity <= $item->product->stock,
            ]),
            'totals' => [
                'subtotal' => round($subtotal, 2),
                'discount' => round($discount, 2),
                'coupon_code' => $couponCode && $discount > 0 ? $couponCode : null,
                'shipping' => $shipping,
                'total' => round($total, 2),
            ],
            'addresses' => $addresses,
            'payment_methods' => ['cod', 'upi', 'card', 'wallet'],
        ]]);
    }

    /** PUT /api/account/profile */
    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'url', 'max:500'],
        ]);

        $request->user()->update($data);

        return response()->json(['message' => 'Profile updated.', 'data' => $request->user()->fresh()]);
    }

    /** POST /api/account/change-password */
    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'different:current_password'],
        ]);

        if (! \Illuminate\Support\Facades\Hash::check($data['current_password'], $request->user()->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $request->user()->update(['password' => $data['new_password']]);

        return response()->json(['message' => 'Password changed successfully.']);
    }

    /** Banner set shared with the web home (single source when web migrates later). */
    private function banners(): array
    {
        return [
            ['title' => 'Mega Festive Sale', 'subtitle' => 'Up to 60% off across categories', 'image' => 'https://picsum.photos/seed/cm-hero1/1200/400', 'link' => '/shop'],
            ['title' => 'New Arrivals', 'subtitle' => 'Fresh drops every week', 'image' => 'https://picsum.photos/seed/cm-hero2/1200/400', 'link' => '/shop?sort=newest'],
            ['title' => 'Flash Deals', 'subtitle' => 'Limited time, huge savings', 'image' => 'https://picsum.photos/seed/cm-hero3/1200/400', 'link' => '/shop?flash=1'],
        ];
    }
}
