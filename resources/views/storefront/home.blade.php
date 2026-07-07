<x-layouts.storefront title="CityMarket — Premium Shopping, Smarter with AI">

    <!-- Immersive 3D Hero Carousel -->
    <section class="mb-6 relative" x-data="heroCarousel()">
        <!-- Particle canvas background -->
        <canvas id="particle-canvas" class="particle-canvas"></canvas>

        <div class="relative rounded-3xl overflow-hidden h-72 md:h-96 lg:h-[420px] bg-ink-900 perspective-1000">
            <!-- Floating decorative shapes (3D depth) -->
            <div class="absolute top-8 left-8 w-24 h-24 rounded-2xl bg-gradient-to-br from-brand-500/30 to-purple-500/20 animate-float blur-sm" style="transform: translateZ(30px)"></div>
            <div class="absolute top-16 right-20 w-16 h-16 rounded-full bg-cyan-500/20 animate-float-slow" style="transform: translateZ(50px)"></div>
            <div class="absolute bottom-20 left-1/3 w-20 h-20 rounded-full border-2 border-white/10 animate-spin-slow" style="transform: translateZ(20px)"></div>
            <div class="absolute bottom-10 right-10 w-12 h-12 rounded-lg bg-amber-500/20 animate-float-fast rotate-45" style="transform: translateZ(40px)"></div>

            <template x-for="(slide, i) in slides" :key="i">
                <div x-show="current === i"
                     x-transition:enter="transition ease-out duration-700"
                     x-transition:enter-start="opacity-0 scale-105"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-500"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="absolute inset-0 preserve-3d">
                    <!-- Background image -->
                    <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105" :style="`background-image: url('${slide.image}');`"></div>
                    <!-- Gradient overlay -->
                    <div class="absolute inset-0 animate-gradient" :style="`background: ${slide.overlay}; background-size: 200% 200%;`"></div>
                    <!-- Decorative floating rings -->
                    <div class="absolute right-8 top-8 w-32 h-32 rounded-full border-4 border-white/10 animate-spin-slow" style="transform: translateZ(30px)"></div>
                    <div class="absolute right-20 bottom-12 w-16 h-16 rounded-full bg-white/10 backdrop-blur-sm animate-float" style="transform: translateZ(50px)"></div>
                    <!-- Content with 3D depth -->
                    <div class="relative z-10 h-full flex flex-col justify-center p-8 md:p-12 max-w-lg" style="transform: translateZ(40px)">
                        <span class="inline-block self-start bg-white/25 backdrop-blur text-white text-xs font-bold px-4 py-1.5 rounded-full mb-4 uppercase tracking-wider animate-bounce-in" x-text="slide.badge"></span>
                        <h2 class="text-3xl md:text-5xl font-bold text-white mb-3 leading-tight drop-shadow-lg" x-text="slide.title"></h2>
                        <p class="text-base md:text-lg text-white/90 mb-6 drop-shadow" x-text="slide.subtitle"></p>
                        <a :href="slide.link" class="inline-flex items-center gap-2 self-start bg-white text-ink-900 text-sm font-bold px-6 py-3 rounded-xl hover:bg-white/90 hover:scale-105 transition-all shadow-2xl btn-shine sweep-shine magnetic-btn">
                            <span x-text="slide.cta"></span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            </template>

            <!-- Dots -->
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-20">
                <template x-for="(slide, i) in slides" :key="'dot'+i">
                    <button @click="current = i" :class="current === i ? 'w-8 bg-white' : 'w-2 bg-white/50'" class="h-2 rounded-full transition-all duration-300"></button>
                </template>
            </div>

            <!-- Arrows -->
            <button @click="prev()" class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-white/20 backdrop-blur flex items-center justify-center text-white hover:bg-white/40 hover:scale-110 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button @click="next()" class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-white/20 backdrop-blur flex items-center justify-center text-white hover:bg-white/40 hover:scale-110 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </section>

    <!-- Three Promotional Banners (Bento Grid) -->
    <section class="mb-8" data-reveal="up">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 perspective-1000">
            <!-- Banner 1: Fashion Sale — large left card -->
            <div class="md:col-span-2 md:row-span-2 relative rounded-2xl overflow-hidden group cursor-pointer min-h-[200px] md:min-h-[320px] sweep-shine" onclick="window.location='{{ route('category', 'fashion') }}'">
                <!-- Background image -->
                <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-110" style="background-image: url('https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=800&h=600&fit=crop');"></div>
                <!-- Gradient overlay -->
                <div class="absolute inset-0 bg-gradient-to-br from-rose-500/70 via-pink-500/40 to-transparent"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                <!-- Decorative shapes -->
                <div class="absolute -top-8 -left-8 w-32 h-32 rounded-full border-4 border-white/10 animate-spin-slow"></div>
                <div class="absolute -bottom-4 right-12 w-20 h-20 rounded-full bg-white/10 backdrop-blur-sm group-hover:scale-150 transition-transform duration-700 animate-float"></div>
                <div class="absolute top-8 right-8 w-3 h-3 rounded-full bg-white/60 animate-pulse"></div>
                <div class="relative z-10 h-full flex flex-col justify-end p-6 md:p-8">
                    <span class="inline-block self-start bg-white/25 backdrop-blur text-white text-[11px] font-bold px-3 py-1 rounded-full mb-3 uppercase tracking-widest animate-bounce-in">Limited Time</span>
                    <h2 class="text-2xl md:text-4xl font-extrabold text-white mb-1 leading-tight drop-shadow-lg">Fashion Fiesta</h2>
                    <p class="text-sm md:text-base text-white/90 mb-4 drop-shadow">Flat 50% OFF on top brands · Style that speaks</p>
                    <span class="inline-flex items-center gap-2 self-start bg-white text-ink-900 text-sm font-bold px-5 py-2.5 rounded-xl group-hover:gap-3 transition-all shadow-lg btn-shine magnetic-btn">
                        Shop Fashion
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                </div>
            </div>

            <!-- Banner 2: Beauty Bonanza — top right -->
            <div class="relative rounded-2xl overflow-hidden group cursor-pointer min-h-[150px] sweep-shine" onclick="window.location='{{ route('category', 'beauty') }}'">
                <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-110" style="background-image: url('https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=600&h=300&fit=crop');"></div>
                <div class="absolute inset-0 bg-gradient-to-br from-violet-600/70 to-transparent"></div>
                <div class="absolute top-3 right-3 w-12 h-12 rounded-full border-2 border-white/20 animate-spin-slow"></div>
                <div class="relative z-10 h-full flex flex-col justify-center p-5 md:p-6">
                    <span class="inline-block self-start bg-white/25 backdrop-blur text-white text-[10px] font-bold px-2.5 py-1 rounded-full mb-2 uppercase tracking-widest">Exclusive</span>
                    <h3 class="text-lg md:text-xl font-extrabold text-white mb-0.5 drop-shadow">Beauty Bonanza</h3>
                    <p class="text-xs md:text-sm text-white/90 mb-3">Up to 40% OFF premium skincare</p>
                    <span class="inline-flex items-center gap-1.5 self-start bg-white/25 backdrop-blur text-white text-xs font-bold px-4 py-2 rounded-lg group-hover:bg-white/45 transition-colors magnetic-btn">
                        Explore <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </div>
            </div>

            <!-- Banner 3: Electronics Deal — bottom right -->
            <div class="relative rounded-2xl overflow-hidden group cursor-pointer min-h-[150px] sweep-shine" onclick="window.location='{{ route('category', 'electronics') }}'">
                <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-110" style="background-image: url('https://images.unsplash.com/photo-1498049794561-7780e7231661?w=600&h=300&fit=crop');"></div>
                <div class="absolute inset-0 bg-gradient-to-br from-cyan-600/70 to-transparent"></div>
                <div class="absolute top-3 right-3 w-10 h-10 rounded-full bg-white/10 backdrop-blur-sm animate-float"></div>
                <div class="relative z-10 h-full flex flex-col justify-center p-5 md:p-6">
                    <span class="inline-flex items-center gap-1 self-start bg-white/25 backdrop-blur text-white text-[10px] font-bold px-2.5 py-1 rounded-full mb-2 uppercase tracking-widest">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span> Live Deals
                    </span>
                    <h3 class="text-lg md:text-xl font-extrabold text-white mb-0.5 drop-shadow">Tech Trends</h3>
                    <p class="text-xs md:text-sm text-white/90 mb-3">Best prices on gadgets & devices</p>
                    <span class="inline-flex items-center gap-1.5 self-start bg-white/25 backdrop-blur text-white text-xs font-bold px-4 py-2 rounded-lg group-hover:bg-white/45 transition-colors magnetic-btn">
                        Shop Now <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Strip -->
    <section class="mb-8" data-reveal="up">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="flex items-center gap-3 p-3.5 hover:bg-ink-50 rounded-xl transition-all hover:scale-105 glow-border" data-reveal="scale" data-reveal-delay="0">
                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center shrink-0 animate-float">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </div>
                <div><div class="text-sm font-semibold text-ink-900">Free Delivery</div><div class="text-xs text-ink-500">On orders over ₹500</div></div>
            </div>
            <div class="flex items-center gap-3 p-3.5 hover:bg-ink-50 rounded-xl transition-all hover:scale-105 glow-border" data-reveal="scale" data-reveal-delay="100">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center shrink-0 animate-float-slow">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div><div class="text-sm font-semibold text-ink-900">Secure Payment</div><div class="text-xs text-ink-500">100% protected</div></div>
            </div>
            <div class="flex items-center gap-3 p-3.5 hover:bg-ink-50 rounded-xl transition-all hover:scale-105 glow-border" data-reveal="scale" data-reveal-delay="200">
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center shrink-0 animate-float">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div><div class="text-sm font-semibold text-ink-900">Easy Returns</div><div class="text-xs text-ink-500">7-day return policy</div></div>
            </div>
            <div class="flex items-center gap-3 p-3.5 hover:bg-ink-50 rounded-xl transition-all hover:scale-105 glow-border" data-reveal="scale" data-reveal-delay="300">
                <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center shrink-0 animate-float-slow">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div><div class="text-sm font-semibold text-ink-900">24/7 Support</div><div class="text-xs text-ink-500">Always here to help</div></div>
            </div>
        </div>
    </section>

    <!-- Categories with large image tiles -->
    <section class="mb-10" data-reveal="up">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-ink-900 shimmer-text">Shop by Category</h2>
                <p class="text-sm text-ink-500 mt-0.5">Explore our wide range of categories</p>
            </div>
            <a href="{{ route('shop') }}" class="text-sm text-brand-600 hover:text-brand-700 font-semibold flex items-center gap-1 anim-underline">
                View All <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-3 md:grid-cols-6 gap-3 perspective-1000">
            @foreach($categories as $cat)
                <a href="{{ route('category', $cat->slug) }}" class="group flex flex-col items-center gap-2" data-reveal="scale" data-reveal-delay="{{ $loop->index * 60 }}">
                    <div class="w-full aspect-square rounded-2xl bg-gradient-to-br from-ink-50 to-ink-100 border border-ink-100 flex items-center justify-center text-3xl md:text-4xl group-hover:from-brand-50 group-hover:to-brand-100 group-hover:border-brand-200 group-hover:scale-110 group-hover:rotate-6 transition-all duration-300 shadow-sm animate-float-slow">
                        {{ $cat->icon ?? '🛒' }}
                    </div>
                    <span class="text-xs md:text-sm font-semibold text-ink-700 text-center group-hover:text-brand-600 transition-colors">{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <!-- Promo Banner: AI Assistant -->
    <section class="mb-10" data-reveal="scale">
        <a href="{{ route('assistant') }}" class="block relative rounded-2xl overflow-hidden p-6 md:p-8 group sweep-shine">
            <!-- Background image -->
            <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105" style="background-image: url('https://images.unsplash.com/photo-1677442136019-21780ecad995?w=1200&h=300&fit=crop');"></div>
            <!-- Gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-r from-violet-700/80 via-purple-700/50 to-transparent"></div>
            <!-- Decorative floating shapes -->
            <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-white/10 skew-x-12 origin-top-right group-hover:translate-x-12 transition-transform duration-700"></div>
            <div class="absolute -bottom-4 -left-4 w-24 h-24 rounded-full border-4 border-white/10 animate-morph"></div>
            <div class="absolute top-6 right-10 w-16 h-16 rounded-full bg-white/10 backdrop-blur-sm animate-float"></div>
            <div class="relative z-10 flex items-center gap-4">
                <div class="w-14 h-14 md:w-16 md:h-16 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-3xl md:text-4xl transform group-hover:scale-110 group-hover:rotate-12 transition-transform shrink-0 animate-glow">🤖</div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg md:text-xl font-bold text-white drop-shadow">AI Shopping Assistant</h3>
                    <p class="text-sm text-white/85 mt-0.5">"best running shoes under 2000" — just ask, we'll find it!</p>
                </div>
                <div class="hidden sm:flex bg-white/25 backdrop-blur px-5 py-2.5 rounded-xl text-sm font-bold text-white group-hover:bg-white/45 transition-colors items-center gap-1.5 whitespace-nowrap magnetic-btn">
                    Try Now <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </div>
            </div>
        </a>
    </section>

    <!-- Flash Deals -->
    @if($flashDeals->isNotEmpty())
    <section class="mb-10" data-reveal="up">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-ink-900 flex items-center gap-2 shimmer-text">⚡ Flash Deals</h2>
                    <p class="text-sm text-ink-500 mt-0.5">Limited time — grab them fast!</p>
                </div>
            </div>
            <a href="{{ route('shop', ['on_sale' => 1]) }}" class="text-sm text-brand-600 hover:text-brand-700 font-semibold flex items-center gap-1">
                View All <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="flex gap-3 sm:gap-4 overflow-x-auto no-scrollbar pb-2 scroll-snap-x">
            @foreach($flashDeals as $product)
                <div class="w-40 sm:w-44 md:w-48 shrink-0 scroll-snap-start animate-stagger" style="animation-delay: {{ $loop->index * 50 }}ms">
                    <x-product-card :product="$product" />
                </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Mid-page Split Banner: Home & Kitchen + Sports -->
    <section class="mb-10" data-reveal="up">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Home & Kitchen -->
            <a href="{{ route('category', 'home-kitchen') }}" class="relative rounded-2xl overflow-hidden group min-h-[160px] block sweep-shine">
                <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-110" style="background-image: url('https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=600&h=400&fit=crop');"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-amber-500/75 via-orange-500/40 to-transparent"></div>
                <div class="absolute top-4 right-4 w-12 h-12 rounded-full border-2 border-white/20 animate-spin-slow"></div>
                <div class="absolute -bottom-4 -left-4 w-24 h-24 rounded-full border-4 border-white/10 animate-morph"></div>
                <div class="relative z-10 h-full flex flex-col justify-center p-5 md:p-6" style="transform: translateZ(30px)">
                    <span class="text-[10px] font-bold text-white/80 uppercase tracking-widest mb-1">Cozy Collection</span>
                    <h3 class="text-lg md:text-xl font-extrabold text-white mb-0.5 drop-shadow">Home & Kitchen</h3>
                    <p class="text-xs text-white/85 mb-3">Upgrade your space with style</p>
                    <span class="inline-flex items-center gap-1.5 self-start bg-white text-ink-900 text-xs font-bold px-4 py-2 rounded-lg group-hover:gap-2.5 transition-all shadow-md magnetic-btn">
                        Discover More <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </div>
            </a>

            <!-- Sports & Fitness -->
            <a href="{{ route('category', 'sports') }}" class="relative rounded-2xl overflow-hidden group min-h-[160px] block sweep-shine">
                <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-110" style="background-image: url('https://images.unsplash.com/photo-1517649763962-0c623066013b?w=600&h=400&fit=crop');"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-600/75 via-green-600/40 to-transparent"></div>
                <div class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 backdrop-blur-sm animate-float"></div>
                <div class="absolute -bottom-4 -left-4 w-20 h-20 rounded-full border-4 border-white/10 animate-morph"></div>
                <div class="relative z-10 h-full flex flex-col justify-center p-5 md:p-6" style="transform: translateZ(30px)">
                    <span class="text-[10px] font-bold text-white/80 uppercase tracking-widest mb-1">Get Active</span>
                    <h3 class="text-lg md:text-xl font-extrabold text-white mb-0.5 drop-shadow">Sports & Fitness</h3>
                    <p class="text-xs text-white/85 mb-3">Gear up for your active lifestyle</p>
                    <span class="inline-flex items-center gap-1.5 self-start bg-white text-ink-900 text-xs font-bold px-4 py-2 rounded-lg group-hover:gap-2.5 transition-all shadow-md magnetic-btn">
                        Explore <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </div>
            </a>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="mb-10" data-reveal="up">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-ink-900 shimmer-text">✨ Featured Products</h2>
                <p class="text-sm text-ink-500 mt-0.5">Handpicked just for you</p>
            </div>
            <a href="{{ route('shop') }}" class="text-sm text-brand-600 hover:text-brand-700 font-semibold flex items-center gap-1 anim-underline">
                View All <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="flex gap-3 sm:gap-4 overflow-x-auto no-scrollbar pb-2 scroll-snap-x perspective-1000">
            @foreach($featured as $product)
                <div class="w-40 sm:w-44 md:w-48 shrink-0 scroll-snap-start card-3d-tilt" data-reveal="left" data-reveal-delay="{{ $loop->index * 60 }}">
                    <x-product-card :product="$product" />
                </div>
            @endforeach
        </div>
    </section>

    <!-- Trending Now -->
    <section class="mb-10" data-reveal="up">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-ink-900 shimmer-text">🔥 Trending Now</h2>
                <p class="text-sm text-ink-500 mt-0.5">What everyone's loving</p>
            </div>
            <a href="{{ route('shop') }}" class="text-sm text-brand-600 hover:text-brand-700 font-semibold flex items-center gap-1 anim-underline">
                View All <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="flex gap-3 sm:gap-4 overflow-x-auto no-scrollbar pb-2 scroll-snap-x perspective-1000">
            @foreach($trending as $product)
                <div class="w-40 sm:w-44 md:w-48 shrink-0 scroll-snap-start card-3d-tilt" data-reveal="right" data-reveal-delay="{{ $loop->index * 60 }}">
                    <x-product-card :product="$product" />
                </div>
            @endforeach
        </div>
    </section>

    <!-- AI Recommendations (logged-in only) -->
    @auth
        @if($recommendations->isNotEmpty())
        <section class="mb-10" data-reveal="up">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <div>
                        <h2 class="text-xl md:text-2xl font-bold text-ink-900 shimmer-text">🎯 Recommended For You</h2>
                        <p class="text-sm text-ink-500 mt-0.5">AI-curated based on your activity</p>
                    </div>
                </div>
            </div>
            <div class="flex gap-3 sm:gap-4 overflow-x-auto no-scrollbar pb-2 scroll-snap-x perspective-1000">
                @foreach($recommendations as $product)
                    <div class="w-40 sm:w-44 md:w-48 shrink-0 scroll-snap-start card-3d-tilt">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        </section>
        @endif
    @endauth

    <!-- Top Brands -->
    @if($brands->isNotEmpty())
    <section class="mb-10" data-reveal="up">
        <h2 class="text-xl md:text-2xl font-bold text-ink-900 mb-5 shimmer-text">Shop by Brand</h2>
        <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-8 gap-3 perspective-1000">
            @foreach($brands as $brand)
                <div class="flex flex-col items-center justify-center p-4 bg-white rounded-xl border border-ink-100 hover:border-brand-300 hover:shadow-lg hover:-translate-y-1 transition-all cursor-pointer card-3d-tilt glow-border" data-reveal="scale" data-reveal-delay="{{ $loop->index * 40 }}">
                    <div class="text-2xl mb-1 animate-float">{{ $brand->logo ?? '🏷️' }}</div>
                    <div class="text-xs font-semibold text-ink-700 text-center">{{ $brand->name }}</div>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Newsletter CTA -->
    <section class="mb-6" data-reveal="scale">
        <div class="rounded-3xl p-8 md:p-12 text-center relative overflow-hidden">
            <!-- Background image -->
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1557683316-973673baf926?w=1200&h=400&fit=crop');"></div>
            <!-- Dark gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-ink-900/95 to-ink-800/90"></div>
            <!-- Floating shapes -->
            <div class="absolute left-4 top-4 text-9xl opacity-5 animate-float">✉</div>
            <div class="absolute right-4 bottom-4 text-9xl opacity-5 animate-float-slow">✉</div>
            <div class="absolute -top-12 left-1/3 w-40 h-40 rounded-full border-4 border-white/5 animate-spin-slow"></div>
            <div class="absolute -bottom-12 right-1/4 w-32 h-32 rounded-full bg-brand-600/10 animate-morph"></div>
            <div class="absolute top-12 right-16 w-6 h-6 rounded-full bg-white/10 animate-float-fast"></div>
            <div class="relative z-10 max-w-md mx-auto">
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-2 shimmer-text">Stay in the Loop</h2>
                <p class="text-ink-400 text-sm mb-6">Get exclusive deals, new arrivals, and AI-curated picks delivered to your inbox.</p>
                <form class="flex flex-col sm:flex-row gap-3" onsubmit="event.preventDefault(); showToast('Subscribed successfully!', 'success'); if(window.CityMarketFX){const r=event.target.getBoundingClientRect();window.CityMarketFX.fireConfetti(r.left+r.width/2,r.top);}">
                    <input type="email" required placeholder="Enter your email" class="flex-1 px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-ink-500 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-400/30 transition-all">
                    <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white font-bold px-6 py-3 rounded-xl text-sm transition-all whitespace-nowrap btn-shine sweep-shine magnetic-btn ripple relative overflow-hidden">Subscribe</button>
                </form>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
    function heroCarousel() {
        return {
            current: 0,
            slides: [
                {
                    badge: 'Mega Sale', title: 'Up to 70% OFF', subtitle: 'Electronics, Fashion, Home & more. New deals every hour!',
                    cta: 'Shop Now', image: 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1200&h=600&fit=crop',
                    overlay: 'linear-gradient(90deg, rgba(220,38,38,0.85) 0%, rgba(220,38,38,0.45) 50%, transparent 100%)', link: '{{ route("shop") }}'
                },
                {
                    badge: 'Premium Picks', title: 'Beauty & Fragrance', subtitle: 'Discover premium beauty brands at unbeatable prices.',
                    cta: 'Explore Beauty', image: 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=1200&h=600&fit=crop',
                    overlay: 'linear-gradient(90deg, rgba(124,58,237,0.85) 0%, rgba(124,58,237,0.45) 50%, transparent 100%)', link: '{{ route("category", "beauty") }}'
                },
                {
                    badge: 'New Arrivals', title: 'Fashion Forward', subtitle: 'Latest trends in apparel, footwear & accessories.',
                    cta: 'Shop Fashion', image: 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1200&h=600&fit=crop',
                    overlay: 'linear-gradient(90deg, rgba(8,145,178,0.85) 0%, rgba(8,145,178,0.45) 50%, transparent 100%)', link: '{{ route("category", "fashion") }}'
                },
                {
                    badge: 'Smart Living', title: 'Home & Kitchen', subtitle: 'Transform your home with smart essentials.',
                    cta: 'Shop Home', image: 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=1200&h=600&fit=crop',
                    overlay: 'linear-gradient(90deg, rgba(194,65,12,0.85) 0%, rgba(194,65,12,0.45) 50%, transparent 100%)', link: '{{ route("category", "home-kitchen") }}'
                },
            ],
            init() {
                setInterval(() => this.next(), 5000);
            },
            next() { this.current = (this.current + 1) % this.slides.length; },
            prev() { this.current = (this.current - 1 + this.slides.length) % this.slides.length; },
        };
    }
    </script>
    @endpush
</x-layouts.storefront>
