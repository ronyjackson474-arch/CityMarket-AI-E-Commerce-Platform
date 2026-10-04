<x-layouts.storefront title="My Orders — CityMarket">

<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-ink-900 mb-6">My Orders</h1>

    @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    @if($orders->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-ink-100 p-12 text-center">
            <div class="text-6xl mb-4">📦</div>
            <p class="text-ink-400 mb-4">You haven't placed any orders yet.</p>
            <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 text-white rounded-xl font-medium hover:bg-brand-700 transition-colors">Start Shopping</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="bg-white rounded-2xl shadow-sm border border-ink-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-brand-50 flex items-center justify-center">
                                <span class="text-lg">📦</span>
                            </div>
                            <div>
                                <div class="font-semibold text-ink-900">{{ $order->order_number }}</div>
                                <div class="text-sm text-ink-500">Placed on {{ $order->created_at->format('M d, Y \a\t h:i A') }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                @switch($order->status)
                                    @case('delivered') bg-green-100 text-green-700 @break
                                    @case('cancelled') bg-red-100 text-red-700 @break
                                    @case('shipped') bg-blue-100 text-blue-700 @break
                                    @default bg-amber-100 text-amber-700
                                @endswitch">
                                {{ \Illuminate\Support\Str::title($order->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-ink-100 flex items-center justify-between">
                        <div class="text-sm text-ink-500">{{ $order->items->count() }} item(s) · Total</div>
                        <div class="flex items-center gap-3">
                            <span class="font-bold text-ink-900">₹{{ number_format($order->total, 0) }}</span>
                            <a href="{{ route('account.order-detail', $order->order_number) }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">View Details →</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
</div>

</x-layouts.storefront>
