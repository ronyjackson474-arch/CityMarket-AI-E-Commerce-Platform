<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category:id,name', 'brand:id,name', 'vendor:id,shop_name,slug']);

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'brands'));
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']) . '-' . Str::random(6);
        $data['sku'] = $data['sku'] ?? strtoupper(Str::random(10));

        $product = Product::create($data);

        // Attach images (uploaded files + URLs)
        if (! empty($data['images']) || $request->hasFile('image_files')) {
            $this->syncImages($request, $product, $data['images'] ?? '');
        }

        return redirect()->route('admin.products.index')
            ->with('success', "Product \"{$product->name}\" created.");
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $product->load('images');

        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $product->update($data);

        if (isset($data['images']) || $request->hasFile('image_files')) {
            $this->syncImages($request, $product, $data['images'] ?? '');
        }

        return redirect()->route('admin.products.index')
            ->with('success', "Product \"{$product->name}\" updated.");
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', "Product \"{$product->name}\" deleted.");
    }

    /** Approve / hide a product (used for vendor product approval). */
    public function toggle(Product $product)
    {
        $product->update(['is_active' => ! $product->is_active]);

        $msg = $product->is_active
            ? "Product \"{$product->name}\" approved and now live."
            : "Product \"{$product->name}\" hidden.";

        return redirect()->route('admin.products.index')->with('success', $msg);
    }

    /**
     * Sync product images from uploaded files + URLs (comma-separated string or array).
     */
    private function syncImages(ProductRequest $request, Product $product, string|array $images): void
    {
        if (is_string($images)) {
            $images = array_filter(array_map('trim', explode(',', $images)));
        }

        $paths = collect($images)->filter(fn ($u) => $u !== '');

        if ($request->hasFile('image_files')) {
            foreach ($request->file('image_files') as $file) {
                if ($file->isValid()) {
                    $paths->push('/storage/' . $file->store('products', 'public'));
                }
            }
        }

        if ($paths->isEmpty()) {
            return;
        }

        // Clear existing and re-create (simple Phase 1 approach)
        $product->images()->delete();

        foreach ($paths->values()->all() as $i => $path) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
                'is_primary' => $i === 0,
                'position' => $i,
            ]);
        }
    }
}
