<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isVendor()) {
            return redirect()->route('vendor.sell')->with('error', 'Seller account required.');
        }

        $vendor = $user->vendor()->first();

        if (! $vendor || $vendor->status !== 'approved') {
            return redirect()->route('vendor.sell')
                ->with('error', $vendor && $vendor->status === 'suspended'
                    ? 'Your seller account is suspended. Contact support.'
                    : 'Your shop is pending admin approval.');
        }

        // Make the vendor available to controllers / views.
        $request->attributes->set('vendor', $vendor);
        view()->share('currentVendor', $vendor);

        return $next($request);
    }
}
