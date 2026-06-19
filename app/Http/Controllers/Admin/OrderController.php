<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Services\WalletService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user:id,name', 'items');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('search')) {
            $query->where('order_number', 'like', "%{$search}%")
                ->orWhere('customer_name', 'like', "%{$search}%");
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('user', 'items.product');

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', Order::STATUSES)],
        ]);

        $order->update(['status' => $data['status']]);

        return back()->with('success', "Order status updated to {$data['status']}.");
    }

    public function returns()
    {
        $returns = ReturnRequest::with('order.user:id,name', 'order:id,order_number,total,user_id', 'user:id,name')
            ->latest()
            ->paginate(15);

        return view('admin.returns.index', compact('returns'));
    }

    public function handleReturn(Request $request, ReturnRequest $returnRequest, WalletService $walletService)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject'],
        ]);

        if ($validated['action'] === 'approve') {
            $returnRequest->update(['status' => ReturnRequest::STATUS_APPROVED]);

            // Refund to wallet
            $walletService->refundOrder($returnRequest->order, $returnRequest->refund_amount, 'return_refund');

            // Update order status
            $returnRequest->order->update(['status' => Order::STATUS_RETURNED]);

            $returnRequest->update(['status' => ReturnRequest::STATUS_COMPLETED]);

            return back()->with('success', 'Return approved. Refund of ₹' . number_format($returnRequest->refund_amount, 2) . ' credited to wallet.');
        }

        $returnRequest->update(['status' => ReturnRequest::STATUS_REJECTED]);

        return back()->with('success', 'Return request rejected.');
    }
}
