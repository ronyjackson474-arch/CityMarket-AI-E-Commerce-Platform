<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VendorApiController extends Controller
{
    /** POST /api/vendor/register — become a vendor (any authenticated user). */
    public function register(Request $request)
    {
        $data = $request->validate([
            'shop_name'   => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo_url'    => ['nullable', 'url', 'max:500'],
        ]);

        $user = $request->user();

        if ($user->vendor) {
            return response()->json(['message' => 'You already have a vendor account.'], 409);
        }

        $slug = Str::slug($data['shop_name']);
        if (Vendor::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::lower(Str::random(4));
        }

        $user->update(['role' => 'vendor']);

        $vendor = Vendor::create([
            'user_id'     => $user->id,
            'shop_name'   => $data['shop_name'],
            'slug'        => $slug,
            'logo_url'    => $data['logo_url'] ?? null,
            'description' => $data['description'] ?? null,
            'status'      => 'pending',
        ]);

        return response()->json([
            'message' => 'Vendor application received. Pending admin approval.',
            'data' => ['vendor' => $vendor],
        ], 201);
    }

    /** GET /api/vendor/status — poll this while pending. */
    public function status(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (! $vendor) {
            return response()->json(['data' => ['is_vendor' => false]]);
        }

        return response()->json(['data' => ['is_vendor' => true, 'vendor' => $vendor]]);
    }

    /** Guard: vendor + approved (403 otherwise). */
    private function vendor(Request $request): Vendor
    {
        $vendor = $request->user()->vendor;

        if (! $vendor || $vendor->status !== 'approved') {
            abort(403, 'Approved vendor access required.');
        }

        return $vendor;
    }

    /** GET /api/vendor/dashboard */
    public function dashboard(Request $request)
    {
        $vendor = $this->vendor($request);

        $revenue = OrderItem::query()
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('products.vendor_id', $vendor->id)
            ->selectRaw('COALESCE(SUM(order_items.price * order_items.quantity), 0) AS revenue')
            ->value('revenue');

        return response()->json(['data' => [
            'products'   => $vendor->products()->count(),
            'active'     => $vendor->products()->where('is_active', true)->count(),
            'pending'    => $vendor->products()->where('is_active', false)->count(),
            'units_sold' => (int) OrderItem::whereHas('product', fn ($q) => $q->where('vendor_id', $vendor->id))->sum('quantity'),
            'revenue'    => (float) $revenue,
        ]]);
    }

    /** GET /api/vendor/products */
    public function products(Request $request)
    {
        $vendor = $this->vendor($request);

        $products = $vendor->products()->with('images')->latest()
            ->paginate((int) $request->query('per_page', 10));

        return response()->json(['data' => $products->items(), 'meta' => [
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
        ]]);
    }

    /** POST /api/vendor/products — created inactive (admin approval flow). */
    public function storeProduct(Request $request)
    {
        $vendor = $this->vendor($request);

        $data = $this->validateProduct($request, $vendor->id);

        $product = $vendor->products()->create($data + ['is_active' => false]);
        $this->syncImages($product, $request);

        return response()->json([
            'message' => 'Product submitted — pending admin approval.',
            'data' => $product->load('images'),
        ], 201);
    }

    /** GET /api/vendor/products/{product} */
    public function showProduct(Request $request, int $productId)
    {
        $vendor = $this->vendor($request);
        $product = $vendor->products()->with('images')->find($productId);

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return response()->json(['data' => $product]);
    }

    /** PUT /api/vendor/products/{product} — pushes product back into review. */
    public function updateProduct(Request $request, int $productId)
    {
        $vendor = $this->vendor($request);
        $product = $vendor->products()->find($productId);

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $data = $this->validateProduct($request, $vendor->id, $product->id);
        $product->update($data + ['is_active' => false]);
        $this->syncImages($product, $request);

        return response()->json(['message' => 'Product updated — sent back for approval.', 'data' => $product->fresh('images')]);
    }

    /** DELETE /api/vendor/products/{product} */
    public function destroyProduct(Request $request, int $productId)
    {
        $vendor = $this->vendor($request);
        $product = $vendor->products()->find($productId);

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted.']);
    }

    /** GET /api/vendor/orders — only own products' line items. */
    public function orders(Request $request)
    {
        $vendor = $this->vendor($request);

        $items = OrderItem::whereHas('product', fn ($q) => $q->where('vendor_id', $vendor->id))
            ->with(['order:id,order_number,status,created_at', 'product:id,name,slug'])
            ->latest('id')
            ->paginate((int) $request->query('per_page', 15));

        return response()->json(['data' => $items->items(), 'meta' => [
            'current_page' => $items->currentPage(),
            'last_page' => $items->lastPage(),
            'total' => $items->total(),
        ]]);
    }

    /** POST /api/vendor/orders/{orderNumber}/status — vendor fulfilment. */
    public function updateOrderStatus(Request $request, string $orderNumber)
    {
        $data = $request->validate([
            'status' => ['required', 'in:packed,shipped'],
        ]);

        $vendor = $this->vendor($request);

        $order = \App\Models\Order::where('order_number', $orderNumber)
            ->whereHas('items.product', fn ($q) => $q->where('vendor_id', $vendor->id))
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found among your products\' orders.'], 404);
        }

        $allowed = [
            \App\Models\Order::STATUS_PENDING => [\App\Models\Order::STATUS_CONFIRMED],
            \App\Models\Order::STATUS_CONFIRMED => [\App\Models\Order::STATUS_PACKED],
            \App\Models\Order::STATUS_PACKED => [\App\Models\Order::STATUS_SHIPPED],
        ];

        if (! in_array($data['status'], $allowed[$order->status] ?? [], true)) {
            return response()->json([
                'message' => "Cannot move order from \"{$order->status}\" to \"{$data['status']}\".",
            ], 422);
        }

        $order->update(['status' => $data['status']]);

        return response()->json([
            'message' => "Order {$orderNumber} → {$data['status']}.",
            'data' => $order->fresh(),
        ]);
    }

    /** GET /api/vendor/profile */
    public function profile(Request $request)
    {
        return response()->json(['data' => $this->vendor($request)]);
    }

    /** GET /api/vendor/earnings — summary + paginated ledger. */
    public function earnings(Request $request)
    {
        $vendor = $this->vendor($request);
        $service = app(\App\Services\VendorEarningsService::class);

        $ledger = $service->ledger($vendor, (int) $request->query('per_page', 15));

        return response()->json([
            'data' => [
                'summary' => $service->summary($vendor),
                'ledger' => $ledger->items(),
            ],
            'meta' => [
                'current_page' => $ledger->currentPage(),
                'last_page' => $ledger->lastPage(),
                'total' => $ledger->total(),
            ],
        ]);
    }

    /** GET /api/vendor/withdrawals */
    public function withdrawals(Request $request)
    {
        $vendor = $this->vendor($request);

        $list = \App\Models\VendorWithdrawal::where('vendor_id', $vendor->id)
            ->latest('id')
            ->paginate((int) $request->query('per_page', 10));

        return response()->json(['data' => $list->items(), 'meta' => [
            'current_page' => $list->currentPage(),
            'last_page' => $list->lastPage(),
            'total' => $list->total(),
        ]]);
    }

    /** POST /api/vendor/withdrawals — request a payout. */
    public function requestWithdrawal(Request $request)
    {
        $vendor = $this->vendor($request);

        $data = $request->validate([
            'amount'        => ['required', 'numeric', 'min:1'],
            'method'        => ['required', 'in:bank_transfer,upi'],
            'payout_detail' => ['required', 'string', 'max:200'],
        ]);

        try {
            $withdrawal = app(\App\Services\VendorEarningsService::class)
                ->requestWithdrawal($vendor, (float) $data['amount'], $data['method'], $data['payout_detail']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Withdrawal request submitted. It will be paid after admin approval.',
            'data' => $withdrawal,
        ], 201);
    }

    /** PUT /api/vendor/profile */
    public function updateProfile(Request $request)
    {
        $vendor = $this->vendor($request);

        $data = $request->validate([
            'shop_name'   => ['sometimes', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo_url'    => ['nullable', 'url', 'max:500'],
            'banner_url'  => ['nullable', 'url', 'max:500'],
        ]);

        $vendor->update($data);

        return response()->json(['message' => 'Shop profile updated.', 'data' => $vendor->fresh()]);
    }

    // ----- helpers -----

    private function validateProduct(Request $request, int $vendorId, ?int $ignoreId = null): array
    {
        $skuRule = ['nullable', 'string', 'max:50', 'unique:products,sku' . ($ignoreId ? ",{$ignoreId}" : '')];

        return $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'category_id'    => ['required', 'exists:categories,id'],
            'brand_id'       => ['nullable', 'exists:brands,id'],
            'description'    => ['nullable', 'string'],
            'specifications' => ['nullable', 'string'],
            'sku'            => $skuRule,
            'price'          => ['required', 'numeric', 'min:0.01'],
            'sale_price'     => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock'          => ['required', 'integer', 'min:0'],
            'images'         => ['nullable', 'array'],
            'images.*'       => ['url'],
        ]) + ['specifications' => null, 'brand_id' => null];
    }

    private function syncImages($product, Request $request): void
    {
        $urls = collect($request->input('images', []))
            ->filter(fn ($u) => filter_var($u, FILTER_VALIDATE_URL))
            ->unique()->values();

        $product->images()->delete();
        $urls->each(fn ($url, $i) => $product->images()->create([
            'image_path' => $url, 'is_primary' => $i === 0, 'position' => $i,
        ]));
    }
}
