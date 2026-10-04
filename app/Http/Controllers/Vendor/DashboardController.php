<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    /** The authenticated vendor (guaranteed approved by middleware). */
    protected function vendor(): Vendor
    {
        return Auth::user()->vendor()->firstOrFail();
    }

    public function dashboard()
    {
        $vendor = $this->vendor();

        $stats = [
            'products'      => $vendor->products()->count(),
            'active'        => $vendor->products()->where('is_active', true)->count(),
            'pending'       => $vendor->products()->where('is_active', false)->count(),
            'units_sold'    => (int) OrderItem::whereHas('product', fn ($q) => $q->where('vendor_id', $vendor->id))->sum('quantity'),
            'revenue'       => (float) (OrderItem::query()
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('products.vendor_id', $vendor->id)
                ->selectRaw('COALESCE(SUM(order_items.price * order_items.quantity), 0) AS revenue')
                ->value('revenue') ?? 0),
        ];

        $recentProducts = $vendor->products()->with('images')->latest()->limit(5)->get();
        $lowStock = $vendor->products()->where('stock', '<=', 5)->where('is_active', true)->orderBy('stock')->limit(5)->get();

        return view('vendor.dashboard', compact('vendor', 'stats', 'recentProducts', 'lowStock'));
    }

    public function products()
    {
        $products = $this->vendor()->products()
            ->with('images')
            ->latest()
            ->paginate(10);

        return view('vendor.products.index', compact('products'));
    }

    public function createProduct()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return view('vendor.products.form', compact('categories', 'brands'));
    }

    public function storeProduct(Request $request)
    {
        $data = $this->validateProduct($request);

        $product = $this->vendor()->products()->create($data + ['is_active' => false]);
        $this->syncImages($product, $request);

        return redirect()->route('vendor.products.index')
            ->with('success', 'Product submitted! It will appear in your shop after admin approval.');
    }

    public function editProduct(Product $product)
    {
        $this->authorizeProduct($product);

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return view('vendor.products.form', ['product' => $product, 'categories' => $categories, 'brands' => $brands]);
    }

    public function updateProduct(Request $request, Product $product)
    {
        $this->authorizeProduct($product);

        $data = $this->validateProduct($request, $product->id);

        // Edits to an approved product push it back into review.
        $product->update($data + ['is_active' => false]);
        $this->syncImages($product, $request);

        return redirect()->route('vendor.products.index')
            ->with('success', 'Product updated — sent back for admin approval.');
    }

    public function destroyProduct(Product $product)
    {
        $this->authorizeProduct($product);
        $product->delete();

        return redirect()->route('vendor.products.index')->with('success', 'Product deleted.');
    }

    public function orders()
    {
        $vendor = $this->vendor();

        $items = OrderItem::whereHas('product', fn ($q) => $q->where('vendor_id', $vendor->id))
            ->with(['order:id,order_number,status,created_at,user_id', 'product:id,name,slug'])
            ->latest('id')
            ->paginate(15);

        return view('vendor.orders', compact('items'));
    }

    public function profile()
    {
        return view('vendor.profile', ['vendor' => $this->vendor()]);
    }

    public function updateProfile(Request $request)
    {
        $vendor = $this->vendor();

        $data = $request->validate([
            'shop_name'   => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo_url'    => ['nullable', 'url', 'max:500'],
            'banner_url'  => ['nullable', 'url', 'max:500'],
        ]);

        $vendor->update($data);

        return redirect()->route('vendor.profile')->with('success', 'Shop profile updated.');
    }

    // ----- helpers -----

    protected function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        $skuRule = ['nullable', 'string', 'max:50'];
        if ($ignoreId) {
            $skuRule[] = 'unique:products,sku,' . $ignoreId;
        } else {
            $skuRule[] = 'unique:products,sku';
        }

        return $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'category_id'   => ['required', 'exists:categories,id'],
            'brand_id'      => ['nullable', 'exists:brands,id'],
            'description'   => ['nullable', 'string'],
            'specifications'=> ['nullable', 'string'],
            'sku'           => $skuRule,
            'price'         => ['required', 'numeric', 'min:0.01'],
            'sale_price'    => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock'         => ['required', 'integer', 'min:0'],
        ]) + ['specifications' => null];
    }

    protected function syncImages(Product $product, Request $request): void
    {
        $raw = $request->input('images', '');
        $urls = collect(is_array($raw) ? $raw : array_filter(array_map('trim', explode(',', (string) $raw))))
            ->filter(fn ($u) => filter_var($u, FILTER_VALIDATE_URL))
            ->unique()
            ->values();

        $product->images()->delete();

        $urls->each(fn ($url, $i) => $product->images()->create([
            'image_path' => $url,
            'is_primary' => $i === 0,
            'position'   => $i,
        ]));
    }

    protected function authorizeProduct(Product $product): void
    {
        $vendorId = $this->vendor()->id;
        abort_unless($product->vendor_id === $vendorId, 404);
    }
}
