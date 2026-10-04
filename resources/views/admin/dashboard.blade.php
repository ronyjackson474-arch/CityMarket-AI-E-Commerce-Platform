<x-layouts.admin title="Dashboard">
    <!-- Stats cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['Total Revenue', '₹' . number_format($stats['revenue']), 'bg-green-500', '💰'],
            ['Total Orders', $stats['orders'], 'bg-blue-500', '🧾'],
            ['Customers', $stats['users'], 'bg-purple-500', '👥'],
            ['Products', $stats['products'], 'bg-amber-500', '📦'],
            ['Pending Orders', $stats['pending_orders'], 'bg-orange-500', '⏳'],
            ['Low Stock Items', $stats['low_stock'], 'bg-red-500', '⚠️'],
            ['Active Vendors', $stats['vendors'], 'bg-teal-500', '🏪'],
            ['Pending Vendor Approvals', $stats['pending_vendors'], 'bg-fuchsia-500', '📝'],
        ] as $stat)
            <div class="bg-white rounded-xl border border-ink-100 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs text-ink-500 uppercase tracking-wide font-medium">{{ $stat[0] }}</div>
                        <div class="text-2xl font-bold text-ink-900 mt-1">{{ $stat[1] }}</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl {{ $stat[2] }} flex items-center justify-center text-2xl">{{ $stat[3] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Recent Orders -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-ink-100">
            <div class="p-5 border-b border-ink-100 flex items-center justify-between">
                <h3 class="font-bold text-ink-900">Recent Orders</h3>
                <a href="{{ route('admin.orders.index') }}" class="text-sm text-brand-600 hover:underline">View all →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-ink-50 text-ink-500 text-xs uppercase">
                        <tr>
                            <th class="text-left px-5 py-3 font-medium">Order #</th>
                            <th class="text-left px-5 py-3 font-medium">Customer</th>
                            <th class="text-left px-5 py-3 font-medium">Total</th>
                            <th class="text-left px-5 py-3 font-medium">Status</th>
                            <th class="text-left px-5 py-3 font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-ink-50">
                                <td class="px-5 py-3">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-brand-600 font-medium hover:underline">{{ $order->order_number }}</a>
                                </td>
                                <td class="px-5 py-3 text-ink-700">{{ $order->customer_name }}</td>
                                <td class="px-5 py-3 font-semibold">₹{{ number_format($order->total) }}</td>
                                <td class="px-5 py-3">
                                    @include('admin.partials.status-badge', ['status' => $order->status])
                                </td>
                                <td class="px-5 py-3 text-ink-500 text-xs">{{ $order->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-ink-400">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low stock alerts -->
        <div class="bg-white rounded-xl border border-ink-100">
            <div class="p-5 border-b border-ink-100">
                <h3 class="font-bold text-ink-900 flex items-center gap-2">⚠️ Low Stock</h3>
            </div>
            <div class="p-5 space-y-3">
                @forelse($lowStockProducts as $product)
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-ink-800 truncate">{{ $product->name }}</div>
                            <div class="text-xs text-ink-500">{{ $product->category?->name }}</div>
                        </div>
                        <div class="text-sm font-bold @if($product->stock == 0) text-red-600 @else text-amber-600 @endif">{{ $product->stock }} left</div>
                    </div>
                @empty
                    <p class="text-sm text-ink-400 text-center py-4">All products well stocked ✅</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Sales chart -->
    @if($salesData->isNotEmpty())
    <div class="bg-white rounded-xl border border-ink-100 mt-6 p-5">
        <h3 class="font-bold text-ink-900 mb-4">Sales (Last 7 Days)</h3>
        <div class="flex items-end gap-2 h-40">
            @php
                $maxSale = $salesData->max('total') ?: 1;
            @endphp
            @foreach($salesData as $day)
                <div class="flex-1 flex flex-col items-center gap-1">
                    <div class="w-full bg-gradient-to-t from-brand-600 to-brand-400 rounded-t-md transition-all hover:opacity-80" style="height: {{ ($day->total / $maxSale) * 100 }}%" title="₹{{ number_format($day->total) }} — {{ $day->count }} orders"></div>
                    <div class="text-xs text-ink-500">{{ \Carbon\Carbon::parse($day->date)->format('D') }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</x-layouts.admin>
