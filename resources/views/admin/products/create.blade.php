<x-layouts.admin title="Add Product">
    <div class="mb-5">
        <a href="{{ route('admin.products.index') }}" class="text-sm text-brand-600 hover:underline">← Back to Products</a>
        <h2 class="text-xl font-bold text-ink-900 mt-1">Add New Product</h2>
    </div>
    @include('admin.products.form')
</x-layouts.admin>
