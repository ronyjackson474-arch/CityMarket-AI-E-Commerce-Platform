<x-layouts.storefront :title="$vendor->shop_name . ' — CityMarket Vendor Shop'">
    <div class="max-w-7xl mx-auto">
        <!-- Vendor banner -->
        <div class="relative overflow-hidden rounded-2xl mb-6">
            @if($vendor->banner_url)
                <img src="{{ $vendor->banner_url }}" alt="{{ $vendor->shop_name }} banner" class="w-full h-48 md:h-64 object-cover">
            @else
                <div class="w-full h-48 md:h-64 bg-gradient-to-r from-brand-600 via-brand-500 to-purple-600"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-5 md:p-7 flex items-end gap-4">
                @if($vendor->logo_url)
                    <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->shop_name }}" class="w-20 h-20 rounded-2xl object-cover border-4 border-white shadow-xl">
                @else
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-500 to-purple-600 border-4 border-white shadow-xl flex items-center justify-center text-white text-3xl font-extrabold">{{ mb_substr($vendor->shop_name, 0, 1) }}</div>
                @endif
                <div class="text-white pb-1">
                    <h1 class="text-2xl md:text-3xl font-extrabold drop-shadow">{{ $vendor->shop_name }}</h1>
                    <div class="flex items-center gap-2 text-sm mt-1">
                        <span class="pc-rating">★ {{ number_format($vendor->rating, 1) }}</span>
                        <span class="text-white/80">· {{ $vendor->products_count }} products</span>
                        <span class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-500/90 text-[10px] font-bold uppercase tracking-wide">✓ Verified Seller</span>
                    </div>
                </div>
            </div>
        </div>

        @if($vendor->description)
            <p class="text-sm text-ink-600 bg-white rounded-xl border border-ink-100 p-4 mb-6 max-w-3xl">{{ $vendor->description }}</p>
        @endif

        <!-- Search -->
        <form class="mb-6 flex gap-2 max-w-md" action="{{ route('vendor.shop', $vendor->slug) }}" method="GET">
            <input type="text" name="q" value="{{ $q }}" placeholder="Search in {{ $vendor->shop_name }}..."
                class="flex-1 px-4 py-2.5 border border-ink-200 rounded-xl text-sm focus:border-brand-500 outline-none bg-white">
            <button class="px-5 py-2.5 bg-ink-900 text-white text-sm font-semibold rounded-xl hover:bg-brand-600 transition-colors">Search</button>
        </form>

        @if($products->isEmpty())
            <div class="bg-white rounded-2xl border border-ink-100 p-12 text-center">
                <div class="text-4xl mb-3">📦</div>
                <h2 class="font-bold text-ink-800 mb-1">No products yet</h2>
                <p class="text-sm text-ink-500">Is shop mein abhi koi product available nahi hai.</p>
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4">
                @foreach($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            <div class="mt-8">{{ $products->links() }}</div>
        @endif
    </div>
</x-layouts.storefront>
