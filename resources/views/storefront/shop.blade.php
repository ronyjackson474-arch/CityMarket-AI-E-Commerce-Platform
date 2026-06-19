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
        <div class="flex-1">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-xl font-bold text-ink-900">
                        {{ isset($currentCategory) ? $currentCategory->name : 'All Products' }}
                        @if(request('search'))
                            <span class="text-ink-500 font-normal text-sm">— results for "{{ request('search') }}"</span>
                        @endif
                    </h1>
                    <p class="text-sm text-ink-500">{{ $products->total() }} products found</p>
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
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.storefront>
