<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminApiController extends Controller
{
    /** Guard: admin-only. */
    private function guard(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Admin access required.');
    }

    /** GET /api/admin/dashboard */
    public function dashboard(Request $request)
    {
        $this->guard($request);

        return response()->json(['data' => [
            'users' => User::where('role', 'customer')->count(),
            'orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'products' => Product::count(),
            'low_stock' => Product::where('stock', '<', 10)->count(),
            'vendors' => Vendor::where('status', 'approved')->count(),
            'pending_vendors' => Vendor::where('status', 'pending')->count(),
            'revenue' => (float) Order::whereIn('status', ['confirmed', 'packed', 'shipped', 'delivered'])->sum('total'),
        ]]);
    }

    // ----- Vendors -----

    /** GET /api/admin/vendors?status=pending */
    public function vendors(Request $request)
    {
        $this->guard($request);

        $vendors = Vendor::with('user:id,name,email')
            ->withCount(['products' => fn ($p) => $p->where('is_active', true)])
            ->when(in_array($request->query('status'), ['pending', 'approved', 'suspended']),
                fn ($q) => $q->where('status', $request->query('status')))
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        return response()->json(['data' => $vendors->items(), 'meta' => [
            'current_page' => $vendors->currentPage(), 'last_page' => $vendors->lastPage(), 'total' => $vendors->total(),
        ]]);
    }

    /** POST /api/admin/vendors/{vendor}/approve */
    public function approveVendor(Request $request, Vendor $vendor)
    {
        $this->guard($request);
        $vendor->update(['status' => 'approved']);

        return response()->json(['message' => "Vendor \"{$vendor->shop_name}\" approved.", 'data' => $vendor]);
    }

    /** POST /api/admin/vendors/{vendor}/suspend */
    public function suspendVendor(Request $request, Vendor $vendor)
    {
        $this->guard($request);
        $vendor->update(['status' => 'suspended']);

        return response()->json(['message' => "Vendor \"{$vendor->shop_name}\" suspended.", 'data' => $vendor]);
    }

    /** POST /api/admin/vendors/{vendor}/commission */
    public function commission(Request $request, Vendor $vendor)
    {
        $this->guard($request);

        $data = $request->validate(['commission_pct' => ['required', 'integer', 'min:0', 'max:100']]);
        $vendor->update($data);

        return response()->json(['message' => 'Commission updated.', 'data' => $vendor]);
    }

    // ----- Products -----

    /** GET /api/admin/products?search= */
    public function products(Request $request)
    {
        $this->guard($request);

        $products = Product::with(['category:id,name', 'brand:id,name', 'vendor:id,shop_name,slug'])
            ->when($request->query('search'), function ($q, $s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%");
            })
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        return response()->json(['data' => $products->items(), 'meta' => [
            'current_page' => $products->currentPage(), 'last_page' => $products->lastPage(), 'total' => $products->total(),
        ]]);
    }

    /** PATCH /api/admin/products/{product}/toggle — approve/hide. */
    public function toggleProduct(Request $request, Product $product)
    {
        $this->guard($request);
        $product->update(['is_active' => ! $product->is_active]);

        return response()->json([
            'message' => $product->is_active ? 'Product approved and live.' : 'Product hidden.',
            'data' => $product,
        ]);
    }

    /** DELETE /api/admin/products/{product} */
    public function destroyProduct(Request $request, Product $product)
    {
        $this->guard($request);
        $product->delete();

        return response()->json(['message' => 'Product deleted.']);
    }

    // ----- Orders -----

    /** GET /api/admin/orders?status= */
    public function orders(Request $request)
    {
        $this->guard($request);

        $orders = Order::with('user:id,name', 'items')
            ->when(in_array($request->query('status'), ['pending', 'confirmed', 'packed', 'shipped', 'delivered', 'cancelled', 'returned']),
                fn ($q) => $q->where('status', $request->query('status')))
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        return response()->json(['data' => $orders->items(), 'meta' => [
            'current_page' => $orders->currentPage(), 'last_page' => $orders->lastPage(), 'total' => $orders->total(),
        ]]);
    }

    /** GET /api/admin/orders/{order} */
    public function showOrder(Request $request, Order $order)
    {
        $this->guard($request);

        return response()->json(['data' => $order->load('user:id,name,email', 'items.product:id,name,slug')]);
    }

    /** POST /api/admin/orders/{order}/status */
    public function orderStatus(Request $request, Order $order)
    {
        $this->guard($request);

        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'confirmed', 'packed', 'shipped', 'delivered', 'cancelled'])],
        ]);

        $previous = $order->status;
        $order->update($data);

        // Record vendor earnings the moment an order is delivered.
        if ($data['status'] === 'delivered' && $previous !== 'delivered') {
            app(\App\Services\VendorEarningsService::class)->recordForOrder($order->refresh());
        }

        // Notify the customer about the status change.
        if ($order->user_id && $previous !== $order->status) {
            \App\Models\SiteNotification::notify(
                $order->user_id,
                'order_status',
                "Order {$order->order_number} is now {$order->status}",
                "Your order {$order->order_number} status changed to {$order->status}.",
                "/account/orders/{$order->order_number}"
            );
        }

        return response()->json(['message' => "Order {$order->order_number} → {$order->status}.", 'data' => $order]);
    }

    // ----- Returns -----

    /** GET /api/admin/returns */
    public function returns(Request $request)
    {
        $this->guard($request);

        $returns = ReturnRequest::with('order:id,order_number,status')
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        return response()->json(['data' => $returns->items(), 'meta' => [
            'current_page' => $returns->currentPage(), 'last_page' => $returns->lastPage(), 'total' => $returns->total(),
        ]]);
    }

    /** POST /api/admin/returns/{returnRequest}/action — approve|reject */
    public function returnAction(Request $request, ReturnRequest $returnRequest)
    {
        $this->guard($request);

        $data = $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject'])],
        ]);

        if ($data['action'] === 'approve') {
            $returnRequest->update([
                'status' => 'approved',
                'processed_at' => now(),
            ]);

            // Refund to the customer's wallet and mark the order returned
            // (parity with the web admin panel flow).
            $wallet = app(\App\Services\WalletService::class);
            $wallet->refundOrder($returnRequest->order, (float) $returnRequest->refund_amount, 'return_refund');
            $returnRequest->order->update(['status' => \App\Models\Order::STATUS_RETURNED]);
            $returnRequest->update(['status' => 'completed']);

            return response()->json([
                'message' => 'Return approved. Refund credited to wallet.',
                'data' => $returnRequest->fresh(),
            ]);
        }

        $returnRequest->update([
            'status' => 'rejected',
            'processed_at' => now(),
        ]);

        return response()->json(['message' => 'Return rejected.', 'data' => $returnRequest]);
    }

    // ----- Vendor payouts -----

    /** GET /api/admin/payouts */
    public function payouts(Request $request)
    {
        $this->guard($request);

        $list = \App\Models\VendorWithdrawal::with('vendor:id,shop_name,user_id')
            ->when(in_array($request->query('status'), ['pending', 'approved', 'paid', 'rejected']),
                fn ($q) => $q->where('status', $request->query('status')))
            ->latest('id')
            ->paginate((int) $request->query('per_page', 15));

        return response()->json(['data' => $list->items(), 'meta' => [
            'current_page' => $list->currentPage(),
            'last_page' => $list->lastPage(),
            'total' => $list->total(),
        ]]);
    }

    /** POST /api/admin/payouts/{withdrawal}/approve — mark as paid. */
    public function approvePayout(Request $request, \App\Models\VendorWithdrawal $withdrawal)
    {
        $this->guard($request);

        $withdrawal = app(\App\Services\VendorEarningsService::class)
            ->approveWithdrawal($withdrawal, $request->input('note'));

        return response()->json(['message' => 'Payout marked as paid.', 'data' => $withdrawal]);
    }

    /** POST /api/admin/payouts/{withdrawal}/reject — return funds to vendor balance. */
    public function rejectPayout(Request $request, \App\Models\VendorWithdrawal $withdrawal)
    {
        $this->guard($request);

        $withdrawal = app(\App\Services\VendorEarningsService::class)
            ->rejectWithdrawal($withdrawal, $request->input('note'));

        return response()->json(['message' => 'Payout rejected — funds returned to vendor balance.', 'data' => $withdrawal]);
    }

    // ----- Users -----

    /** GET /api/admin/users?search= */
    public function users(Request $request)
    {
        $this->guard($request);

        $users = User::query()
            ->when($request->query('search'), function ($q, $s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%");
            })
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        return response()->json(['data' => $users->items(), 'meta' => [
            'current_page' => $users->currentPage(), 'last_page' => $users->lastPage(), 'total' => $users->total(),
        ]]);
    }

    /** POST /api/admin/users/{user}/toggle-status — activate/deactivate. */
    public function toggleUser(Request $request, User $user)
    {
        $this->guard($request);
        abort_if($user->isAdmin(), 403, 'Cannot deactivate an admin.');

        $user->update(['is_active' => ! $user->is_active]);

        return response()->json([
            'message' => $user->is_active ? 'User activated.' : 'User deactivated.',
            'data' => $user,
        ]);
    }
}
