<x-layouts.admin title="Edit Brand">
    <div class="mb-5">
        <a href="{{ route('admin.brands.index') }}" class="text-sm text-brand-600 hover:underline">← Back to Brands</a>
        <h2 class="text-xl font-bold text-ink-900 mt-1">Edit: {{ $brand->name }}</h2>
    </div>
    @include('admin.brands.form')
</x-layouts.admin>
