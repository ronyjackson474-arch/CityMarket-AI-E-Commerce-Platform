<x-layouts.admin title="Categories">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl font-bold text-ink-900">Categories</h2>
        <a href="{{ route('admin.categories.create') }}" class="bg-brand-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-brand-700">+ Add Category</a>
    </div>

    <div class="bg-white rounded-xl border border-ink-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-ink-50 text-ink-500 text-xs uppercase">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium">Name</th>
                        <th class="text-left px-5 py-3 font-medium">Parent</th>
                        <th class="text-left px-5 py-3 font-medium">Products</th>
                        <th class="text-left px-5 py-3 font-medium">Position</th>
                        <th class="text-left px-5 py-3 font-medium">Status</th>
                        <th class="text-right px-5 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse($categories as $cat)
                        <tr class="hover:bg-ink-50">
                            <td class="px-5 py-3"><span class="mr-1">{{ $cat->icon }}</span> {{ $cat->name }}</td>
                            <td class="px-5 py-3 text-ink-500">{{ $cat->parent?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-ink-600">{{ $cat->products()->count() }}</td>
                            <td class="px-5 py-3 text-ink-600">{{ $cat->position }}</td>
                            <td class="px-5 py-3">
                                @if($cat->is_active)<span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Active</span>@else<span class="text-xs bg-ink-100 text-ink-500 px-2 py-0.5 rounded-full">Hidden</span>@endif
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.categories.edit', $cat) }}" class="text-brand-600 hover:text-brand-700 text-xs font-medium">Edit</a>
                                <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Delete {{ $cat->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-600 text-xs font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-ink-400">No categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $categories->links() }}</div>
</x-layouts.admin>
