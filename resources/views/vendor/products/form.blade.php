<x-layouts.vendor :title="isset($product) ? 'Edit Product' : 'Add Product'">
    <div class="max-w-3xl bg-white rounded-2xl border border-ink-100 p-6">
        <h2 class="font-bold text-ink-900 mb-1">{{ isset($product) ? 'Edit Product' : 'Add New Product' }}</h2>
        <p class="text-xs text-ink-400 mb-6">💡 Vendor products admin approval ke baad live hote hain.</p>

        <form action="{{ isset($product) ? route('vendor.products.update', $product) : route('vendor.products.store') }}"
            method="POST" class="space-y-4" enctype="multipart/form-data">
            @csrf
            @if(isset($product)) @method('PUT') @endif

            <div>
                <label class="text-sm font-medium text-ink-700 mb-1 block">Product name *</label>
                <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required maxlength="255"
                    class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-ink-700 mb-1 block">Category *</label>
                    <select name="category_id" required class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none bg-white">
                        <option value="">Select…</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-ink-700 mb-1 block">Brand</label>
                    <select name="brand_id" class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none bg-white">
                        <option value="">—</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id ?? '') == $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-ink-700 mb-1 block">Description</label>
                <textarea name="description" rows="4" class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">{{ old('description', $product->description ?? '') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-ink-700 mb-1 block">Price (₹) *</label>
                    <input type="number" step="0.01" min="0.01" name="price" value="{{ old('price', $product->price ?? '') }}" required
                        class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium text-ink-700 mb-1 block">Sale price (₹)</label>
                    <input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $product->sale_price ?? '') }}"
                        class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-ink-700 mb-1 block">Stock *</label>
                    <input type="number" min="0" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" required
                        class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium text-ink-700 mb-1 block">SKU (auto if empty)</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}"
                        class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-ink-700 mb-1 block">Product Images — upload files ya URL paste karo</label>

                <div class="border-2 border-dashed border-ink-200 rounded-xl p-4 mb-3 bg-ink-50/50">
                    <label class="block text-xs font-medium text-ink-600 mb-2">📁 Upload files (JPG/PNG/WebP, max 2MB each — multiple select kar sakte ho)</label>
                    <input type="file" name="image_files[]" multiple accept="image/jpeg,image/png,image/webp" id="imageFilesInput"
                        class="w-full text-sm text-ink-600 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-brand-600 file:text-white file:text-xs file:font-semibold file:cursor-pointer hover:file:bg-brand-700 cursor-pointer">
                    @error('image_files.*')
                        <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                    <div id="imagePreviewGrid" class="grid grid-cols-4 sm:grid-cols-6 gap-2 mt-3"></div>
                </div>

                <label class="block text-xs font-medium text-ink-600 mb-1">🔗 Ya image URLs (comma-separated, first = primary)</label>
                <input type="text" name="images" value="{{ old('images', isset($product) ? $product->images->pluck('image_path')->implode(', ') : '') }}"
                    placeholder="https://example.com/img1.jpg, https://example.com/img2.jpg"
                    class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                <p class="text-[11px] text-ink-400 mt-1">Upload aur URL dono ek saath bhi chalega — sab images product pe lagengi.</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-colors">
                    {{ isset($product) ? 'Update Product' : 'Submit for Approval' }}
                </button>
                <a href="{{ route('vendor.products.index') }}" class="px-6 py-2.5 border border-ink-200 text-sm font-semibold rounded-lg hover:bg-ink-50 transition-colors">Cancel</a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    (function () {
        var input = document.getElementById('imageFilesInput');
        var grid = document.getElementById('imagePreviewGrid');
        if (!input || !grid) return;
        input.addEventListener('change', function () {
            grid.innerHTML = '';
            Array.from(input.files || []).forEach(function (file) {
                if (!file.type.startsWith('image/')) return;
                var wrap = document.createElement('div');
                wrap.className = 'relative rounded-lg overflow-hidden border border-ink-200 bg-white';
                var img = document.createElement('img');
                img.alt = file.name;
                img.className = 'w-full h-16 object-cover';
                img.src = URL.createObjectURL(file);
                wrap.appendChild(img);
                grid.appendChild(wrap);
            });
        });
    })();
    </script>
    @endpush
</x-layouts.vendor>
