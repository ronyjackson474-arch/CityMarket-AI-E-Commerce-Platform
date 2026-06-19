<x-layouts.admin title="Edit Product">
    <div class="mb-5">
        <a href="{{ route('admin.products.index') }}" class="text-sm text-brand-600 hover:underline">← Back to Products</a>
        <h2 class="text-xl font-bold text-ink-900 mt-1">Edit: {{ $product->name }}</h2>
    </div>
    @include('admin.products.form')
</x-layouts.admin>
