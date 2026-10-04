<x-layouts.storefront title="Sell on CityMarket — Become a Vendor">
    <div class="max-w-3xl mx-auto">
        <!-- Hero -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-600 via-brand-500 to-purple-600 text-white p-8 md:p-10 mb-8">
            <div class="absolute -top-10 -right-10 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-14 -left-8 w-56 h-56 bg-amber-400/20 rounded-full blur-3xl"></div>
            <div class="relative">
                <span class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur px-3 py-1 rounded-full text-xs font-semibold mb-4">🏪 CityMarket Seller Hub</span>
                <h1 class="text-3xl md:text-4xl font-extrabold mb-2">Apna shop kholo, duniya tak becho</h1>
                <p class="text-white/85 text-sm md:text-base max-w-xl">Join {{ $vendorCount }}+ sellers on CityMarket. Zero listing fee, secure payments, aur millions of customers — all on one marketplace.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium flex items-start gap-2">
                <span>✅</span><span>{{ session('success') }}</span>
            </div>
        @endif

        @if($vendor)
            <!-- Existing vendor status card -->
            <div class="bg-white rounded-2xl border border-ink-100 p-6 mb-6">
                <div class="flex items-center gap-4">
                    @if($vendor->logo_url)
                        <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->shop_name }}" class="w-14 h-14 rounded-xl object-cover border border-ink-100">
                    @else
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center text-white text-xl font-bold">{{ mb_substr($vendor->shop_name, 0, 1) }}</div>
                    @endif
                    <div class="flex-1">
                        <h2 class="font-bold text-ink-900">{{ $vendor->shop_name }}</h2>
                        <p class="text-xs text-ink-500">vendor/citymarket.in/shop/{{ $vendor->slug }}</p>
                    </div>
                    @if($vendor->status === 'pending')
                        <span class="px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 text-xs font-bold border border-amber-200">⏳ Pending approval</span>
                    @elseif($vendor->status === 'approved')
                        <span class="px-3 py-1.5 rounded-full bg-green-50 text-green-700 text-xs font-bold border border-green-200">✓ Approved</span>
                    @else
                        <span class="px-3 py-1.5 rounded-full bg-red-50 text-red-700 text-xs font-bold border border-red-200">Suspended</span>
                    @endif
                </div>
                @if($vendor->status === 'pending')
                    <p class="mt-4 text-sm text-ink-500 bg-ink-50 rounded-lg p-3">Aapki application admin review mein hai. Approval milte hi seller dashboard unlock ho jayega.</p>
                @endif
            </div>
        @else
            @guest
                <div class="bg-white rounded-2xl border border-ink-100 p-6 mb-6 text-center">
                    <p class="text-sm text-ink-600 mb-4">Seller banne ke liye pehle account mein sign in karo.</p>
                    <div class="flex items-center justify-center gap-3">
                        <a href="{{ route('login') }}" class="px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition-colors">Sign In</a>
                        <a href="{{ route('register') }}" class="px-5 py-2.5 border border-ink-200 text-sm font-semibold rounded-lg hover:bg-ink-50 transition-colors">Create Account</a>
                    </div>
                </div>
            @else
                <!-- Registration form -->
                <div class="bg-white rounded-2xl border border-ink-100 p-6 md:p-8">
                    <h2 class="text-xl font-bold text-ink-900 mb-1">Register your shop</h2>
                    <p class="text-sm text-ink-500 mb-6">Details bharo — admin approval ke baad aapki shop live ho jayegi.</p>

                    <form action="{{ route('vendor.register') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="text-sm font-medium text-ink-700 mb-1 block">Shop name *</label>
                            <input type="text" name="shop_name" value="{{ old('shop_name') }}" required maxlength="120"
                                placeholder="e.g. TechTrend Store"
                                class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none @error('shop_name') border-red-400 @enderror">
                            @error('shop_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-ink-700 mb-1 block">Shop description</label>
                            <textarea name="description" rows="3" maxlength="2000" placeholder="Aap kya bechte ho?"
                                class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
                            @error('description')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-ink-700 mb-1 block">Logo URL (optional)</label>
                            <input type="url" name="logo_url" value="{{ old('logo_url') }}" placeholder="https://..."
                                class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none @error('logo_url') border-red-400 @enderror">
                            @error('logo_url')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="w-full btn-shine bg-brand-600 text-white font-semibold py-3 rounded-xl hover:bg-brand-700 transition-colors shadow-md">
                            🚀 Apply to Sell
                        </button>
                        <p class="text-[11px] text-ink-400 text-center">Apply karne par aapka account seller ban jayega. Approval usually 24–48 hours mein.</p>
                    </form>
                </div>
            @endguest
        @endif

        <!-- Benefits -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
            <div class="bg-white rounded-xl border border-ink-100 p-5 text-center">
                <div class="text-2xl mb-2">💸</div>
                <h3 class="text-sm font-bold text-ink-900 mb-1">0% Listing Fee</h3>
                <p class="text-xs text-ink-500">Unlimited products, no upfront cost</p>
            </div>
            <div class="bg-white rounded-xl border border-ink-100 p-5 text-center">
                <div class="text-2xl mb-2">🚚</div>
                <h3 class="text-sm font-bold text-ink-900 mb-1">Pan-India Delivery</h3>
                <p class="text-xs text-ink-500">Logistics handled by CityMarket</p>
            </div>
            <div class="bg-white rounded-xl border border-ink-100 p-5 text-center">
                <div class="text-2xl mb-2">📊</div>
                <h3 class="text-sm font-bold text-ink-900 mb-1">Seller Dashboard</h3>
                <p class="text-xs text-ink-500">Orders, revenue & stock — live tracking</p>
            </div>
        </div>
    </div>
</x-layouts.storefront>
