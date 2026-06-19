<x-layouts.admin title="Brands">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl font-bold text-ink-900">Brands</h2>
        <a href="{{ route('admin.brands.create') }}" class="bg-brand-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-brand-700">+ Add Brand</a>
    </div>
    <div class="bg-white rounded-xl border border-ink-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-ink-50 text-ink-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-5 py-3 font-medium">Logo</th>
                    <th class="text-left px-5 py-3 font-medium">Name</th>
                    <th class="text-left px-5 py-3 font-medium">Products</th>
                    <th class="text-left px-5 py-3 font-medium">Status</th>
                    <th class="text-right px-5 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse($brands as $brand)
                    <tr class="hover:bg-ink-50">
                        <td class="px-5 py-3 text-2xl">{{ $brand->logo ?? '🏷️' }}</td>
                        <td class="px-5 py-3 font-medium text-ink-800">{{ $brand->name }}</td>
                        <td class="px-5 py-3 text-ink-600">{{ $brand->products()->count() }}</td>
                        <td class="px-5 py-3">@if($brand->is_active)<span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Active</span>@else<span class="text-xs bg-ink-100 text-ink-500 px-2 py-0.5 rounded-full">Hidden</span>@endif</td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.brands.edit', $brand) }}" class="text-brand-600 text-xs font-medium">Edit</a>
                            <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Delete {{ $brand->name }}?')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 text-xs font-medium">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-ink-400">No brands yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $brands->links() }}</div>
</x-layouts.admin>
