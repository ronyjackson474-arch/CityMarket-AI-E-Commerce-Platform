<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
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

        return response()->json($products);
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['category', 'brand', 'images', 'reviews.user:id,name', 'reviews' => fn ($q) => $q->latest()])
            ->first();

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        // Related products from the same category
        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with(['images' => fn ($q) => $q->where('is_primary', true)])
            ->limit(6)
            ->get();

        return response()->json([
            'data' => $product,
            'related' => $related,
        ]);
    }

    public function featured()
    {
        $products = Product::where('is_active', true)
            ->where('is_featured', true)
            ->with(['images' => fn ($q) => $q->where('is_primary', true), 'category:id,name,slug', 'brand:id,name,slug'])
            ->limit(10)
            ->get();

        return response()->json(['data' => $products]);
    }

    public function flashDeals()
    {
        $products = Product::where('is_active', true)
            ->where('is_flash_deal', true)
            ->with(['images' => fn ($q) => $q->where('is_primary', true), 'category:id,name,slug', 'brand:id,name,slug'])
            ->limit(10)
            ->get();

        return response()->json(['data' => $products]);
    }
}
