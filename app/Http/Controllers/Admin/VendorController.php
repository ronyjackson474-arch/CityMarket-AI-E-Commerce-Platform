<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorWithdrawal;
use App\Services\VendorEarningsService;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');

        $vendors = Vendor::with('user:id,name,email')
            ->withCount(['products' => fn ($p) => $p->where('is_active', true)])
            ->when(in_array($status, ['pending', 'approved', 'suspended']), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all'       => Vendor::count(),
            'pending'   => Vendor::where('status', 'pending')->count(),
            'approved'  => Vendor::where('status', 'approved')->count(),
            'suspended' => Vendor::where('status', 'suspended')->count(),
        ];

        return view('admin.vendors.index', compact('vendors', 'counts', 'status'));
    }

    public function approve(Vendor $vendor)
    {
        $vendor->update(['status' => 'approved']);

        return back()->with('success', "\"{$vendor->shop_name}\" approved — their shop is now live.");
    }

    public function suspend(Vendor $vendor)
    {
        $vendor->update(['status' => 'suspended']);

        return back()->with('success', "\"{$vendor->shop_name}\" suspended — shop and products hidden.");
    }

    public function updateCommission(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'commission_pct' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $vendor->update($data);

        return back()->with('success', "Commission for \"{$vendor->shop_name}\" set to {$vendor->commission_pct}%.");
    }

    public function payouts(Request $request)
    {
        $status = $request->query('status', 'all');

        $withdrawals = VendorWithdrawal::with('vendor.user:id,name,email')
            ->when(in_array($status, ['pending', 'approved', 'paid', 'rejected']), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.vendors.payouts', compact('withdrawals', 'status'));
    }

    public function approveWithdrawal(Request $request, VendorWithdrawal $withdrawal, VendorEarningsService $service)
    {
        $note = $request->validate(['note' => ['nullable', 'string', 'max:500']])['note'] ?? null;

        $service->approveWithdrawal($withdrawal, $note);

        return back()->with('success', 'Withdrawal of ₹' . number_format((float) $withdrawal->amount, 2)
            . ' marked as paid for "' . $withdrawal->vendor->shop_name . '".');
    }

    public function rejectWithdrawal(Request $request, VendorWithdrawal $withdrawal, VendorEarningsService $service)
    {
        $note = $request->validate(['note' => ['nullable', 'string', 'max:500']])['note'] ?? null;

        $service->rejectWithdrawal($withdrawal, $note);

        return back()->with('success', 'Withdrawal rejected — amount returned to the vendor\'s available balance.');
    }
}
