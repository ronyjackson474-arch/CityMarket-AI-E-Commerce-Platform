<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorShopController extends Controller
{
    /** Vendor directory: all approved vendors. */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $vendors = Vendor::approved()
            ->withCount(['products' => fn ($p) => $p->where('is_active', true)])
            ->when($q !== '', fn ($query) => $query->where('shop_name', 'like', "%{$q}%"))
            ->orderByDesc('rating')
            ->paginate(12)
            ->withQueryString();

        return view('storefront.vendors', compact('vendors', 'q'));
    }

    /** Public vendor shop page: banner + that vendor's active products. */
    public function show(Request $request, string $slug)
    {
        $vendor = Vendor::approved()
            ->where('slug', $slug)
            ->withCount(['products' => fn ($p) => $p->where('is_active', true)])
            ->firstOrFail();

        $q = trim((string) $request->query('q'));

        $products = $vendor->products()
            ->where('is_active', true)
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->with([
                'images' => fn ($i) => $i->where('is_primary', true),
                'category:id,name,slug',
                'brand:id,name,slug',
            ])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('storefront.vendor-shop', compact('vendor', 'products', 'q'));
    }
}
