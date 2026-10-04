<x-layouts.vendor title="Dashboard">
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent products -->
        <div class="bg-white rounded-2xl border border-ink-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-ink-900">Recent Products</h2>
                <a href="{{ route('vendor.products.create') }}" class="text-xs font-bold text-brand-600 hover:underline">+ Add New</a>
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

        <!-- Low stock -->
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
</x-layouts.vendor>
