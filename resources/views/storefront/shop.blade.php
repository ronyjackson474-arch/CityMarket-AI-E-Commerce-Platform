<x-layouts.storefront title="{{ isset($currentCategory) ? $currentCategory->name . ' — CityMarket' : 'Shop — CityMarket' }}">
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Filters sidebar -->
        <aside class="lg:w-64 shrink-0">
            <div class="bg-white rounded-xl border border-ink-100 p-5 lg:sticky lg:top-32">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-ink-900">Filters</h3>
                    <a href="{{ route('shop') }}" class="text-xs text-brand-600 hover:underline">Clear all</a>
                </div>

                <form method="GET" action="{{ route('shop') }}" class="space-y-5">
                    @foreach(['category','brand','price_min','price_max','min_rating','sort','on_sale'] as $preserve)
                        @if(request($preserve) && !in_array($preserve, ['category','brand','price_min','price_max','min_rating']))
                            <input type="hidden" name="{{ $preserve }}" value="{{ request($preserve) }}">
                        @endif
                    @endforeach
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    <!-- Categories -->
                    <div>
                        <h4 class="text-sm font-semibold text-ink-700 mb-2">Category</h4>
                        <div class="space-y-1.5 max-h-48 overflow-y-auto">
                            @foreach($categories as $cat)
                                <label class="flex items-center gap-2 text-sm text-ink-600 cursor-pointer hover:text-ink-900">
                                    <input type="radio" name="category" value="{{ $cat->id }}" {{ (string)request('category') === (string)$cat->id ? 'checked' : '' }}
                                        class="rounded text-brand-600 focus:ring-brand-500" onchange="this.form.submit()">
                                    <span>{{ $cat->icon }} {{ $cat->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Price range -->
                    <div>
                        <h4 class="text-sm font-semibold text-ink-700 mb-2">Price Range</h4>
                        <div class="flex items-center gap-2">
                            <input type="number" name="price_min" value="{{ request('price_min') }}" placeholder="Min" min="0"
                                class="w-full px-2 py-1.5 text-sm border border-ink-200 rounded-md focus:border-brand-500 outline-none">
                            <span class="text-ink-400">-</span>
                            <input type="number" name="price_max" value="{{ request('price_max') }}" placeholder="Max" min="0"
                                class="w-full px-2 py-1.5 text-sm border border-ink-200 rounded-md focus:border-brand-500 outline-none">
                        </div>
                    </div>

                    <!-- Brands -->
                    <div>
                        <h4 class="text-sm font-semibold text-ink-700 mb-2">Brand</h4>
                        <div class="space-y-1.5 max-h-40 overflow-y-auto">
                            @foreach($brands as $brand)
                                <label class="flex items-center gap-2 text-sm text-ink-600 cursor-pointer hover:text-ink-900">
                                    <input type="radio" name="brand" value="{{ $brand->id }}" {{ (string)request('brand') === (string)$brand->id ? 'checked' : '' }}
                                        class="rounded text-brand-600 focus:ring-brand-500" onchange="this.form.submit()">
                                    <span>{{ $brand->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Rating -->
                    <div>
                        <h4 class="text-sm font-semibold text-ink-700 mb-2">Min Rating</h4>
                        <div class="space-y-1.5">
                            @foreach([4, 3, 2] as $r)
                                <label class="flex items-center gap-2 text-sm text-ink-600 cursor-pointer hover:text-ink-900">
                                    <input type="radio" name="min_rating" value="{{ $r }}" {{ (string)request('min_rating') === (string)$r ? 'checked' : '' }}
                                        class="rounded text-brand-600 focus:ring-brand-500" onchange="this.form.submit()">
                                    <span class="flex items-center text-amber-400">
                                        @for($i = 0; $i < $r; $i++)★@endfor <span class="text-ink-500 ml-1">& up</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- On sale -->
                    <label class="flex items-center gap-2 text-sm text-ink-600 cursor-pointer hover:text-ink-900">
                        <input type="checkbox" name="on_sale" value="1" {{ request('on_sale') ? 'checked' : '' }}
                            class="rounded text-brand-600 focus:ring-brand-500" onchange="this.form.submit()">
                        <span>🏷️ On Sale Only</span>
                    </label>

                    <button type="submit" class="w-full bg-brand-600 text-white text-sm font-medium py-2 rounded-lg hover:bg-brand-700 transition-colors">Apply Filters</button>
                </form>
            </div>
        </aside>

        <!-- Products grid -->
        <div class="flex-1" x-data="{ view: localStorage.getItem('shopView') || 'grid' }">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                <div>
                    <h1 class="text-xl font-bold text-ink-900">
                        {{ isset($currentCategory) ? $currentCategory->name : 'All Products' }}
                        @if(request('search'))
                            <span class="text-ink-500 font-normal text-sm">— results for "{{ request('search') }}"</span>
                        @endif
                    </h1>
                    <p class="text-sm text-ink-500">{{ $products->total() }} products found</p>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Grid/List toggle -->
                    <div class="hidden sm:flex items-center border border-ink-200 rounded-lg overflow-hidden">
                        <button @click="view='grid'; localStorage.setItem('shopView','grid')" :class="view==='grid' ? 'bg-brand-600 text-white' : 'text-ink-500 hover:bg-ink-50'" class="p-2 transition-colors" title="Grid view">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zm0 8a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zm8-8a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2zm0 8a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2z"/></svg>
                        </button>
                        <button @click="view='list'; localStorage.setItem('shopView','list')" :class="view==='list' ? 'bg-brand-600 text-white' : 'text-ink-500 hover:bg-ink-50'" class="p-2 transition-colors" title="List view">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 6a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 6a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"/></svg>
                        </button>
                    </div>
                    <form method="GET" action="{{ route('shop') }}" class="flex items-center gap-2">
                        @php
                            $preserveKeys = ['category', 'brand', 'price_min', 'price_max', 'min_rating', 'on_sale', 'search'];
                        @endphp
                        @foreach($preserveKeys as $pk)
                            @if(request($pk))<input type="hidden" name="{{ $pk }}" value="{{ request($pk) }}">@endif
                        @endforeach
                        <select name="sort" onchange="this.form.submit()" class="text-sm border border-ink-200 rounded-lg px-3 py-2 bg-white focus:border-brand-500 outline-none">
                            <option value="">Sort: Popularity</option>
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Top Rated</option>
                            <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest First</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Active filter chips -->
            @php
                $activeFilters = [];
                if(request('category')) $activeFilters['Category'] = $categories->where('id', request('category'))->first()?->name ?? 'Category';
                if(request('brand')) $activeFilters['Brand'] = $brands->where('id', request('brand'))->first()?->name ?? 'Brand';
                if(request('price_min')) $activeFilters['Min Price'] = '₹' . request('price_min');
                if(request('price_max')) $activeFilters['Max Price'] = '₹' . request('price_max');
                if(request('min_rating')) $activeFilters['Rating'] = request('min_rating') . '★+';
                if(request('on_sale')) $activeFilters['On Sale'] = 'Yes';
                if(request('search')) $activeFilters['Search'] = '"' . request('search') . '"';
            @endphp
            @if(!empty($activeFilters))
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <span class="text-xs text-ink-400 font-medium">Active:</span>
                    @foreach($activeFilters as $label => $value)
                        <span class="inline-flex items-center gap-1 bg-brand-50 text-brand-700 text-xs font-medium px-2.5 py-1 rounded-full">
                            {{ $label }}: {{ $value }}
                        </span>
                    @endforeach
                    <a href="{{ route('shop') }}" class="text-xs text-ink-500 hover:text-rose-600 font-medium underline ml-1">Clear all</a>
                </div>
            @endif

            @if($products->isEmpty())
                <div class="bg-white rounded-xl border border-ink-100 p-12 text-center">
                    <div class="text-5xl mb-3">🔍</div>
                    <h3 class="text-lg font-semibold text-ink-800 mb-1">No products found</h3>
                    <p class="text-sm text-ink-500 mb-4">Try adjusting your filters or search terms.</p>
                    <a href="{{ route('assistant') }}" class="inline-flex items-center gap-2 bg-brand-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-brand-700">
                        🤖 Ask AI Assistant
                    </a>
                </div>
            @else
                <!-- Grid view -->
                <div x-show="view==='grid'" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                <!-- List view -->
                <div x-show="view==='list'" x-cloak class="space-y-3">
                    @foreach($products as $product)
                        <a href="{{ route('product', $product->slug) }}" class="flex gap-4 bg-white rounded-xl border border-ink-100 p-3 hover:shadow-md transition-all group">
                            <div class="w-28 h-28 shrink-0 rounded-lg overflow-hidden bg-ink-50 relative">
                                @if($product->primaryImage)
                                    <img src="{{ $product->primaryImage }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @endif
                                @if($product->discount_percent > 0)
                                    <div class="absolute top-1 left-1 bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">-{{ $product->discount_percent }}%</div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold text-ink-900 line-clamp-1 group-hover:text-brand-600">{{ $product->name }}</h3>
                                @if($product->brand)<p class="text-xs text-ink-400">{{ $product->brand->name }}</p>@endif
                                <p class="text-xs text-ink-500 mt-1 line-clamp-2">{{ $product->description }}</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="text-lg font-bold text-brand-600">₹{{ number_format($product->effective_price) }}</span>
                                    @if($product->discount_percent > 0)<span class="text-xs text-ink-400 line-through">₹{{ number_format($product->price) }}</span>@endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.storefront>
