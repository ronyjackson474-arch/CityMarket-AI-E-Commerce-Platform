<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VendorController extends Controller
{
    public function showSell()
    {
        $vendor = Auth::user()?->vendor;

        return view('storefront.sell', [
            'vendor' => $vendor,
            'vendorCount' => Vendor::approved()->count(),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'shop_name'   => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo_url'    => ['nullable', 'url', 'max:500'],
        ]);

        $user = Auth::user();

        abort_if($user->vendor, 403, 'You already have a vendor account.');

        $slug = Str::slug($data['shop_name']);
        if (Vendor::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::lower(Str::random(4));
        }

        // Promote the account to vendor role.
        $user->update(['role' => 'vendor']);

        $vendor = Vendor::create([
            'user_id'     => $user->id,
            'shop_name'   => $data['shop_name'],
            'slug'        => $slug,
            'logo_url'    => $data['logo_url'] ?? null,
            'description' => $data['description'] ?? null,
            'status'      => 'pending',
        ]);

        return redirect()
            ->route('vendor.sell')
            ->with('success', "Application received! Your shop \"{$vendor->shop_name}\" is pending admin approval.");
    }
}
