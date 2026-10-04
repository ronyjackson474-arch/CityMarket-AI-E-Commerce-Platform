<x-layouts.admin title="Add Brand">
    <div class="mb-5">
        <a href="{{ route('admin.brands.index') }}" class="text-sm text-brand-600 hover:underline">← Back to Brands</a>
        <h2 class="text-xl font-bold text-ink-900 mt-1">Add Brand</h2>
    </div>
    @include('admin.brands.form')
</x-layouts.admin>
