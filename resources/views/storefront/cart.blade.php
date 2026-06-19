<x-layouts.storefront title="Shopping Cart — CityMarket">
    <h1 class="text-2xl font-bold text-ink-900 mb-6">🛒 Your Cart</h1>

    @if($cart->items->isEmpty())
        <div class="bg-white rounded-xl border border-ink-100 p-12 text-center">
            <div class="text-6xl mb-4">🛒</div>
            <h3 class="text-lg font-semibold text-ink-800 mb-1">Your cart is empty</h3>
            <p class="text-sm text-ink-500 mb-4">Browse our catalog and find something you love.</p>
            <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 bg-brand-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-brand-700 transition-colors">
                Continue Shopping
            </a>
        </div>
    @else
        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Items -->
            <div class="lg:col-span-2 space-y-3">
                @foreach($cart->items as $item)
                    <div class="bg-white rounded-xl border border-ink-100 p-4 flex gap-4">
                        <div class="w-20 h-20 rounded-lg bg-ink-50 overflow-hidden shrink-0">
                            @if($item->product->primary_image)
                                <img src="{{ $item->product->primary_image }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ route('product', $item->product->slug) }}" class="text-sm font-medium text-ink-800 hover:text-brand-600 line-clamp-2">{{ $item->product->name }}</a>
                                <form action="{{ route('cart.remove') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                    <button type="submit" class="text-ink-400 hover:text-red-500 p-1" title="Remove">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                            <div class="text-xs text-ink-500 mt-0.5">{{ $item->product->brand?->name }}</div>
                            <div class="flex items-center justify-between mt-2">
                                <form action="{{ route('cart.update') }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                    <div class="flex items-center border border-ink-200 rounded-lg overflow-hidden">
                                        <button type="button" onclick="var i=this.parentNode.querySelector('input'); i.value=Math.max(1,parseInt(i.value)-1); this.form.submit()" class="px-2 py-1 text-ink-600 hover:bg-ink-50">−</button>
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock }}" class="w-10 text-center border-0 focus:ring-0 outline-none text-sm" onchange="this.form.submit()">
                                        <button type="button" onclick="var i=this.parentNode.querySelector('input'); i.value=Math.min({{ $item->product->stock }},parseInt(i.value)+1); this.form.submit()" class="px-2 py-1 text-ink-600 hover:bg-ink-50">+</button>
                                    </div>
                                </form>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-ink-900">₹{{ number_format($item->product->effective_price * $item->quantity) }}</div>
                                    @if($item->product->discount_percent > 0)
                                        <div class="text-xs text-ink-400 line-through">₹{{ number_format($item->product->price * $item->quantity) }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <a href="{{ route('shop') }}" class="inline-block text-sm text-brand-600 hover:text-brand-700 font-medium mt-2">← Continue Shopping</a>
            </div>

            <!-- Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-ink-100 p-5 lg:sticky lg:top-32">
                    <h3 class="font-bold text-ink-900 mb-4">Order Summary</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-ink-600">Subtotal ({{ $cart->item_count }} items)</span>
                            <span class="font-medium text-ink-900">₹{{ number_format($cart->subtotal) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink-600">Shipping</span>
                            <span class="font-medium @if($cart->shipping == 0) text-green-600 @else text-ink-900 @endif">
                                @if($cart->shipping == 0) FREE @else ₹{{ number_format($cart->shipping) }} @endif
                            </span>
                        </div>
                        @if($cart->shipping > 0)
                            <p class="text-xs text-ink-400">Add ₹{{ number_format(500 - $cart->subtotal) }} more for FREE delivery</p>
                        @endif
                        <div class="border-t border-ink-100 pt-2 mt-2 flex justify-between">
                            <span class="font-bold text-ink-900">Total</span>
                            <span class="font-bold text-brand-600 text-lg">₹{{ number_format($cart->total) }}</span>
                        </div>
                    </div>

                    <a href="{{ route('checkout') }}" class="block bg-brand-600 text-white text-center font-semibold py-3 rounded-lg hover:bg-brand-700 transition-colors mt-4">
                        Proceed to Checkout →
                    </a>
                </div>
            </div>
        </div>
    @endif
</x-layouts.storefront>
