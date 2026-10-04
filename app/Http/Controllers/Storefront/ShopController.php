<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only([
            'category', 'category_slug', 'brand', 'price_min', 'price_max',
            'min_rating', 'search', 'sort', 'on_sale',
        ]);

        $products = Product::query()
            ->where('is_active', true)
            ->applyFilters($filters)
            ->with(['category:id,name,slug', 'brand:id,name,slug', 'images' => fn ($q) => $q->where('is_primary', true)])
            ->paginate(20)
            ->withQueryString();

        // Filter sidebar data — lightweight queries, no caching needed.
        $categories = Category::where('is_active', true)->whereNull('parent_id')->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return view('storefront.shop', compact('products', 'categories', 'brands'));
    }

    public function category(Request $request, string $slug)
    {
        $category = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $request->merge(['category_slug' => $slug]);

        return $this->index($request)->with('currentCategory', $category);
    }

    public function product(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['category', 'brand', 'images', 'reviews.user:id,name', 'reviews' => fn ($q) => $q->latest()->limit(5)])
            ->firstOrFail();

        // Hide products of suspended vendors from the public storefront.
        if ($product->vendor_id && optional($product->vendor)->status !== 'approved') {
            abort(404);
        }

        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with(['category:id,name,slug', 'brand:id,name,slug', 'images' => fn ($q) => $q->where('is_primary', true)])
            ->inRandomOrder()
            ->limit(8)
            ->get();

        // Record the view for recommendations (fire-and-forget).
        if (auth()->check()) {
            \App\Models\ProductView::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
            ]);
        }

        return view('storefront.product-detail', compact('product', 'related'));
    }
}
