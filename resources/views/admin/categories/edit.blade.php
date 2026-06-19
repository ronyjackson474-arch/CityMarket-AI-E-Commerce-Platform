<x-layouts.admin title="Edit Category">
    <div class="mb-5">
        <a href="{{ route('admin.categories.index') }}" class="text-sm text-brand-600 hover:underline">← Back to Categories</a>
        <h2 class="text-xl font-bold text-ink-900 mt-1">Edit: {{ $category->name }}</h2>
    </div>
    @include('admin.categories.form')
</x-layouts.admin>
