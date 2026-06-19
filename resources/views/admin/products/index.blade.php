<x-layouts.admin title="Products">
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-xl font-bold text-ink-900">Products</h2>
            <p class="text-sm text-ink-500">{{ $products->total() }} products total</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="bg-brand-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-brand-700 flex items-center gap-2">+ Add Product</a>
    </div>

    <form method="GET" class="mb-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products by name or SKU..."
            class="w-full max-w-md px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
    </form>

    <div class="bg-white rounded-xl border border-ink-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-ink-50 text-ink-500 text-xs uppercase">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium">Product</th>
                        <th class="text-left px-5 py-3 font-medium">Category</th>
                        <th class="text-left px-5 py-3 font-medium">Brand</th>
                        <th class="text-left px-5 py-3 font-medium">Price</th>
                        <th class="text-left px-5 py-3 font-medium">Stock</th>
                        <th class="text-left px-5 py-3 font-medium">Status</th>
                        <th class="text-right px-5 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-ink-50">
                            <td class="px-5 py-3">
                                <div class="font-medium text-ink-800">{{ $product->name }}</div>
                                <div class="text-xs text-ink-400">{{ $product->sku }}</div>
                            </td>
                            <td class="px-5 py-3 text-ink-600">{{ $product->category?->name }}</td>
                            <td class="px-5 py-3 text-ink-600">{{ $product->brand?->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <div class="font-semibold">₹{{ number_format($product->effective_price) }}</div>
                                @if($product->discount_percent > 0)
                                    <div class="text-xs text-ink-400 line-through">₹{{ number_format($product->price) }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <span class="@if($product->stock < 10) text-red-600 font-bold @endif">{{ $product->stock }}</span>
                            </td>
                            <td class="px-5 py-3">
                                @if($product->is_active)
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Active</span>
                                @else
                                    <span class="text-xs bg-ink-100 text-ink-500 px-2 py-0.5 rounded-full">Hidden</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-brand-600 hover:text-brand-700 text-xs font-medium">Edit</a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Delete {{ $product->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-600 text-xs font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-8 text-center text-ink-400">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $products->links() }}</div>
</x-layouts.admin>
