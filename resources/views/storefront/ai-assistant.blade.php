<x-layouts.storefront title="AI Shopping Assistant — CityMarket">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-600 to-brand-600 rounded-2xl text-3xl mb-3 shadow-lg">🤖</div>
            <h1 class="text-2xl font-bold text-ink-900">AI Shopping Assistant</h1>
            <p class="text-sm text-ink-500 mt-1">Tell me what you're looking for in plain English — I'll find the perfect products for you.</p>
        </div>

        <!-- Search form -->
        <form action="{{ route('assistant') }}" method="POST" class="mb-8">
            @csrf
            <div class="relative">
                <input type="text" name="query" value="{{ $query ?? '' }}"
                    placeholder="e.g., 'best running shoes under 2000' or 'wireless headphones on sale'"
                    class="w-full pl-5 pr-32 py-4 rounded-2xl border-2 border-ink-200 focus:border-brand-500 outline-none text-base shadow-sm"
                    required>
                <button type="submit" class="absolute right-2 top-2 bottom-2 px-6 bg-gradient-to-r from-purple-600 to-brand-600 text-white font-semibold rounded-xl hover:opacity-90 transition-opacity flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6c0 1.886.876 3.568 2.244 4.65L6 14v2a1 1 0 001 1h6a1 1 0 001-1v-2l-.244-1.35A5.978 5.978 0 0016 8a6 6 0 00-6-6z"/></svg>
                    Ask AI
                </button>
            </div>

            <!-- Example chips -->
            <div class="flex flex-wrap gap-2 mt-3 justify-center">
                @foreach(['Best shoes under 1000', 'Headphones on sale', 'Cheapest electronics', 'Top rated beauty products'] as $example)
                    <button type="button" onclick="this.form.query.value='{{ $example }}'; this.form.submit()"
                        class="text-xs bg-ink-100 hover:bg-brand-100 text-ink-700 hover:text-brand-700 px-3 py-1.5 rounded-full transition-colors">
                        {{ $example }}
                    </button>
                @endforeach
            </div>
        </form>

        <!-- Results -->
        @if(isset($result))
            @php
                $interp = $result['interpretation'] ?? [];
                $products = $result['products'] ?? [];
                $aiInterpreted = $interp['ai_interpreted'] ?? false;
            @endphp

            <!-- AI Interpretation card -->
            <div class="bg-gradient-to-br from-purple-50 to-brand-50 border border-purple-200 rounded-2xl p-5 mb-6">
                <div class="flex items-start gap-3">
                    <div class="text-2xl shrink-0">🤖</div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-ink-800">AI understood:</span>
                            @if($aiInterpreted)
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">AI-powered</span>
                            @else
                                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">Keyword fallback</span>
                            @endif
                        </div>
                        <p class="text-sm text-ink-700 mb-2">"I searched for <strong>{{ $interp['keywords'] ?? 'all products' }}</strong>"</p>
                        <div class="flex flex-wrap gap-2">
                            @if(!empty($interp['category']))<span class="text-xs bg-white text-ink-700 px-2 py-1 rounded-md">📂 {{ $interp['category'] }}</span>@endif
                            @if(!empty($interp['price_max']))<span class="text-xs bg-white text-ink-700 px-2 py-1 rounded-md">💰 Under ₹{{ number_format($interp['price_max']) }}</span>@endif
                            @if(!empty($interp['price_min']))<span class="text-xs bg-white text-ink-700 px-2 py-1 rounded-md">💰 Over ₹{{ number_format($interp['price_min']) }}</span>@endif
                            @if(!empty($interp['min_rating']))<span class="text-xs bg-white text-ink-700 px-2 py-1 rounded-md">⭐ {{ $interp['min_rating'] }}+ rating</span>@endif
                            @if(!empty($interp['on_sale']))<span class="text-xs bg-white text-ink-700 px-2 py-1 rounded-md">🏷️ On Sale</span>@endif
                            @if(!empty($interp['sort']))<span class="text-xs bg-white text-ink-700 px-2 py-1 rounded-md">↕️ {{ str_replace('_', ' ', $interp['sort']) }}</span>@endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products -->
            @if(count($products) > 0)
                <div class="mb-3">
                    <h2 class="text-lg font-bold text-ink-900">Found {{ count($products) }} product{{ count($products) > 1 ? 's' : '' }} for you</h2>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($products as $productData)
                        @php $product = \App\Models\Product::find($productData['id']); @endphp
                        @if($product)
                            <x-product-card :product="$product" />
                        @endif
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl border border-ink-100 p-8 text-center">
                    <div class="text-4xl mb-2">😕</div>
                    <h3 class="font-semibold text-ink-800 mb-1">No products matched your request</h3>
                    <p class="text-sm text-ink-500">Try rephrasing or browsing our <a href="{{ route('shop') }}" class="text-brand-600 hover:underline">full catalog</a>.</p>
                </div>
            @endif
        @else
            <!-- Welcome state -->
            <div class="grid sm:grid-cols-3 gap-4">
                @foreach([
                    ['🎯', 'Natural Language', 'Describe what you want — no filters needed.'],
                    ['⚡', 'Instant Results', 'AI finds and ranks the best products instantly.'],
                    ['🏷️', 'Smart Deals', 'It knows when to show you sale items.'],
                ] as [$icon, $title, $desc])
                    <div class="bg-white rounded-xl border border-ink-100 p-5 text-center">
                        <div class="text-3xl mb-2">{{ $icon }}</div>
                        <h3 class="font-semibold text-ink-800 mb-1">{{ $title }}</h3>
                        <p class="text-xs text-ink-500">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.storefront>
