<x-layouts.storefront title="Order Confirmed — CityMarket">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl border border-ink-100 p-8 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-9 h-9 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-ink-900 mb-2">Order Placed Successfully! 🎉</h1>
            <p class="text-ink-600 text-sm mb-4">Thank you for your purchase. Your order is being processed.</p>

            <div class="bg-ink-50 rounded-xl p-4 mb-6 inline-block">
                <div class="text-xs text-ink-500">Order Number</div>
                <div class="text-lg font-bold text-brand-600">{{ $order->order_number }}</div>
            </div>

            <div class="text-left border-t border-ink-100 pt-4 mb-6">
                <h3 class="font-semibold text-ink-800 mb-3">Order Summary</h3>
                <div class="space-y-2 text-sm">
                    @foreach($order->items as $item)
                        <div class="flex justify-between">
                            <span class="text-ink-600">{{ $item->product_name }} × {{ $item->quantity }}</span>
                            <span class="text-ink-800 font-medium">₹{{ number_format($item->price * $item->quantity) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="border-t border-ink-100 mt-3 pt-3 space-y-1.5 text-sm">
                    <div class="flex justify-between"><span class="text-ink-600">Subtotal</span><span>₹{{ number_format($order->subtotal) }}</span></div>
                    @if($order->discount > 0)
                        <div class="flex justify-between text-green-600"><span>Discount</span><span>-₹{{ number_format($order->discount) }}</span></div>
                    @endif
                    <div class="flex justify-between"><span class="text-ink-600">Shipping</span><span>@if($order->shipping == 0) FREE @else ₹{{ number_format($order->shipping) }} @endif</span></div>
                    <div class="flex justify-between font-bold text-base pt-1"><span class="text-ink-900">Total</span><span class="text-brand-600">₹{{ number_format($order->total) }}</span></div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('shop') }}" class="bg-brand-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-brand-700 transition-colors">Continue Shopping</a>
                @auth
                    <a href="{{ route('checkout') }}" class="bg-white border border-ink-200 text-ink-700 text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-ink-50 transition-colors">View My Orders</a>
                @endauth
            </div>
        </div>
    </div>
</x-layouts.storefront>
