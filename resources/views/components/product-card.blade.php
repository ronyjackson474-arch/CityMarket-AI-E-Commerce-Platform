@props(['product'])

<div class="group pc-ring bg-white rounded-2xl border border-ink-100 overflow-hidden relative shadow-sm hover:shadow-xl transition-shadow duration-300">
    <!-- Wishlist button -->
    @auth
        <button onclick="toggleWishlist({{ $product->id }}, this)" class="absolute top-2.5 right-2.5 z-20 w-9 h-9 rounded-full glass shadow-md flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-90 wishlist-btn-{{ $product->id }}" title="Add to wishlist">
            <svg class="w-4 h-4 text-ink-600 transition-all duration-300 hover:text-brand-600 wishlist-icon-{{ $product->id }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </button>
    @else
        <a href="{{ route('login') }}" class="absolute top-2.5 right-2.5 z-20 w-9 h-9 rounded-full glass shadow-md flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-90" title="Login to add to wishlist">
            <svg class="w-4 h-4 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </a>
    @endauth

    <a href="{{ route('product', $product->slug) }}" class="block">
        <div class="aspect-square bg-gradient-to-b from-white to-slate-50 relative overflow-hidden">
            @php
                // Sharper card images: load the 600px square Unsplash variant (cards render ~400px max)
                $cardImg = $product->primary_image
                    ? preg_replace('/([?&])w=1200/', '$1w=600', preg_replace('/([?&])h=1200/', '$1h=600', $product->primary_image))
                    : null;
            @endphp
            @if($cardImg)
                <img src="{{ $cardImg }}" alt="{{ $product->name }}"
                    width="600" height="600" decoding="async"
                    class="w-full h-full object-cover card-img transition-transform duration-500 group-hover:scale-[1.05]" loading="lazy"
                    onload="this.parentElement.classList.add('pc-img-done')"
                    onerror="this.style.display='none'; this.parentElement.classList.add('pc-img-done'); this.parentElement.classList.add('img-failed')">
            @else
                <div class="w-full h-full pc-placeholder flex items-center justify-center text-ink-400">
                    <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            @endif
            <!-- Skeleton shimmer while image loads (auto-hides as safety) -->
            <div class="pc-img-skel" data-skel-timeout="1"></div>
            <!-- Fallback icon shown if image fails -->
            <div class="absolute inset-0 hidden items-center justify-center text-ink-300 img-fallback">
                <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <!-- Discount badge -->
            @if($product->discount_percent > 0)
                <span class="absolute top-2.5 left-2.5 bg-gradient-to-r from-brand-600 to-brand-500 text-white text-[11px] font-bold px-2 py-1 rounded-md shadow-md">-{{ $product->discount_percent }}%</span>
            @endif
            <!-- Flash badge -->
            @if($product->is_flash_deal)
                <span class="absolute bottom-2.5 left-2.5 bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow flex items-center gap-0.5">⚡ Flash</span>
            @endif
        </div>
    </a>
    <div class="p-3.5">
        @if($product->brand)
            <p class="text-[10px] uppercase tracking-[0.12em] text-brand-500/90 font-bold mb-1">{{ $product->brand->name }}</p>
        @endif
        <a href="{{ route('product', $product->slug) }}">
            <h3 class="text-sm font-semibold text-ink-800 line-clamp-2 mb-2 min-h-[2.5rem] group-hover:text-brand-600 transition-colors duration-300 leading-snug">{{ $product->name }}</h3>
        </a>
        <div class="flex items-center gap-2 mb-2.5">
            <span class="pc-rating">
                <svg class="w-3 h-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                {{ number_format($product->rating, 1) }}
            </span>
            <span class="text-[11px] text-ink-400">{{ $product->review_count }} reviews</span>
        </div>
        @if($product->stock > 0 && $product->stock <= 10)
            <div class="pc-stock-bar mb-2.5" title="Only {{ $product->stock }} left">
                <span style="width: {{ max(10, $product->stock * 10) }}%"></span>
            </div>
            <p class="text-[11px] font-semibold text-amber-600 mb-2">🔥 Only {{ $product->stock }} left</p>
        @endif
        <div class="flex items-end justify-between">
            <div class="price-pop origin-left">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-lg font-extrabold bg-gradient-to-r from-ink-900 to-brand-600 bg-clip-text text-transparent">₹{{ number_format($product->effective_price) }}</span>
                    @if($product->discount_percent > 0)
                        <span class="text-xs text-ink-400 line-through">₹{{ number_format($product->price) }}</span>
                    @endif
                </div>
            </div>
            @if($product->stock > 0)
                <button onclick="quickAddToCart({{ $product->id }}, event)" class="transition-all duration-300 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 w-9 h-9 bg-gradient-to-br from-brand-600 to-brand-500 hover:from-ink-900 hover:to-ink-700 active:scale-90 text-white rounded-xl flex items-center justify-center shadow-md btn-glow" title="Add to cart">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </button>
            @else
                <span class="text-[11px] text-ink-400 font-semibold uppercase tracking-wide bg-ink-50 px-2 py-1 rounded-md">Sold Out</span>
            @endif
        </div>
    </div>
</div>
