<x-layouts.storefront title="CityMarket — Shop Smarter with AI">
    <!-- Hero Banner Slider -->
    <section class="mb-8">
        <div class="grid md:grid-cols-3 gap-4">
            @foreach($banners as $i => $banner)
                <div class="relative rounded-2xl overflow-hidden h-40 md:h-48 bg-gradient-to-br {{ $banner['bg'] }} {{ $i === 0 ? 'md:col-span-2 md:h-48' : '' }}">
                    <img src="{{ $banner['image'] }}" alt="{{ $banner['title'] }}" class="absolute inset-0 w-full h-full object-cover mix-blend-overlay opacity-60">
                    <div class="relative h-full flex flex-col justify-center p-6 text-white">
                        <h2 class="text-xl md:text-2xl font-bold mb-1">{{ $banner['title'] }}</h2>
                        <p class="text-sm opacity-90 mb-3">{{ $banner['subtitle'] }}</p>
                        <a href="{{ route('shop') }}" class="self-start bg-white text-ink-900 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-ink-100 transition-colors">Shop Now →</a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- AI Assistant Banner -->
    <section class="mb-8">
        <a href="{{ route('assistant') }}" class="block bg-gradient-to-r from-purple-600 via-brand-600 to-pink-600 rounded-2xl p-6 text-white relative overflow-hidden group">
            <div class="relative z-10 flex items-center gap-4">
                <div class="text-4xl">🤖</div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold">AI Shopping Assistant</h3>
                    <p class="text-sm opacity-90">Tell me what you're looking for — I'll find the best products for you. Try "best running shoes under 2000"</p>
                </div>
                <div class="hidden sm:block bg-white/20 backdrop-blur px-4 py-2 rounded-lg text-sm font-semibold group-hover:bg-white/30 transition-colors">Try Now →</div>
            </div>
            <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-white/10 skew-x-12 origin-top-right"></div>
        </a>
    </section>

    <!-- Categories -->
    <section class="mb-10">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-ink-900">Shop by Category</h2>
            <a href="{{ route('shop') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">View all →</a>
        </div>
        <div class="grid grid-cols-4 md:grid-cols-8 gap-3">
            @foreach($categories as $cat)
                <a href="{{ route('category', $cat->slug) }}" class="flex flex-col items-center gap-2 p-3 bg-white rounded-xl border border-ink-100 hover:border-brand-300 hover:shadow-md transition-all group">
                    <div class="w-14 h-14 rounded-full bg-brand-50 flex items-center justify-center text-2xl group-hover:bg-brand-100 transition-colors">
                        {{ $cat->icon }}
                    </div>
                    <span class="text-xs font-medium text-ink-700 text-center">{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <!-- Flash Deals -->
    @if($flashDeals->isNotEmpty())
    <section class="mb-10">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-bold text-ink-900">⚡ Flash Deals</h2>
                <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2 py-0.5 rounded-full">Limited Time</span>
            </div>
            <a href="{{ route('shop', ['on_sale' => 1]) }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">View all →</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            @foreach($flashDeals as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </section>
    @endif

    <!-- Featured / Trending -->
    <section class="mb-10">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-ink-900">✨ Featured Products</h2>
            <a href="{{ route('shop') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">View all →</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            @foreach($featured as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </section>

    <!-- Trending -->
    <section class="mb-10">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-ink-900">🔥 Trending Now</h2>
            <a href="{{ route('shop') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">View all →</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            @foreach($trending as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </section>

    <!-- AI Recommendations (logged-in only) -->
    @auth
        @if($recommendations->isNotEmpty())
        <section class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-ink-900">✨ Recommended For You</h2>
                <span class="text-xs bg-purple-100 text-purple-600 px-2 py-1 rounded-full font-medium">AI Powered</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                @foreach($recommendations as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
        @endif
    @endauth

    <!-- Brands -->
    @if($brands->isNotEmpty())
    <section class="mb-6">
        <h2 class="text-lg font-bold text-ink-900 mb-4">Top Brands</h2>
        <div class="grid grid-cols-3 sm:grid-cols-5 md:grid-cols-8 gap-3">
            @foreach($brands as $brand)
                <div class="flex items-center justify-center p-4 bg-white rounded-xl border border-ink-100 hover:shadow-md transition-shadow">
                    <div class="text-center">
                        <div class="text-2xl mb-1">{{ $brand->logo ?? '🏷️' }}</div>
                        <div class="text-xs font-semibold text-ink-700">{{ $brand->name }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif
</x-layouts.storefront>
