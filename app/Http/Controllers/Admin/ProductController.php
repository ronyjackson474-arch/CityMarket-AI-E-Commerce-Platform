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
        $query = Product::with(['category:id,name', 'brand:id,name']);

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

        // Attach images
        if (! empty($data['images'])) {
            $this->syncImages($product, $data['images']);
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

        if (isset($data['images'])) {
            $this->syncImages($product, $data['images']);
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

    /**
     * Sync image URLs provided as a comma-separated string or array.
     */
    private function syncImages(Product $product, string|array $images): void
    {
        if (is_string($images)) {
            $images = array_filter(array_map('trim', explode(',', $images)));
        }

        if (empty($images)) {
            return;
        }

        // Clear existing and re-create (simple Phase 1 approach)
        $product->images()->delete();

        foreach ($images as $i => $url) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $url,
                'is_primary' => $i === 0,
                'position' => $i,
            ]);
        }
    }
}
