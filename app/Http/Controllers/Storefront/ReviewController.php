<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $existing = Review::where('product_id', $product->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($existing) {
            return back()->with('error', 'You have already reviewed this product.');
        }

        $isVerified = Auth::user()->orders()
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->exists();

        Review::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'] ?? null,
            'is_verified_purchase' => $isVerified,
        ]);

        return back()->with('success', 'Thank you! Your review has been posted.');
    }
}
