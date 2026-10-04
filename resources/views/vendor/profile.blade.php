<x-layouts.vendor title="Shop Profile">
    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl border border-ink-100 p-6">
            <h2 class="font-bold text-ink-900 mb-1">Shop Profile</h2>
            <p class="text-xs text-ink-400 mb-6">Ye details aapki public shop page (/vendor/{{ $vendor->slug }}) par dikhte hain.</p>

            <div class="flex items-center gap-4 mb-6 p-4 rounded-xl bg-ink-50">
                @if($vendor->logo_url)
                    <img src="{{ $vendor->logo_url }}" class="w-16 h-16 rounded-xl object-cover border border-ink-200" alt="">
                @else
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center text-white text-2xl font-extrabold">{{ mb_substr($vendor->shop_name, 0, 1) }}</div>
                @endif
                <div>
                    <p class="font-bold text-ink-900">{{ $vendor->shop_name }}</p>
                    <a href="{{ route('vendor.shop', $vendor->slug) }}" class="text-xs text-brand-600 hover:underline">Visit my shop →</a>
                </div>
            </div>

            <form action="{{ route('vendor.profile.update') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="text-sm font-medium text-ink-700 mb-1 block">Shop name *</label>
                    <input type="text" name="shop_name" value="{{ old('shop_name', $vendor->shop_name) }}" required maxlength="120"
                        class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium text-ink-700 mb-1 block">Description</label>
                    <textarea name="description" rows="3" maxlength="2000"
                        class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">{{ old('description', $vendor->description) }}</textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Logo URL</label>
                        <input type="url" name="logo_url" value="{{ old('logo_url', $vendor->logo_url) }}" placeholder="https://..."
                            class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Banner URL</label>
                        <input type="url" name="banner_url" value="{{ old('banner_url', $vendor->banner_url) }}" placeholder="https://..."
                            class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                    </div>
                </div>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-colors">Save Changes</button>
            </form>
        </div>
    </div>
</x-layouts.vendor>
