<x-layouts.vendor title="My Products">
    <div class="bg-white rounded-2xl border border-ink-100 overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-ink-100">
            <h2 class="font-bold text-ink-900">My Products ({{ $products->total() }})</h2>
            <a href="{{ route('vendor.products.create') }}" class="px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-colors">+ Add Product</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-ink-400 border-b border-ink-100">
                        <th class="px-5 py-3 font-semibold">Product</th>
                        <th class="px-5 py-3 font-semibold">Price</th>
                        <th class="px-5 py-3 font-semibold">Stock</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                        <tr class="border-b border-ink-50 hover:bg-ink-50/50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    @if($p->primary_image)
                                        <img src="{{ $p->primary_image }}" class="w-10 h-10 rounded-lg object-cover border border-ink-100" alt="">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-ink-100 flex items-center justify-center">📦</div>
                                    @endif
                                    <span class="font-semibold text-ink-800">{{ $p->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 font-semibold">₹{{ number_format($p->effective_price) }}</td>
                            <td class="px-5 py-3 {{ $p->stock <= 5 ? 'text-amber-600 font-bold' : 'text-ink-600' }}">{{ $p->stock }}</td>
                            <td class="px-5 py-3">
                                @if($p->is_active)
                                    <span class="text-[10px] font-bold px-2 py-1 rounded-full bg-green-50 text-green-700">LIVE</span>
                                @else
                                    <span class="text-[10px] font-bold px-2 py-1 rounded-full bg-amber-50 text-amber-700">PENDING APPROVAL</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('vendor.products.edit', $p) }}" class="text-xs font-bold text-blue-600 hover:underline mr-3">Edit</a>
                                <form action="{{ route('vendor.products.destroy', $p) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Delete this product?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-bold text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-ink-400">No products yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $products->links() }}</div>
    </div>
</x-layouts.vendor>
