<x-layouts.storefront title="Checkout — CityMarket">
    <h1 class="text-2xl font-bold text-ink-900 mb-6">Checkout</h1>

    <form action="{{ route('checkout.store') }}" method="POST">
        @csrf
        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Shipping -->
                <div class="bg-white rounded-xl border border-ink-100 p-5">
                    <h3 class="font-bold text-ink-900 mb-4 flex items-center gap-2">📍 Shipping Details</h3>

                    @if($addresses->isNotEmpty())
                        <div class="mb-4">
                            <label class="text-sm font-medium text-ink-700 mb-2 block">Saved Address</label>
                            <select id="saved-address" class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none" onchange="fillAddress(this.value)">
                                <option value="">— Enter new address —</option>
                                @foreach($addresses as $address)
                                    <option value="{{ htmlspecialchars($address->full_address, ENT_QUOTES) }}" data-name="{{ $address->name }}" data-phone="{{ $address->phone }}">
                                        {{ $address->name }} — {{ $address->city }}, {{ $address->pincode }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="grid sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm font-medium text-ink-700 mb-1 block">Full Name *</label>
                            <input type="text" name="customer_name" id="customer_name" value="{{ auth()->user()?->name }}" required
                                class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-ink-700 mb-1 block">Phone *</label>
                            <input type="tel" name="customer_phone" id="customer_phone" value="{{ auth()->user()?->phone }}" required
                                class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Shipping Address *</label>
                        <textarea name="shipping_address" id="shipping_address" rows="3" required
                            class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none"
                            placeholder="House no, Street, City, State, Pincode"></textarea>
                    </div>
                    <div class="mt-3">
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Order Notes (optional)</label>
                        <textarea name="notes" rows="2"
                            class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none"
                            placeholder="Delivery instructions..."></textarea>
                    </div>
                </div>

                <!-- Payment -->
                <div class="bg-white rounded-xl border border-ink-100 p-5">
                    <h3 class="font-bold text-ink-900 mb-4 flex items-center gap-2">💳 Payment Method</h3>
                    <div class="space-y-2">
                        @foreach([
                            'cod' => ['💵 Cash on Delivery', 'Pay when your order is delivered'],
                            'upi' => ['📱 UPI', 'GPay, PhonePe, Paytm'],
                            'card' => ['💳 Credit / Debit Card', 'Visa, Mastercard, RuPay'],
                            'wallet' => ['👛 Wallet', 'CityMarket Wallet'],
                        ] as $method => [$title, $desc])
                            <label class="flex items-center gap-3 p-3 border border-ink-200 rounded-lg cursor-pointer hover:border-brand-400 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 transition-colors">
                                <input type="radio" name="payment_method" value="{{ $method }}" {{ $method === 'cod' ? 'checked' : '' }} required
                                    class="text-brand-600 focus:ring-brand-500">
                                <div>
                                    <div class="text-sm font-medium text-ink-800">{!! $title !!}</div>
                                    <div class="text-xs text-ink-500">{{ $desc }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-ink-100 p-5 lg:sticky lg:top-32">
                    <h3 class="font-bold text-ink-900 mb-4">Your Order</h3>
                    <div class="space-y-2 mb-4 max-h-60 overflow-y-auto">
                        @foreach($cart->items as $item)
                            <div class="flex items-center gap-2 text-sm">
                                <div class="w-10 h-10 rounded bg-ink-50 overflow-hidden shrink-0">
                                    @if($item->product->primary_image)
                                        <img src="{{ $item->product->primary_image }}" alt="" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-ink-700 truncate">{{ $item->product->name }}</div>
                                    <div class="text-xs text-ink-400">Qty: {{ $item->quantity }}</div>
                                </div>
                                <div class="text-ink-800 font-medium">₹{{ number_format($item->product->effective_price * $item->quantity) }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-ink-100 pt-3 space-y-1.5 text-sm">
                        <div class="flex justify-between"><span class="text-ink-600">Subtotal</span><span class="font-medium">₹{{ number_format($cart->subtotal) }}</span></div>
                        <div class="flex justify-between"><span class="text-ink-600">Shipping</span><span class="font-medium @if($cart->shipping == 0) text-green-600 @endif">@if($cart->shipping == 0) FREE @else ₹{{ number_format($cart->shipping) }} @endif</span></div>
                        <div class="flex justify-between border-t border-ink-100 pt-2 mt-1"><span class="font-bold text-ink-900">Total</span><span class="font-bold text-brand-600 text-lg">₹{{ number_format($cart->total) }}</span></div>
                    </div>

                    <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-3 rounded-lg hover:bg-brand-700 transition-colors mt-4">
                        Place Order →
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
        function fillAddress(value) {
            const sel = document.getElementById('saved-address');
            const opt = sel.options[sel.selectedIndex];
            document.getElementById('shipping_address').value = value;
            if (opt.dataset.name) document.getElementById('customer_name').value = opt.dataset.name;
            if (opt.dataset.phone) document.getElementById('customer_phone').value = opt.dataset.phone;
        }
    </script>
</x-layouts.storefront>
