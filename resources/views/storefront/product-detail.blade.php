<x-layouts.storefront title="{{ $product->name }} — CityMarket">
    <!-- Breadcrumb -->
    <nav class="text-sm text-ink-500 mb-4 flex items-center gap-1.5">
        <a href="{{ route('home') }}" class="hover:text-brand-600">Home</a>
        <span class="text-ink-300">›</span>
        @if($product->category)
            <a href="{{ route('category', $product->category->slug) }}" class="hover:text-brand-600">{{ $product->category->name }}</a>
        @else
            <a href="{{ route('shop') }}" class="hover:text-brand-600">Shop</a>
        @endif
        <span class="text-ink-300">›</span>
        <span class="text-ink-700 font-medium truncate">{{ $product->name }}</span>
    </nav>

    <div class="grid lg:grid-cols-12 gap-8 mb-12">
        <!-- Image Gallery + 3D/AR Viewer -->
        <div class="lg:col-span-5">
            <div class="lg:sticky lg:top-24" data-reveal="right">
                <div class="bg-white rounded-2xl border border-ink-100 overflow-hidden aspect-square mb-3 relative group ar-stage gallery-main"
                    onclick="openLightbox(window.__galleryIndex ?? 0)">
                    @if($product->images->isNotEmpty())
                        @if($product->discount_percent > 0)
                            <div class="absolute top-3 left-3 z-10 bg-rose-500 text-white text-xs font-bold px-2.5 py-1 rounded-lg shadow-lg animate-bounce-in">-{{ $product->discount_percent }}%</div>
                        @endif
                        @if($product->is_flash_deal)
                            <div class="absolute top-3 right-3 z-10 bg-gradient-to-r from-orange-500 to-rose-500 text-white text-xs font-bold px-2.5 py-1 rounded-lg shadow-lg flex items-center gap-1 animate-pulse">⚡ FLASH</div>
                        @endif
                        <img id="main-product-image" src="{{ $product->images->first()->image_path }}" alt="{{ $product->name }}" width="1200" height="1200" decoding="async" fetchpriority="high" class="w-full h-full object-cover">
                        <!-- Zoom hint -->
                        <div class="absolute bottom-3 left-3 z-10 bg-white/85 backdrop-blur text-ink-700 text-[10px] font-bold px-2.5 py-1 rounded-full shadow flex items-center gap-1 pointer-events-none">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                            Click to zoom
                        </div>
                    @else
                        <div class="w-full h-full pc-placeholder flex items-center justify-center text-ink-400">
                            <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                    <!-- AR View Button (floating) -->
                    <button data-ar-open class="absolute bottom-3 right-3 z-20 bg-ink-900/90 backdrop-blur text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-lg flex items-center gap-2 hover:bg-brand-600 transition-all magnetic-btn animate-glow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 9l9-6 9 6M3 9v6m18-6v6"/></svg>
                        View in AR
                    </button>
                </div>
                @if($product->images->count() > 1)
                    <div class="grid grid-cols-5 gap-2" id="gallery-thumbs">
                        @foreach($product->images as $img)
                            <button onclick="setGalleryImage({{ $loop->index }}, this); event.stopPropagation();"
                                data-idx="{{ $loop->index }}"
                                class="gallery-thumb aspect-square {{ $loop->first ? 'active' : '' }}">
                                <img src="{{ $img->image_path }}" alt="{{ $product->name }} view {{ $loop->iteration }}" width="200" height="200" decoding="async" loading="lazy" class="product-img">
                            </button>
                        @endforeach
                    </div>
                @endif
                <!-- Trust badges with animations -->
                <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                    <div class="p-3 bg-green-50 rounded-xl" data-reveal="scale" data-reveal-delay="0">
                        <div class="text-2xl mb-1 animate-float">🚚</div>
                        <div class="text-xs font-semibold text-ink-700">Free Delivery</div>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-xl" data-reveal="scale" data-reveal-delay="100">
                        <div class="text-2xl mb-1 animate-float-slow">🔄</div>
                        <div class="text-xs font-semibold text-ink-700">7-Day Returns</div>
                    </div>
                    <div class="p-3 bg-purple-50 rounded-xl" data-reveal="scale" data-reveal-delay="200">
                        <div class="text-2xl mb-1 animate-float">🔒</div>
                        <div class="text-xs font-semibold text-ink-700">Secure Pay</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AR Modal (immersive product viewer) -->
        @if($product->images->isNotEmpty())
        <div id="ar-modal" class="ar-modal-backdrop">
            <div class="ar-modal-panel relative w-full max-w-md mx-4">
                <!-- Close button -->
                <button data-ar-close class="absolute -top-12 right-0 w-10 h-10 rounded-full bg-white/10 backdrop-blur flex items-center justify-center text-white hover:bg-white/30 transition-all z-10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <!-- AR header -->
                <div class="text-center mb-4">
                    <span class="inline-block bg-white/10 backdrop-blur text-white text-xs font-bold px-3 py-1.5 rounded-full mb-2 uppercase tracking-widest">AR Preview</span>
                    <h3 class="text-lg font-bold text-white">{{ $product->name }}</h3>
                    <p class="text-xs text-white/60 mt-1">Drag to rotate · Auto-rotating</p>
                </div>
                <!-- 3D product stage -->
                <div class="ar-stage h-72 flex items-center justify-center">
                    <div id="ar-product-card" class="ar-product-card ar-auto-rotate relative w-48 h-48 cursor-grab active:cursor-grabbing">
                        <div class="ar-face ar-face-front shadow-2xl rounded-2xl overflow-hidden">
                            <img src="{{ $product->images->first()->image_path }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        </div>
                        <div class="ar-face ar-face-back shadow-2xl rounded-2xl overflow-hidden bg-ink-800 flex items-center justify-center">
                            @if($product->images->count() > 1)
                                <img src="{{ $product->images->skip(1)->first()->image_path }}" alt="" class="w-full h-full object-cover" style="transform: scaleX(-1);">
                            @else
                                <img src="{{ $product->images->first()->image_path }}" alt="" class="w-full h-full object-cover" style="transform: scaleX(-1);">
                            @endif
                        </div>
                    </div>
                </div>
                <!-- Floating particles -->
                <div class="absolute top-10 left-8 w-6 h-6 rounded-full bg-brand-500/20 animate-float"></div>
                <div class="absolute bottom-20 right-8 w-8 h-8 rounded-full bg-purple-500/20 animate-float-slow"></div>
                <div class="absolute top-20 right-12 w-4 h-4 rounded-full bg-cyan-500/30 animate-float-fast"></div>
                <!-- AR controls -->
                <div class="flex justify-center gap-3 mt-4">
                    <button onclick="const c=document.getElementById('ar-product-card'); c.classList.toggle('ar-auto-rotate'); if(c.classList.contains('ar-auto-rotate')){c.style.transform='';}" class="bg-white/10 backdrop-blur text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-white/20 transition-all magnetic-btn">
                        ↻ Toggle Rotate
                    </button>
                    <button data-ar-close class="bg-brand-600 text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-brand-500 transition-all magnetic-btn">Close</button>
                </div>
            </div>
        </div>
        @endif

        <!-- Details -->
        <div class="lg:col-span-7" data-reveal="left">
            @if($product->brand)
                <a href="{{ route('shop', ['brand' => $product->brand_id]) }}" class="inline-block text-sm text-brand-600 font-semibold hover:underline bg-brand-50 px-3 py-1 rounded-full mb-3 animate-bounce-in">{{ $product->brand->name }}</a>
            @endif
            @if($product->vendor && $product->vendor->status === 'approved')
                <a href="{{ route('vendor.shop', $product->vendor->slug) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-purple-700 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 px-3 py-1 rounded-full mb-3 ml-1 border border-purple-100 transition-colors">
                    🏪 Sold by {{ $product->vendor->shop_name }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endif
            <h1 class="text-3xl font-bold text-ink-900 mb-3 shimmer-text">{{ $product->name }}</h1>

            <div class="flex flex-wrap items-center gap-3 mb-5">
                <div class="flex items-center bg-green-50 px-2.5 py-1 rounded-lg">
                    <span class="text-amber-400 text-sm">
                        @for($i = 1; $i <= 5; $i++)
                            @if($product->rating >= $i)★ @elseif($product->rating >= $i - 0.5)★ @else☆@endif
                        @endfor
                    </span>
                    <span class="text-ink-800 text-sm ml-1 font-bold">{{ number_format($product->rating, 1) }}</span>
                </div>
                <a href="#reviews" class="text-sm text-ink-500 hover:text-brand-600 underline">{{ $product->review_count }} reviews</a>
                <span class="text-sm @if($product->stock > 0) text-green-600 @else text-red-600 @endif font-medium flex items-center gap-1">
                    @if($product->stock > 0) ✓ In Stock @else ✗ Out of Stock @endif
                </span>
            </div>

            <!-- Price -->
            <div class="flex items-baseline gap-3 mb-6 pb-6 border-b border-ink-100">
                <span class="text-4xl font-bold text-ink-900">₹{{ number_format($product->effective_price) }}</span>
                @if($product->discount_percent > 0)
                    <span class="text-xl text-ink-400 line-through">₹{{ number_format($product->price) }}</span>
                    <span class="bg-rose-100 text-rose-700 text-sm font-bold px-2 py-1 rounded-lg">Save ₹{{ number_format($product->price - $product->effective_price) }} ({{ $product->discount_percent }}%)</span>
                @endif
            </div>

            <!-- Tabs -->
            <div x-data="{ tab: 'desc' }" class="mb-6">
                <div class="flex gap-1 border-b border-ink-100 mb-4">
                    <button @click="tab='desc'" :class="tab==='desc' ? 'text-brand-600 border-brand-600' : 'text-ink-500 border-transparent hover:text-ink-800'" class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors">Description</button>
                    @if($product->specifications)
                        <button @click="tab='specs'" :class="tab==='specs' ? 'text-brand-600 border-brand-600' : 'text-ink-500 border-transparent hover:text-ink-800'" class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors">Specifications</button>
                    @endif
                    <button @click="tab='reviews'" :class="tab==='reviews' ? 'text-brand-600 border-brand-600' : 'text-ink-500 border-transparent hover:text-ink-800'" class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors">Reviews ({{ $product->review_count }})</button>
                </div>

                <!-- Description Tab -->
                <div x-show="tab==='desc'" class="text-ink-600 text-sm leading-relaxed">
                    <p>{{ $product->description }}</p>
                </div>

                <!-- Specs Tab -->
                @if($product->specifications)
                    @php $specs = is_string($product->specifications) ? json_decode($product->specifications, true) : (array)$product->specifications; @endphp
                    <div x-show="tab==='specs'" x-cloak>
                        @if(!empty($specs))
                            <dl class="grid grid-cols-2 gap-x-4">
                                @foreach($specs as $key => $value)
                                    <div class="flex justify-between border-b border-ink-100 py-2.5">
                                        <dt class="text-ink-500 text-sm">{{ $key }}</dt>
                                        <dd class="text-ink-800 font-medium text-sm text-right">{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    </div>
                @endif

                <!-- Reviews Tab -->
                <div x-show="tab==='reviews'" x-cloak id="reviews">
                    @if($product->reviews->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($product->reviews->take(4) as $review)
                                <div class="bg-ink-50 rounded-xl p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold text-sm">
                                                {{ strtoupper(substr($review->user->name ?? 'A', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-ink-800">{{ $review->user->name ?? 'Anonymous' }}</div>
                                                @if($review->is_verified_purchase)
                                                    <div class="text-xs text-green-600 flex items-center gap-1">✓ Verified Purchase</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-amber-400 text-sm">@for($i = 1; $i <= $review->rating; $i++)★@endfor</div>
                                    </div>
                                    @if($review->title)<h4 class="text-sm font-semibold text-ink-800 mb-1">{{ $review->title }}</h4>@endif
                                    <p class="text-sm text-ink-600">{{ $review->body }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-ink-400 text-sm text-center py-8">No reviews yet. Be the first to review!</p>
                    @endif

                    @auth
                        <form action="{{ route('review.store', $product->slug) }}" method="POST" class="mt-4 bg-white rounded-xl border border-ink-100 p-4 space-y-3">
                            @csrf
                            <h4 class="text-sm font-bold text-ink-800">Write a Review</h4>
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-medium text-gray-700">Rating:</label>
                                <select name="rating" class="rounded-lg border-gray-300 text-sm" required>
                                    <option value="5">★★★★★ (5)</option>
                                    <option value="4">★★★★ (4)</option>
                                    <option value="3">★★★ (3)</option>
                                    <option value="2">★★ (2)</option>
                                    <option value="1">★ (1)</option>
                                </select>
                            </div>
                            <input type="text" name="title" placeholder="Review title (optional)" class="w-full rounded-lg border-gray-300 text-sm">
                            <textarea name="body" rows="3" placeholder="Share your experience..." class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                            <button type="submit" class="px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700">Post Review</button>
                        </form>
                    @endauth
                </div>
            </div>

            <!-- Add to cart -->
            @if($product->stock > 0)
                <form action="{{ route('cart.add') }}" method="POST" class="flex flex-wrap items-center gap-3 mb-6" data-reveal="up">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <div class="flex items-center border-2 border-ink-200 rounded-xl overflow-hidden bg-white">
                        <button type="button" onclick="var i=this.nextElementSibling; if(i.value>1){i.value--; i.dispatchEvent(new Event('change'))}" class="px-4 py-3 text-ink-600 hover:bg-brand-50 hover:text-brand-600 text-lg font-bold transition-colors">−</button>
                        <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="w-16 text-center border-0 focus:ring-0 outline-none font-bold text-lg">
                        <button type="button" onclick="var i=this.previousElementSibling; if(i.value<{{ $product->stock }}){i.value++; i.dispatchEvent(new Event('change'))}" class="px-4 py-3 text-ink-600 hover:bg-brand-50 hover:text-brand-600 text-lg font-bold transition-colors">+</button>
                    </div>
                    <button type="submit" class="flex-1 min-w-[200px] bg-brand-600 text-white font-semibold py-3 px-8 rounded-xl hover:bg-brand-700 transition-all flex items-center justify-center gap-2 shadow-lg shadow-brand-600/20 hover:shadow-brand-600/40 hover:scale-105 btn-shine sweep-shine magnetic-btn">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Add to Cart
                    </button>
                    @auth
                        <form action="{{ route('wishlist.add') }}" method="POST" class="contents">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="p-3 border-2 border-ink-200 rounded-xl text-ink-600 hover:text-rose-600 hover:border-rose-400 hover:scale-110 transition-all magnetic-btn" title="Add to Wishlist">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </button>
                        </form>
                    @endauth
                </form>
            @else
                <div class="bg-ink-100 text-ink-500 text-center py-4 rounded-xl font-semibold mb-6 animate-pulse">Currently Out of Stock</div>
            @endif

            <!-- Trust badges -->
            <div class="grid grid-cols-3 gap-3 pt-6 border-t border-ink-100">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-lg">🚚</div>
                    <div><div class="text-xs text-ink-800 font-semibold">Free Delivery</div><div class="text-xs text-ink-400">Orders ₹500+</div></div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-lg">↩️</div>
                    <div><div class="text-xs text-ink-800 font-semibold">Easy Returns</div><div class="text-xs text-ink-400">7-day policy</div></div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-lg">🔒</div>
                    <div><div class="text-xs text-ink-800 font-semibold">Secure Pay</div><div class="text-xs text-ink-400">Protected</div></div>
                </div>
            </div>
        </div>
    </div>

    @if($related->isNotEmpty())
    <section class="mb-12" data-reveal="up">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-ink-900 shimmer-text">You May Also Like</h2>
            @if($product->category)
                <a href="{{ route('category', $product->category->slug) }}" class="text-sm text-brand-600 hover:underline font-medium anim-underline">View all →</a>
            @else
                <a href="{{ route('shop') }}" class="text-sm text-brand-600 hover:underline font-medium anim-underline">View all →</a>
            @endif
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 perspective-1000">
            @foreach($related as $relProduct)
                <div class="card-3d-tilt" data-reveal="scale" data-reveal-delay="{{ $loop->index * 80 }}">
                    <x-product-card :product="$relProduct" />
                </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Sticky mobile add-to-cart bar -->
    @if($product->stock > 0)
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-ink-100 p-3 flex items-center gap-3 z-40" x-data="{ qty: 1 }">
        <div>
            <div class="text-lg font-bold text-ink-900">₹{{ number_format($product->effective_price) }}</div>
            @if($product->discount_percent > 0)<div class="text-xs text-ink-400 line-through">₹{{ number_format($product->price) }}</div>@endif
        </div>
        <form action="{{ route('cart.add') }}" method="POST" class="flex-1">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-3 rounded-xl hover:bg-brand-700">Add to Cart</button>
        </form>
    </div>
    @endif

    <!-- Gallery state -->
    <script>
        window.__galleryImages = @js($product->images->pluck('image_path'));
        window.__galleryIndex = 0;

        function setGalleryImage(index, btn) {
            window.__galleryIndex = index;
            const main = document.getElementById('main-product-image');
            if (main && window.__galleryImages[index]) {
                main.style.opacity = '0';
                setTimeout(() => {
                    main.src = window.__galleryImages[index];
                    main.style.transition = 'opacity 0.25s ease';
                    main.style.opacity = '1';
                }, 120);
            }
            document.querySelectorAll('#gallery-thumbs .gallery-thumb').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
        }

        function openLightbox(index) {
            if (!window.__galleryImages || !window.__galleryImages.length) return;
            window.__galleryIndex = index;
            document.getElementById('lightbox-img').src = window.__galleryImages[index];
            document.getElementById('product-lightbox').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('product-lightbox').classList.remove('open');
            document.body.style.overflow = '';
            // sync main image back to the last viewed one
            const main = document.getElementById('main-product-image');
            if (main) main.src = window.__galleryImages[window.__galleryIndex];
            document.querySelectorAll('#gallery-thumbs .gallery-thumb').forEach((b, i) => b.classList.toggle('active', i === window.__galleryIndex));
        }

        function lightboxNav(step) {
            const n = window.__galleryImages.length;
            window.__galleryIndex = (window.__galleryIndex + step + n) % n;
            document.getElementById('lightbox-img').src = window.__galleryImages[window.__galleryIndex];
        }

        document.addEventListener('keydown', (e) => {
            const lb = document.getElementById('product-lightbox');
            if (!lb || !lb.classList.contains('open')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowRight') lightboxNav(1);
            if (e.key === 'ArrowLeft') lightboxNav(-1);
        });
    </script>

    <!-- Lightbox -->
    <div id="product-lightbox" class="lightbox" onclick="if (event.target === this) closeLightbox()">
        <button class="lightbox-close" onclick="closeLightbox()" aria-label="Close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        @if($product->images->count() > 1)
            <button class="lightbox-nav lightbox-prev" onclick="lightboxNav(-1)" aria-label="Previous">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button class="lightbox-nav lightbox-next" onclick="lightboxNav(1)" aria-label="Next">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        @endif
        <img id="lightbox-img" src="{{ $product->images->first()->image_path ?? '' }}" alt="{{ $product->name }}">
    </div>
</x-layouts.storefront>