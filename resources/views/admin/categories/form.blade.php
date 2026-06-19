@php $editing = isset($category); @endphp
<form action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="POST">
    @csrf @if($editing) @method('PUT') @endif
    <div class="max-w-2xl bg-white rounded-xl border border-ink-100 p-5 space-y-4">
        <div>
            <label class="text-sm font-medium text-ink-700 mb-1 block">Name *</label>
            <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
        </div>
        <div>
            <label class="text-sm font-medium text-ink-700 mb-1 block">Parent Category</label>
            <select name="parent_id" class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                <option value="">None (top-level)</option>
                @foreach($parents as $p)
                    <option value="{{ $p->id }}" {{ (string)old('parent_id', $category->parent_id ?? '') === (string)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-sm font-medium text-ink-700 mb-1 block">Icon (emoji)</label>
                <input type="text" name="icon" value="{{ old('icon', $category->icon ?? '') }}" class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
            </div>
            <div>
                <label class="text-sm font-medium text-ink-700 mb-1 block">Position</label>
                <input type="number" name="position" value="{{ old('position', $category->position ?? 0) }}" class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
            </div>
        </div>
        <div>
            <label class="text-sm font-medium text-ink-700 mb-1 block">Image URL</label>
            <input type="text" name="image" value="{{ old('image', $category->image ?? '') }}" class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
        </div>
        <div>
            <label class="text-sm font-medium text-ink-700 mb-1 block">Description</label>
            <textarea name="description" rows="2" class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">{{ old('description', $category->description ?? '') }}</textarea>
        </div>
        <label class="flex items-center gap-2 text-sm text-ink-700">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', isset($category) ? $category->is_active : true) ? 'checked' : '' }} class="rounded text-brand-600 focus:ring-brand-500"> Active
        </label>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-brand-600 text-white font-medium px-5 py-2 rounded-lg hover:bg-brand-700">Save</button>
            <a href="{{ route('admin.categories.index') }}" class="text-ink-600 text-sm py-2 hover:text-ink-900">Cancel</a>
        </div>
    </div>
</form>
