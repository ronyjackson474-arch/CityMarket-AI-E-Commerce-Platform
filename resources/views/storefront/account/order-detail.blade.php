<x-layouts.storefront title="Order Details — CityMarket">

<div class="max-w-4xl mx-auto px-4 py-8">
    <a href="{{ route('account.orders') }}" class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-block">← Back to Orders</a>

    <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $order->order_number }}</h1>
                <p class="text-sm text-gray-500">Placed on {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                @switch($order->status)
                    @case('delivered') bg-green-100 text-green-700 @break
                    @case('cancelled') bg-red-100 text-red-700 @break
                    @case('shipped') bg-blue-100 text-blue-700 @break
                    @case('returned') bg-purple-100 text-purple-700 @break
                    @default bg-amber-100 text-amber-700
                @endswitch">
                {{ \Illuminate\Support\Str::title($order->status) }}
            </span>
        </div>

        @if(in_array($order->status, ['pending','confirmed','packed','shipped','delivered']))
            <div class="flex items-center justify-between mb-6 mt-6 overflow-x-auto">
                @php
                    $steps = ['pending' => 'Order Placed', 'confirmed' => 'Confirmed', 'packed' => 'Packed', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
                    $currentStep = array_search($order->status, array_keys($steps));
                @endphp
                @foreach($steps as $key => $label)
                    @php $completed = (array_search($key, array_keys($steps)) <= $currentStep); @endphp
                    <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                                {{ $completed ? 'bg-rose-600 text-white' : 'bg-gray-200 text-gray-400' }}">✓</div>
                            <span class="text-xs mt-1 {{ $completed ? 'text-gray-900 font-medium' : 'text-gray-400' }}">{{ $label }}</span>
                        </div>
                        @if(!$loop->last)
                            <div class="flex-1 h-0.5 mx-2 {{ $completed && array_search($key, array_keys($steps)) < $currentStep ? 'bg-rose-600' : 'bg-gray-200' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 rounded-xl bg-green-50 text-green-700 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-xl bg-red-50 text-red-700 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        <div class="space-y-3 mb-6">
            @foreach($order->items as $item)
                <div class="flex items-center gap-4 p-3 rounded-xl bg-gray-50">
                    @if($item->product && $item->product->images->isNotEmpty())
                        <img src="{{ asset('storage/' . ($item->product->images->firstWhere('is_primary', true)?->image_path ?? $item->product->images->first()->image_path)) }}" alt="{{ $item->product_name }}" class="w-16 h-16 rounded-lg object-cover">
                    @else
                        <div class="w-16 h-16 rounded-lg bg-gray-200 flex items-center justify-center text-gray-400 text-xs">No image</div>
                    @endif
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 text-sm">{{ $item->product_name }}</div>
                        <div class="text-xs text-gray-500">Qty: {{ $item->quantity }}</div>
                    </div>
                    <div class="font-semibold text-gray-900">₹{{ number_format($item->price * $item->quantity, 0) }}</div>
                </div>
            @endforeach
        </div>

        <div class="border-t border-gray-100 pt-4 space-y-1 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>₹{{ number_format($order->subtotal, 0) }}</span></div>
            @if($order->discount > 0)<div class="flex justify-between text-green-600"><span>Discount</span><span>-₹{{ number_format($order->discount, 0) }}</span></div>@endif
            <div class="flex justify-between"><span class="text-gray-500">Shipping</span><span>₹{{ number_format($order->shipping, 0) }}</span></div>
            <div class="flex justify-between font-bold text-lg pt-2 border-t border-gray-100"><span>Total</span><span class="text-rose-600">₹{{ number_format($order->total, 0) }}</span></div>
        </div>

        <div class="mt-6 flex gap-3 flex-wrap">
            @if(in_array($order->status, [\App\Models\Order::STATUS_PENDING, \App\Models\Order::STATUS_CONFIRMED]))
                <form action="{{ route('account.cancel-order', $order->order_number) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 border border-red-300 text-red-600 rounded-xl text-sm font-medium hover:bg-red-50">Cancel Order</button>
                </form>
            @endif

            @if($order->status === \App\Models\Order::STATUS_DELIVERED && !$returnRequest)
                <button onclick="document.getElementById('return-form').classList.toggle('hidden')" class="px-4 py-2 border border-purple-300 text-purple-600 rounded-xl text-sm font-medium hover:bg-purple-50">Request Return</button>
            @endif

            @if($returnRequest)
                <span class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-medium bg-purple-100 text-purple-700">Return: {{ \Illuminate\Support\Str::title($returnRequest->status) }}</span>
            @endif
        </div>

        @if($order->status === \App\Models\Order::STATUS_DELIVERED && !$returnRequest)
            <form id="return-form" action="{{ route('account.request-return', $order->order_number) }}" method="POST" class="hidden mt-4 p-4 rounded-xl bg-gray-50 space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                    <select name="reason" class="w-full rounded-xl border-gray-300 text-sm" required>
                        <option value="damaged">Item arrived damaged</option>
                        <option value="wrong_item">Wrong item received</option>
                        <option value="not_as_described">Not as described</option>
                        <option value="changed_mind">Changed my mind</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                    <textarea name="description" rows="3" class="w-full rounded-xl border-gray-300 text-sm" placeholder="Tell us more..."></textarea>
                </div>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-medium hover:bg-purple-700">Submit Return Request</button>
            </form>
        @endif
    </div>
</div>

</x-layouts.storefront>
