<x-layouts.vendor title="Dashboard">
    <!-- Stat cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        @foreach([
            ['label' => 'Total Products', 'value' => number_format($stats['products']), 'icon' => '📦', 'bg' => 'bg-blue-50 text-blue-600'],
            ['label' => 'Live (Approved)', 'value' => number_format($stats['active']), 'icon' => '✅', 'bg' => 'bg-green-50 text-green-600'],
            ['label' => 'Pending Approval', 'value' => number_format($stats['pending']), 'icon' => '⏳', 'bg' => 'bg-amber-50 text-amber-600'],
            ['label' => 'Units Sold', 'value' => number_format($stats['units_sold']), 'icon' => '🛒', 'bg' => 'bg-purple-50 text-purple-600'],
            ['label' => 'Revenue', 'value' => '₹' . number_format($stats['revenue']), 'icon' => '💰', 'bg' => 'bg-rose-50 text-rose-600'],
        ] as $card)
            <div class="bg-white rounded-2xl border border-ink-100 p-5 shadow-sm">
                <div class="w-10 h-10 rounded-xl {{ $card['bg'] }} flex items-center justify-center text-lg mb-3">{{ $card['icon'] }}</div>
                <div class="text-xl font-extrabold text-ink-900">{{ $card['value'] }}</div>
                <div class="text-xs text-ink-500 font-medium mt-0.5">{{ $card['label'] }}</div>
            </div>
        @endforeach
    </div>

    <!-- Earnings strip -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['label' => 'Available Balance', 'value' => '₹' . number_format($earnings['available'], 2), 'icon' => '💳', 'bg' => 'bg-emerald-50 text-emerald-600'],
            ['label' => 'Pending Payouts', 'value' => '₹' . number_format($earnings['pending_payouts'], 2), 'icon' => '⏳', 'bg' => 'bg-amber-50 text-amber-600'],
            ['label' => 'Withdrawn', 'value' => '₹' . number_format($earnings['withdrawn'], 2), 'icon' => '🏧', 'bg' => 'bg-blue-50 text-blue-600'],
            ['label' => 'Platform Commission', 'value' => '₹' . number_format($earnings['total_commission'], 2), 'icon' => '🏷️', 'bg' => 'bg-rose-50 text-rose-600'],
        ] as $card)
            <a href="{{ route('vendor.earnings') }}" class="bg-white rounded-2xl border border-ink-100 p-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl {{ $card['bg'] }} flex items-center justify-center">{{ $card['icon'] }}</div>
                    <div>
                        <div class="text-base font-extrabold text-ink-900">{{ $card['value'] }}</div>
                        <div class="text-[11px] text-ink-500 font-medium">{{ $card['label'] }}</div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <!-- Sales chart -->
    <div class="bg-white rounded-2xl border border-ink-100 p-5 mb-6">
        <h2 class="font-bold text-ink-900 mb-4">Sales — Last 14 Days</h2>
        <div class="h-56">
            <canvas id="vendorSalesChart" data-chart="line"></canvas>
            <script id="vendorSalesChart-data" type="application/json">
                {
                    "type": "line",
                    "legend": false,
                    "data": {
                        "labels": {!! json_encode($salesSeries->map(fn ($d) => \Carbon\Carbon::parse($d['date'])->format('d M'))) !!},
                        "datasets": [{
                            "label": "Sales ₹",
                            "data": {{ json_encode($salesSeries->pluck('total')) }},
                            "borderColor": "#059669",
                            "backgroundColor": "rgba(5,150,105,0.08)",
                            "fill": true,
                            "tension": 0.35,
                            "pointRadius": 2,
                            "borderWidth": 2
                        }]
                    }
                }
            </script>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent orders -->
        <div class="bg-white rounded-2xl border border-ink-100 p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-ink-900">Recent Orders</h2>
                <a href="{{ route('vendor.orders') }}" class="text-xs font-bold text-brand-600 hover:underline">View all →</a>
            </div>
            @forelse($recentOrders as $item)
                <div class="flex items-center justify-between py-2.5 border-b border-ink-50 last:border-0">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-ink-800 font-mono">{{ $item->order?->order_number }}</p>
                        <p class="text-xs text-ink-400 truncate">{{ $item->product_name }} × {{ $item->quantity }} · {{ $item->order?->customer_name }}</p>
                    </div>
                    <div class="text-right shrink-0 ml-3">
                        <p class="text-sm font-bold text-ink-900">₹{{ number_format($item->price * $item->quantity) }}</p>
                        <span class="text-[10px] font-bold uppercase {{ in_array($item->order?->status, ['delivered']) ? 'text-green-600' : ($item->order?->status === 'cancelled' ? 'text-red-600' : 'text-blue-600') }}">{{ $item->order?->status }}</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-ink-400 py-6 text-center">No orders yet.</p>
            @endforelse
        </div>

        <!-- Right column: products + low stock -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-ink-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-ink-900">Recent Products</h2>
                    <a href="{{ route('vendor.products.create') }}" class="text-xs font-bold text-brand-600 hover:underline">+ Add</a>
                </div>
                @forelse($recentProducts as $p)
                    <div class="flex items-center gap-3 py-2.5 border-b border-ink-50 last:border-0">
                        @if($p->primary_image)
                            <img src="{{ $p->primary_image }}" class="w-10 h-10 rounded-lg object-cover border border-ink-100" alt="">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-ink-100 flex items-center justify-center">📦</div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-ink-800 truncate">{{ $p->name }}</p>
                            <p class="text-xs text-ink-400">₹{{ number_format($p->effective_price) }} · stock {{ $p->stock }}</p>
                        </div>
                        @if($p->is_active)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-50 text-green-700">LIVE</span>
                        @else
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">PENDING</span>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-ink-400 py-6 text-center">No products yet — add your first one!</p>
                @endforelse
            </div>

            <div class="bg-white rounded-2xl border border-ink-100 p-5">
                <h2 class="font-bold text-ink-900 mb-4">⚠️ Low Stock Alerts</h2>
                @forelse($lowStock as $p)
                    <div class="flex items-center justify-between py-2.5 border-b border-ink-50 last:border-0">
                        <span class="text-sm text-ink-700 truncate pr-3">{{ $p->name }}</span>
                        <span class="text-xs font-bold {{ $p->stock <= 2 ? 'text-red-600' : 'text-amber-600' }}">{{ $p->stock }} left</span>
                    </div>
                @empty
                    <p class="text-sm text-ink-400 py-6 text-center">All stocked up 👍</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.vendor>
