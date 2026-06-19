<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request)
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('position')
            ->limit(8)
            ->get();

        $flashDeals = Product::where('is_active', true)
            ->where('is_flash_deal', true)
            ->with(['images' => fn ($q) => $q->where('is_primary', true), 'category:id,name,slug'])
            ->limit(10)
            ->get();

        $featured = Product::where('is_active', true)
            ->where('is_featured', true)
            ->with(['images' => fn ($q) => $q->where('is_primary', true), 'category:id,name,slug'])
            ->limit(10)
            ->get();

        $trending = Product::where('is_active', true)
            ->with(['images' => fn ($q) => $q->where('is_primary', true), 'category:id,name,slug'])
            ->orderByDesc('review_count')
            ->limit(10)
            ->get();

        $brands = Brand::where('is_active', true)->limit(8)->get();

        // Simple hero banners (would come from DB in Phase 2)
        $banners = [
            ['title' => 'Mega Electronics Sale', 'subtitle' => 'Up to 40% off on top brands', 'image' => 'https://picsum.photos/seed/banner1/1200/400', 'bg' => 'from-blue-600 to-indigo-700'],
            ['title' => 'Fashion Fiesta', 'subtitle' => 'New season, new style', 'image' => 'https://picsum.photos/seed/banner2/1200/400', 'bg' => 'from-pink-500 to-rose-600'],
            ['title' => 'Home Makeover', 'subtitle' => 'Transform your space', 'image' => 'https://picsum.photos/seed/banner3/1200/400', 'bg' => 'from-amber-500 to-orange-600'],
        ];

        return view('storefront.home', compact('categories', 'flashDeals', 'featured', 'trending', 'brands', 'banners'));
    }
}
