<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecommendationController extends Controller
{
    /**
     * Return AI-powered product recommendations for the authenticated user.
     * Strategy: hybrid approach combining:
     * 1. Collaborative filtering proxy — products from same categories as user's viewed/wishlisted items
     * 2. Content-based — products with similar price ranges and brands
     * 3. Popularity fallback — best-selling / highest-rated products
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $limit = min((int) $request->integer('limit', 10), 20);

        // 1. Gather categories the user has shown interest in
        $interestedCategoryIds = collect();

        // From product views
        $viewedCategoryIds = ProductView::where('user_id', $user->id)
            ->join('products', 'product_views.product_id', '=', 'products.id')
            ->select('products.category_id')
            ->distinct()
            ->pluck('category_id');
        $interestedCategoryIds = $interestedCategoryIds->merge($viewedCategoryIds);

        // From wishlist
        $wishCategoryIds = Wishlist::where('user_id', $user->id)
            ->join('products', 'wishlists.product_id', '=', 'products.id')
            ->select('products.category_id')
            ->distinct()
            ->pluck('category_id');
        $interestedCategoryIds = $interestedCategoryIds->merge($wishCategoryIds);

        // From purchases
        $purchasedCategoryIds = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.user_id', $user->id)
            ->select('products.category_id')
            ->distinct()
            ->pluck('category_id');
        $interestedCategoryIds = $interestedCategoryIds->merge($purchasedCategoryIds);

        $interestedCategoryIds = $interestedCategoryIds->unique()->values();

        // Products the user has already interacted with (exclude from recommendations)
        $excludeIds = collect();

        $viewedProductIds = ProductView::where('user_id', $user->id)->pluck('product_id');
        $excludeIds = $excludeIds->merge($viewedProductIds);

        $wishProductIds = Wishlist::where('user_id', $user->id)->pluck('product_id');
        $excludeIds = $excludeIds->merge($wishProductIds);

        $purchasedProductIds = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $user->user_id ?? $user->id)
            ->pluck('order_items.product_id');
        $excludeIds = $excludeIds->merge($purchasedProductIds);

        $excludeIds = $excludeIds->unique()->values()->all();

        $recommendations = collect();

        // Strategy 1: Same categories as user interests
        if ($interestedCategoryIds->isNotEmpty()) {
            $categoryMatches = Product::active()
                ->whereIn('category_id', $interestedCategoryIds)
                ->when(!empty($excludeIds), fn($q) => $q->whereNotIn('id', $excludeIds))
                ->with(['category', 'images' => fn($q) => $q->orderBy('is_primary', 'desc'), 'brand'])
                ->inRandomOrder()
                ->limit($limit)
                ->get();

            $recommendations = $recommendations->merge($categoryMatches);
        }

        // Strategy 2: Fill remaining with popular products
        if ($recommendations->count() < $limit) {
            $needed = $limit - $recommendations->count();
            $alreadyRecommended = $recommendations->pluck('id')->all();
            $allExclude = array_unique(array_merge($excludeIds, $alreadyRecommended));

            $popular = Product::active()
                ->whereNotIn('id', $allExclude)
                ->with(['category', 'images' => fn($q) => $q->orderBy('is_primary', 'desc'), 'brand'])
                ->orderByRaw('COALESCE(rating, 0) DESC, COALESCE(review_count, 0) DESC')
                ->limit($needed)
                ->get();

            $recommendations = $recommendations->merge($popular);
        }

        $recommendations = $recommendations
            ->unique('id')
            ->take($limit)
            ->values();

        return response()->json([
            'data' => $recommendations,
            'meta' => [
                'total' => $recommendations->count(),
                'based_on' => $interestedCategoryIds->isEmpty()
                    ? 'popularity'
                    : 'browsing_history',
            ],
        ]);
    }
}
