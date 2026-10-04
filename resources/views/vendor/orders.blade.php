<x-layouts.vendor title="Orders">
    <div class="bg-white rounded-2xl border border-ink-100 overflow-hidden">
        <div class="p-5 border-b border-ink-100">
            <h2 class="font-bold text-ink-900">My Orders ({{ $items->total() }})</h2>
            <p class="text-xs text-ink-400 mt-0.5">Only line items of your products are shown here.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-ink-400 border-b border-ink-100">
                        <th class="px-5 py-3 font-semibold">Order</th>
                        <th class="px-5 py-3 font-semibold">Product</th>
                        <th class="px-5 py-3 font-semibold">Qty</th>
                        <th class="px-5 py-3 font-semibold">Item Total</th>
                        <th class="px-5 py-3 font-semibold">Order Status</th>
                        <th class="px-5 py-3 font-semibold">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr class="border-b border-ink-50 hover:bg-ink-50/50">
                            <td class="px-5 py-3 font-mono text-xs font-bold text-ink-700">{{ $item->order?->order_number }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ $item->product ? route('product', $item->product->slug) : '#' }}" class="font-semibold text-ink-800 hover:text-brand-600">{{ $item->product_name }}</a>
                            </td>
                            <td class="px-5 py-3">{{ $item->quantity }}</td>
                            <td class="px-5 py-3 font-semibold">₹{{ number_format($item->price * $item->quantity) }}</td>
                            <td class="px-5 py-3">
                                <span class="text-[10px] font-bold px-2 py-1 rounded-full uppercase {{ in_array($item->order?->status, ['delivered','completed']) ? 'bg-green-50 text-green-700' : ($item->order?->status === 'cancelled' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-700') }}">{{ $item->order?->status }}</span>
                            </td>
                            <td class="px-5 py-3 text-ink-500 text-xs">{{ optional($item->order)->created_at?->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-ink-400">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $items->links() }}</div>
    </div>
</x-layouts.vendor>
