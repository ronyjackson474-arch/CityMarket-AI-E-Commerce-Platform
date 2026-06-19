@props(['product'])

<a href="{{ route('product', $product->slug) }}" class="group bg-white rounded-xl border border-ink-100 overflow-hidden card-hover block">
    <div class="aspect-square bg-ink-50 relative overflow-hidden">
        @if($product->primary_image)
            <img src="{{ $product->primary_image }}" alt="{{ $product->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center text-ink-300">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        @endif
        @if($product->discount_percent > 0)
            <span class="absolute top-2 left-2 bg-brand-600 text-white text-xs font-bold px-2 py-0.5 rounded-md shadow">-{{ $product->discount_percent }}%</span>
        @endif
        @if($product->is_flash_deal)
            <span class="absolute top-2 right-2 bg-amber-500 text-white text-xs font-bold px-2 py-0.5 rounded-md shadow">⚡ Flash</span>
        @endif
    </div>
    <div class="p-3">
        @if($product->brand)
            <p class="text-xs text-ink-500 mb-0.5">{{ $product->brand->name }}</p>
        @endif
        <h3 class="text-sm font-medium text-ink-800 line-clamp-2 mb-1.5 min-h-[2.5rem]">{{ $product->name }}</h3>
        <div class="flex items-center gap-1 mb-1.5">
            <div class="flex items-center text-amber-400 text-xs">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                <span class="text-ink-600 ml-0.5">{{ number_format($product->rating, 1) }}</span>
            </div>
            <span class="text-xs text-ink-400">({{ $product->review_count }})</span>
        </div>
        <div class="flex items-baseline gap-1.5">
            <span class="text-base font-bold text-ink-900">₹{{ number_format($product->effective_price) }}</span>
            @if($product->discount_percent > 0)
                <span class="text-xs text-ink-400 line-through">₹{{ number_format($product->price) }}</span>
            @endif
        </div>
    </div>
</a>
