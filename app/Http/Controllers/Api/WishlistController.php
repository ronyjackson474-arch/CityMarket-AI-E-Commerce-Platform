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
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        $request->user()->wishlist()->syncWithoutDetaching([$product->id]);

        return response()->json([
            'message' => "{$product->name} added to wishlist.",
        ], 201);
    }

    public function remove(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $request->user()->wishlist()->detach($data['product_id']);

        return response()->json(['message' => 'Removed from wishlist.']);
    }
}
