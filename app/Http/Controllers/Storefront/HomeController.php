<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /** Eager-load relations every product card needs (avoids N+1). */
    private const PRODUCT_RELATIONS = [
        'images' => 'where(is_primary, true)',
        'category:id,name,slug',
        'brand:id,name,slug',
    ];

    public function __invoke(Request $request)
    {
        // Product block queries with eager-loaded relations (avoids N+1).
        $with = $this->productWith();

        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('position')
            ->limit(8)
            ->get();

        $flashDeals = Product::where('is_active', true)
            ->where('is_flash_deal', true)
            ->with($with)
            ->limit(10)
            ->get();

        $featured = Product::where('is_active', true)
            ->where('is_featured', true)
            ->with($with)
            ->limit(10)
            ->get();

        $trending = Product::where('is_active', true)
            ->with($with)
            ->orderByDesc('review_count')
            ->limit(10)
            ->get();

        $brands = Brand::where('is_active', true)->limit(8)->get();

        // AI-powered recommendations for logged-in users (never cached).
        $recommendations = collect();
        if ($request->user()) {
            $user = $request->user();

            $interestedCategoryIds = collect()
                ->merge(
                    ProductView::where('user_id', $user->id)
                        ->join('products', 'product_views.product_id', '=', 'products.id')
                        ->select('products.category_id')
                        ->distinct()
                        ->pluck('category_id')
                )
                ->merge(
                    Wishlist::where('user_id', $user->id)
                        ->join('products', 'wishlists.product_id', '=', 'products.id')
                        ->select('products.category_id')
                        ->distinct()
                        ->pluck('category_id')
                )
                ->merge(
                    DB::table('orders')
                        ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                        ->join('products', 'order_items.product_id', '=', 'products.id')
                        ->where('orders.user_id', $user->id)
                        ->select('products.category_id')
                        ->distinct()
                        ->pluck('category_id')
                )
                ->unique()
                ->values();

            if ($interestedCategoryIds->isNotEmpty()) {
                $excludeIds = collect()
                    ->merge(ProductView::where('user_id', $user->id)->pluck('product_id'))
                    ->merge(Wishlist::where('user_id', $user->id)->pluck('product_id'))
                    ->unique()
                    ->all();

                $recommendations = Product::where('is_active', true)
                    ->whereIn('category_id', $interestedCategoryIds)
                    ->when(!empty($excludeIds), fn ($q) => $q->whereNotIn('id', $excludeIds))
                    ->with($this->productWith())
                    ->inRandomOrder()
                    ->limit(10)
                    ->get();
            }

            if ($recommendations->isEmpty()) {
                $recommendations = $featured;
            }
        }

        $banners = [
            ['title' => 'Mega Electronics Sale', 'subtitle' => 'Up to 40% off on top brands', 'image' => 'https://picsum.photos/seed/banner1/1200/400', 'bg' => 'from-blue-600 to-indigo-700'],
            ['title' => 'Fashion Fiesta', 'subtitle' => 'New season, new style', 'image' => 'https://picsum.photos/seed/banner2/1200/400', 'bg' => 'from-pink-500 to-rose-600'],
            ['title' => 'Home Makeover', 'subtitle' => 'Transform your space', 'image' => 'https://picsum.photos/seed/banner3/1200/400', 'bg' => 'from-amber-500 to-orange-600'],
        ];

        return view('storefront.home', compact('categories', 'flashDeals', 'featured', 'trending', 'brands', 'banners', 'recommendations'));
    }

    /** Helper: the with() array for product queries — eager-loads relations
     * the product-card component always accesses. */
    private function productWith(): array
    {
        return [
            'images' => fn ($q) => $q->where('is_primary', true),
            'category:id,name,slug',
            'brand:id,name,slug',
        ];
    }
}
