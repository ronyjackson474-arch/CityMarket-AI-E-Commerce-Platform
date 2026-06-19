<x-layouts.storefront title="{{ $product->name }} — CityMarket">
    <!-- Breadcrumb -->
    <nav class="text-sm text-ink-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-brand-600">Home</a> ›
        <a href="{{ route('category', $product->category->slug) }}" class="hover:text-brand-600">{{ $product->category->name }}</a> ›
        <span class="text-ink-700">{{ $product->name }}</span>
    </nav>

    <div class="grid md:grid-cols-2 gap-8 mb-12">
        <!-- Images -->
        <div>
            <div class="bg-white rounded-2xl border border-ink-100 overflow-hidden aspect-square mb-3">
                @if($product->images->isNotEmpty())
                    <img id="main-product-image" src="{{ $product->images->first()->image_path }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-ink-300">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                @endif
            </div>
            @if($product->images->count() > 1)
                <div class="grid grid-cols-5 gap-2">
                    @foreach($product->images as $img)
                        <button onclick="document.getElementById('main-product-image').src = '{{ $img->image_path }}'"
                            class="aspect-square rounded-lg overflow-hidden border-2 border-transparent hover:border-brand-400 transition-colors">
                            <img src="{{ $img->image_path }}" alt="" class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Details -->
        <div>
            @if($product->brand)
                <a href="{{ route('shop', ['brand' => $product->brand_id]) }}" class="text-sm text-brand-600 font-medium hover:underline">{{ $product->brand->name }}</a>
            @endif
            <h1 class="text-2xl font-bold text-ink-900 mt-1 mb-2">{{ $product->name }}</h1>

            <div class="flex items-center gap-3 mb-4">
                <div class="flex items-center text-amber-400">
                    @for($i = 1; $i <= 5; $i++)
                        @if($product->rating >= $i)★ @elseif($product->rating >= $i - 0.5)☆ @else☆@endif
                    @endfor
                    <span class="text-ink-700 text-sm ml-1 font-medium">{{ number_format($product->rating, 1) }}</span>
                </div>
                <span class="text-sm text-ink-400">|</span>
                <span class="text-sm text-ink-500">{{ $product->review_count }} reviews</span>
                <span class="text-sm text-ink-400">|</span>
                <span class="text-sm @if($product->stock > 0) text-green-600 @else text-red-600 @endif font-medium">
                    @if($product->stock > 0) ✓ In Stock ({{ $product->stock }}) @else ✗ Out of Stock @endif
                </span>
            </div>

            <!-- Price -->
            <div class="flex items-baseline gap-3 mb-6">
                <span class="text-3xl font-bold text-ink-900">₹{{ number_format($product->effective_price) }}</span>
                @if($product->discount_percent > 0)
                    <span class="text-lg text-ink-400 line-through">₹{{ number_format($product->price) }}</span>
                    <span class="bg-brand-100 text-brand-700 text-sm font-bold px-2 py-1 rounded">{{ $product->discount_percent }}% OFF</span>
                @endif
            </div>

            <p class="text-ink-600 text-sm leading-relaxed mb-6">{{ $product->description }}</p>

            @if($product->specifications)
                @php $specs = is_string($product->specifications) ? json_decode($product->specifications, true) : (array)$product->specifications; @endphp
                @if(!empty($specs))
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-ink-800 mb-2">Specifications</h3>
                        <dl class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-sm">
                            @foreach($specs as $key => $value)
                                <div class="flex justify-between border-b border-ink-100 py-1">
                                    <dt class="text-ink-500">{{ $key }}</dt>
                                    <dd class="text-ink-800 font-medium">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
            @endif

            <!-- Add to cart -->
            @if($product->stock > 0)
                <form action="{{ route('cart.add') }}" method="POST" class="flex items-center gap-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <div class="flex items-center border border-ink-200 rounded-lg overflow-hidden">
                        <button type="button" onclick="this.nextElementSibling.stepDown()" class="px-3 py-2.5 text-ink-600 hover:bg-ink-50">−</button>
                        <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="w-14 text-center border-0 focus:ring-0 outline-none">
                        <button type="button" onclick="this.previousElementSibling.stepUp()" class="px-3 py-2.5 text-ink-600 hover:bg-ink-50">+</button>
                    </div>
                    <button type="submit" class="flex-1 bg-brand-600 text-white font-semibold py-2.5 px-6 rounded-lg hover:bg-brand-700 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Add to Cart
                    </button>
                </form>
            @else
                <div class="bg-ink-100 text-ink-500 text-center py-3 rounded-lg font-medium">Currently Out of Stock</div>
            @endif

            <!-- Trust badges -->
            <div class="grid grid-cols-3 gap-3 mt-6 pt-6 border-t border-ink-100">
                <div class="text-center">
                    <div class="text-2xl mb-1">🚚</div>
                    <div class="text-xs text-ink-600 font-medium">Free Delivery</div>
                    <div class="text-xs text-ink-400">Orders over ₹500</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl mb-1">↩️</div>
                    <div class="text-xs text-ink-600 font-medium">Easy Returns</div>
                    <div class="text-xs text-ink-400">7-day return</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl mb-1">🔒</div>
                    <div class="text-xs text-ink-600 font-medium">Secure Payment</div>
                    <div class="text-xs text-ink-400">100% protected</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews -->
    @if($product->reviews->isNotEmpty())
    <section class="mb-12">
        <h2 class="text-lg font-bold text-ink-900 mb-4">Customer Reviews ({{ $product->review_count }})</h2>
        <div class="grid md:grid-cols-2 gap-4">
            @foreach($product->reviews->take(4) as $review)
                <div class="bg-white rounded-xl border border-ink-100 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold text-sm">
                                {{ strtoupper(substr($review->user->name ?? 'A', 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-sm font-medium text-ink-800">{{ $review->user->name ?? 'Anonymous' }}</div>
                                @if($review->is_verified_purchase)
                                    <div class="text-xs text-green-600 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Verified Purchase</div>
                                @endif
                            </div>
                        </div>
                        <div class="text-amber-400 text-sm">
                            @for($i = 1; $i <= $review->rating; $i++)★@endfor
                        </div>
                    </div>
                    @if($review->title)<h4 class="text-sm font-semibold text-ink-800 mb-1">{{ $review->title }}</h4>@endif
                    <p class="text-sm text-ink-600">{{ $review->body }}</p>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Related -->
    @if($related->isNotEmpty())
    <section>
        <h2 class="text-lg font-bold text-ink-900 mb-4">You May Also Like</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach($related as $relProduct)
                <x-product-card :product="$relProduct" />
            @endforeach
        </div>
    </section>
    @endif
</x-layouts.storefront>
