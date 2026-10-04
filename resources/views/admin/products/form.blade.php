<form action="{{ isset($product) ? route('admin.products.update', $product) : route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($product)) @method('PUT') @endif

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl border border-ink-100 p-5">
                <h3 class="font-bold text-ink-900 mb-4">Product Information</h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Name *</label>
                        <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required
                            class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Description</label>
                        <textarea name="description" rows="4" class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">{{ old('description', $product->description ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Specifications (JSON or key: value per line)</label>
                        <textarea name="specifications" rows="3" class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none font-mono">{{ old('specifications', isset($product) ? (is_string($product->specifications) ? $product->specifications : json_encode($product->specifications)) : '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-ink-100 p-5">
                <h3 class="font-bold text-ink-900 mb-4">Images</h3>
                <div class="border-2 border-dashed border-ink-200 rounded-xl p-3 mb-3 bg-ink-50/50">
                    <label class="block text-xs font-medium text-ink-600 mb-1.5">📁 Upload files (JPG/PNG/WebP, max 2MB each, up to 6)</label>
                    <input type="file" name="image_files[]" multiple accept="image/jpeg,image/png,image/webp" id="adminImageFiles"
                        class="w-full text-sm text-ink-600 file:mr-3 file:px-4 file:py-1.5 file:rounded-lg file:border-0 file:bg-brand-600 file:text-white file:text-xs file:font-semibold file:cursor-pointer hover:file:bg-brand-700 cursor-pointer">
                    @error('image_files.*')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                    <div id="adminImagePreview" class="flex gap-2 mt-2 flex-wrap"></div>
                </div>
                <label class="block text-xs font-medium text-ink-600 mb-1">🔗 Ya image URLs (comma-separated, first is primary)</label>
                <textarea name="images" rows="3" class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none" placeholder="https://...">{{ old('images', isset($product) ? $product->images->pluck('image_path')->implode(', ') : '') }}</textarea>
                <p class="text-[11px] text-ink-400 mt-1">Upload aur URL dono ek saath bhi chalega.</p>
                @if(isset($product) && $product->images->isNotEmpty())
                    <div class="flex gap-2 mt-3">
                        @foreach($product->images as $img)
                            <img src="{{ $img->image_path }}" alt="" class="w-16 h-16 object-cover rounded-lg border border-ink-200">
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-ink-100 p-5">
                <h3 class="font-bold text-ink-900 mb-4">Pricing</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Price (₹) *</label>
                        <input type="number" step="0.01" name="price" value="{{ old('price', $product->price ?? '') }}" required
                            class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Sale Price (₹)</label>
                        <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price ?? '') }}"
                            class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Stock *</label>
                        <input type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" required
                            class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-ink-100 p-5">
                <h3 class="font-bold text-ink-900 mb-4">Organization</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Category *</label>
                        <select name="category_id" required class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                            <option value="">Select category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (string)old('category_id', $product->category_id ?? '') === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Brand</label>
                        <select name="brand_id" class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                            <option value="">No brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ (string)old('brand_id', $product->brand_id ?? '') === (string)$brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">SKU</label>
                        <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}" class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-ink-100 p-5">
                <h3 class="font-bold text-ink-900 mb-4">Visibility</h3>
                <div class="space-y-2">
                    @foreach([
                        'is_active' => ['Active (visible to customers)', true],
                        'is_featured' => ['Featured on home page', false],
                        'is_flash_deal' => ['Flash deal', false],
                    ] as $field => [$label, $default])
                        <label class="flex items-center gap-2 text-sm text-ink-700">
                            <input type="checkbox" name="{{ $field }}" value="1" {{ old($field, isset($product) ? $product->{$field} : $default) ? 'checked' : '' }}
                                class="rounded text-brand-600 focus:ring-brand-500"> {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 mt-5">
        <button type="submit" class="bg-brand-600 text-white font-medium px-5 py-2.5 rounded-lg hover:bg-brand-700">Save Product</button>
        <a href="{{ route('admin.products.index') }}" class="text-ink-600 text-sm hover:text-ink-900">Cancel</a>
    </div>
</form>

<script>
(function () {
    var input = document.getElementById('adminImageFiles');
    var grid = document.getElementById('adminImagePreview');
    if (!input || !grid) return;
    input.addEventListener('change', function () {
        grid.innerHTML = '';
        Array.from(input.files || []).forEach(function (file) {
            if (!file.type.startsWith('image/')) return;
            var img = document.createElement('img');
            img.alt = file.name;
            img.className = 'w-16 h-16 object-cover rounded-lg border border-ink-200';
            img.src = URL.createObjectURL(file);
            grid.appendChild(img);
        });
    });
})();
</script>
