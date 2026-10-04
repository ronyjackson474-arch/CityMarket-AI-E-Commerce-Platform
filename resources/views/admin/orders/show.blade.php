<x-layouts.admin title="Order {{ $order->order_number }}">
    <div class="mb-5">
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-brand-600 hover:underline">← Back to Orders</a>
        <div class="flex items-center justify-between mt-1">
            <h2 class="text-xl font-bold text-ink-900">Order {{ $order->order_number }}</h2>
            @include('admin.partials.status-badge', ['status' => $order->status])
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Items -->
            <div class="bg-white rounded-xl border border-ink-100">
                <div class="p-5 border-b border-ink-100"><h3 class="font-bold text-ink-900">Order Items</h3></div>
                <table class="w-full text-sm">
                    <thead class="bg-ink-50 text-ink-500 text-xs uppercase">
                        <tr><th class="text-left px-5 py-2 font-medium">Product</th><th class="text-right px-5 py-2 font-medium">Price</th><th class="text-right px-5 py-2 font-medium">Qty</th><th class="text-right px-5 py-2 font-medium">Total</th></tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="px-5 py-3">
                                    <div class="font-medium text-ink-800">{{ $item->product_name }}</div>
                                    <div class="text-xs text-ink-400">{{ $item->product?->sku ?? '—' }}</div>
                                </td>
                                <td class="px-5 py-3 text-right text-ink-600">₹{{ number_format($item->price) }}</td>
                                <td class="px-5 py-3 text-right text-ink-600">{{ $item->quantity }}</td>
                                <td class="px-5 py-3 text-right font-semibold">₹{{ number_format($item->line_total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Status update -->
            <div class="bg-white rounded-xl border border-ink-100 p-5">
                <h3 class="font-bold text-ink-900 mb-3">Update Status</h3>
                <form action="{{ route('admin.orders.status', $order) }}" method="POST">
                    @csrf
                    <div class="flex gap-2 flex-wrap">
                        @foreach(\App\Models\Order::STATUSES as $status)
                            <button type="submit" name="status" value="{{ $status }}"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $order->status === $status ? 'bg-brand-600 text-white' : 'bg-ink-100 text-ink-700 hover:bg-ink-200' }}">
                                {{ ucfirst($status) }}
                            </button>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar info -->
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-ink-100 p-5">
                <h3 class="font-bold text-ink-900 mb-3">Customer</h3>
                <div class="text-sm space-y-1">
                    <div class="font-medium text-ink-800">{{ $order->customer_name }}</div>
                    <div class="text-ink-500">{{ $order->customer_phone }}</div>
                    @if($order->user)<div class="text-ink-400 text-xs">{{ $order->user->email }}</div>@endif
                </div>
            </div>

            <div class="bg-white rounded-xl border border-ink-100 p-5">
                <h3 class="font-bold text-ink-900 mb-3">Shipping Address</h3>
                <p class="text-sm text-ink-600">{{ $order->shipping_address }}</p>
            </div>

            @if($order->notes)
            <div class="bg-white rounded-xl border border-ink-100 p-5">
                <h3 class="font-bold text-ink-900 mb-2">Order Notes</h3>
                <p class="text-sm text-ink-600">{{ $order->notes }}</p>
            </div>
            @endif

            <div class="bg-white rounded-xl border border-ink-100 p-5">
                <h3 class="font-bold text-ink-900 mb-3">Payment</h3>
                <div class="text-sm space-y-1">
                    <div class="flex justify-between"><span class="text-ink-500">Method</span><span class="uppercase font-medium">{{ $order->payment_method }}</span></div>
                    <div class="flex justify-between"><span class="text-ink-500">Status</span><span class="uppercase font-medium">{{ $order->payment_status }}</span></div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-ink-100 p-5">
                <h3 class="font-bold text-ink-900 mb-3">Totals</h3>
                <div class="text-sm space-y-1.5">
                    <div class="flex justify-between"><span class="text-ink-500">Subtotal</span><span>₹{{ number_format($order->subtotal) }}</span></div>
                    @if($order->discount > 0)<div class="flex justify-between text-green-600"><span>Discount{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}</span><span>-₹{{ number_format($order->discount) }}</span></div>@endif
                    <div class="flex justify-between"><span class="text-ink-500">Shipping</span><span>₹{{ number_format($order->shipping) }}</span></div>
                    <div class="flex justify-between font-bold border-t border-ink-100 pt-1.5 mt-1"><span class="text-ink-900">Total</span><span class="text-brand-600">₹{{ number_format($order->total) }}</span></div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
