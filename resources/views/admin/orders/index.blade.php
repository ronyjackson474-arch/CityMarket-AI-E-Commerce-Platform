<x-layouts.admin title="Orders">
    <h2 class="text-xl font-bold text-ink-900 mb-5">Orders</h2>

    <form method="GET" class="flex gap-2 mb-4 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by order # or customer..."
            class="flex-1 max-w-xs px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
        <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
            <option value="">All statuses</option>
            @foreach(\App\Models\Order::STATUSES as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </form>

    <div class="bg-white rounded-xl border border-ink-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-ink-50 text-ink-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-5 py-3 font-medium">Order #</th>
                    <th class="text-left px-5 py-3 font-medium">Customer</th>
                    <th class="text-left px-5 py-3 font-medium">Items</th>
                    <th class="text-left px-5 py-3 font-medium">Total</th>
                    <th class="text-left px-5 py-3 font-medium">Payment</th>
                    <th class="text-left px-5 py-3 font-medium">Status</th>
                    <th class="text-left px-5 py-3 font-medium">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse($orders as $order)
                    <tr class="hover:bg-ink-50">
                        <td class="px-5 py-3"><a href="{{ route('admin.orders.show', $order) }}" class="text-brand-600 font-medium hover:underline">{{ $order->order_number }}</a></td>
                        <td class="px-5 py-3 text-ink-700">{{ $order->customer_name }}<div class="text-xs text-ink-400">{{ $order->customer_phone }}</div></td>
                        <td class="px-5 py-3 text-ink-600">{{ $order->items->count() }}</td>
                        <td class="px-5 py-3 font-semibold">₹{{ number_format($order->total) }}</td>
                        <td class="px-5 py-3 text-xs uppercase text-ink-500">{{ $order->payment_method }} · {{ $order->payment_status }}</td>
                        <td class="px-5 py-3">@include('admin.partials.status-badge', ['status' => $order->status])</td>
                        <td class="px-5 py-3 text-ink-500 text-xs">{{ $order->created_at->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-ink-400">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</x-layouts.admin>
