@props(['product'])

<div class="group bg-white rounded-xl border border-ink-100 overflow-hidden card-hover relative glow-border card-3d-tilt preserve-3d">
    <!-- Wishlist button -->
    @auth
        <button onclick="toggleWishlist({{ $product->id }}, this)" class="absolute top-2.5 right-2.5 z-10 w-9 h-9 rounded-full bg-white shadow-md flex items-center justify-center hover:bg-white transition-all hover:scale-125 hover:rotate-12 wishlist-btn-{{ $product->id }}" title="Add to wishlist">
            <svg class="w-4 h-4 text-ink-600 transition-colors wishlist-icon-{{ $product->id }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </button>
    @else
        <a href="{{ route('login') }}" class="absolute top-2.5 right-2.5 z-10 w-9 h-9 rounded-full bg-white shadow-md flex items-center justify-center hover:bg-white transition-all hover:scale-125 hover:rotate-12" title="Login to add to wishlist">
            <svg class="w-4 h-4 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </a>
    @endauth

    <a href="{{ route('product', $product->slug) }}" class="block tilt-inner">
        <div class="aspect-square bg-ink-50 relative overflow-hidden">
            @if($product->primary_image)
                <img src="{{ $product->primary_image }}" alt="{{ $product->name }}"
                    class="w-full h-full object-cover group-hover:scale-125 transition-transform duration-700" loading="lazy">
            @else
                <div class="w-full h-full flex items-center justify-center text-ink-300">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            @endif
            <!-- Shine overlay on hover -->
            <div class="absolute inset-0 sweep-shine pointer-events-none"></div>
            <!-- Discount badge -->
            @if($product->discount_percent > 0)
                <span class="absolute top-2.5 left-2.5 bg-brand-600 text-white text-[11px] font-bold px-2 py-1 rounded-md shadow-md animate-bounce-in">-{{ $product->discount_percent }}%</span>
            @endif
            <!-- Flash badge -->
            @if($product->is_flash_deal)
                <span class="absolute bottom-2.5 left-2.5 bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow flex items-center gap-0.5 animate-pulse">⚡ Flash</span>
            @endif
        </div>
    </a>
    <div class="p-3">
        @if($product->brand)
            <p class="text-[11px] uppercase tracking-wider text-ink-500 font-medium mb-0.5">{{ $product->brand->name }}</p>
        @endif
        <a href="{{ route('product', $product->slug) }}">
            <h3 class="text-sm font-medium text-ink-800 line-clamp-2 mb-1.5 min-h-[2.5rem] group-hover:text-brand-600 transition-colors leading-snug">{{ $product->name }}</h3>
        </a>
        <div class="flex items-center gap-1 mb-1.5">
            <div class="flex items-center gap-0.5">
                <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                <span class="text-xs font-semibold text-ink-700">{{ number_format($product->rating, 1) }}</span>
            </div>
            <span class="text-xs text-ink-400">({{ $product->review_count }})</span>
        </div>
        <div class="flex items-end justify-between">
            <div class="flex items-baseline gap-1.5">
                <span class="text-base font-bold text-ink-900">₹{{ number_format($product->effective_price) }}</span>
                @if($product->discount_percent > 0)
                    <span class="text-xs text-ink-400 line-through">₹{{ number_format($product->price) }}</span>
                @endif
            </div>
            @if($product->stock > 0)
                <button onclick="quickAddToCart({{ $product->id }}, event)" class="opacity-0 group-hover:opacity-100 transition-all w-8 h-8 bg-ink-900 hover:bg-brand-600 text-white rounded-lg flex items-center justify-center shadow-sm magnetic-btn hover:scale-110" title="Add to cart">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </button>
            @else
                <span class="text-[11px] text-brand-600 font-semibold uppercase tracking-wide">Sold Out</span>
            @endif
        </div>
    </div>
</div>
