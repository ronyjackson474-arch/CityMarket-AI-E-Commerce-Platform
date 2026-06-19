<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $products = Auth::user()
            ->wishlist()
            ->with(['category:id,name,slug', 'brand:id,name,slug', 'images' => fn ($q) => $q->orderBy('is_primary', 'desc')])
            ->paginate(20);

        return view('storefront.wishlist', compact('products'));
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        Auth::user()->wishlist()->syncWithoutDetaching([$validated['product_id']]);

        return back()->with('success', 'Added to wishlist.');
    }

    public function remove(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        Auth::user()->wishlist()->detach($validated['product_id']);

        return back()->with('success', 'Removed from wishlist.');
    }
}
