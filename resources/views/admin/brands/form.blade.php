@php $editing = isset($brand); @endphp
<form action="{{ $editing ? route('admin.brands.update', $brand) : route('admin.brands.store') }}" method="POST">
    @csrf @if($editing) @method('PUT') @endif
    <div class="max-w-xl bg-white rounded-xl border border-ink-100 p-5 space-y-4">
        <div>
            <label class="text-sm font-medium text-ink-700 mb-1 block">Name *</label>
            <input type="text" name="name" value="{{ old('name', $brand->name ?? '') }}" required class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
        </div>
        <div>
            <label class="text-sm font-medium text-ink-700 mb-1 block">Logo (emoji or text)</label>
            <input type="text" name="logo" value="{{ old('logo', $brand->logo ?? '') }}" class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none" placeholder="e.g. 👟">
        </div>
        <div>
            <label class="text-sm font-medium text-ink-700 mb-1 block">Description</label>
            <textarea name="description" rows="2" class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">{{ old('description', $brand->description ?? '') }}</textarea>
        </div>
        <label class="flex items-center gap-2 text-sm text-ink-700">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', isset($brand) ? $brand->is_active : true) ? 'checked' : '' }} class="rounded text-brand-600 focus:ring-brand-500"> Active
        </label>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-brand-600 text-white font-medium px-5 py-2 rounded-lg hover:bg-brand-700">Save</button>
            <a href="{{ route('admin.brands.index') }}" class="text-ink-600 text-sm py-2 hover:text-ink-900">Cancel</a>
        </div>
    </div>
</form>
