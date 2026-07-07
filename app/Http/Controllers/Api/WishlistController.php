<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $products = $request->user()
            ->wishlist()
            ->with(['category:id,name,slug', 'brand:id,name,slug', 'images' => fn ($q) => $q->where('is_primary', true)])
            ->paginate(20);

        return response()->json($products);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_slug' => ['string', 'exists:products,slug'],
            'product_id' => ['integer', 'exists:products,id'],
        ]);

        if (empty($data['product_slug']) && empty($data['product_id'])) {
            return response()->json(['message' => 'Either product_slug or product_id is required.'], 422);
        }

        $product = $this->resolveProduct($data);

        $request->user()->wishlist()->syncWithoutDetaching([$product->id]);

        return response()->json([
            "message" => "{$product->name} added to wishlist.",
        ], 201);
    }

    public function remove(Request $request)
    {
        $data = $request->validate([
            'product_slug' => ['string', 'exists:products,slug'],
            'product_id' => ['integer', 'exists:products,id'],
        ]);

        if (empty($data['product_slug']) && empty($data['product_id'])) {
            return response()->json(['message' => 'Either product_slug or product_id is required.'], 422);
        }

        $request->user()->wishlist()->detach($this->resolveProduct($data)->id);

        return response()->json(['message' => 'Removed from wishlist.']);
    }

    protected function resolveProduct(array $data): Product
    {
        if (! empty($data['product_slug'])) {
            return Product::where('slug', $data['product_slug'])->firstOrFail();
        }

        return Product::findOrFail($data['product_id']);
    }
}
