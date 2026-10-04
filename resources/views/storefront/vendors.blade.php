<x-layouts.storefront title="Vendors — CityMarket">
    <div class="max-w-7xl mx-auto">
        <!-- Hero -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-ink-900 via-brand-800 to-brand-600 text-white p-8 mb-8">
            <div class="absolute -top-12 right-10 w-52 h-52 bg-white/10 rounded-full blur-3xl"></div>
            <div class="relative">
                <span class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur px-3 py-1 rounded-full text-xs font-semibold mb-3">🏪 Our Sellers</span>
                <h1 class="text-3xl font-extrabold mb-1">CityMarket Vendors</h1>
                <p class="text-white/80 text-sm">Trusted sellers, verified by us — shop directly from your favourite stores.</p>
            </div>
        </div>

        <!-- Search -->
        <form class="mb-6 flex gap-2 max-w-md" action="{{ route('vendors') }}" method="GET">
            <input type="text" name="q" value="{{ $q }}" placeholder="Search shops..."
                class="flex-1 px-4 py-2.5 border border-ink-200 rounded-xl text-sm focus:border-brand-500 outline-none bg-white">
            <button class="px-5 py-2.5 bg-ink-900 text-white text-sm font-semibold rounded-xl hover:bg-brand-600 transition-colors">Search</button>
        </form>

        @if($vendors->isEmpty())
            <div class="bg-white rounded-2xl border border-ink-100 p-12 text-center">
                <div class="text-4xl mb-3">🏪</div>
                <h2 class="font-bold text-ink-800 mb-1">No vendors found</h2>
                <p class="text-sm text-ink-500">Abhi koi vendor available nahi hai — thodi der mein wapas dekho.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($vendors as $vendor)
                    <a href="{{ route('vendor.shop', $vendor->slug) }}"
                        class="group pc-spot relative bg-white rounded-2xl border border-ink-100 p-5 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div class="flex items-center gap-3 mb-3">
                            @if($vendor->logo_url)
                                <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->shop_name }}" class="w-14 h-14 rounded-xl object-cover border border-ink-100">
                            @else
                                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center text-white text-xl font-extrabold shadow-md">{{ mb_substr($vendor->shop_name, 0, 1) }}</div>
                            @endif
                            <div class="min-w-0">
                                <h3 class="font-bold text-ink-900 truncate group-hover:text-brand-600 transition-colors">{{ $vendor->shop_name }}</h3>
                                <div class="flex items-center gap-1 text-xs">
                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <span class="font-semibold text-ink-700">{{ number_format($vendor->rating, 1) }}</span>
                                    <span class="text-ink-400">· {{ $vendor->products_count }} products</span>
                                </div>
                            </div>
                        </div>
                        @if($vendor->description)
                            <p class="text-xs text-ink-500 line-clamp-2 mb-3">{{ $vendor->description }}</p>
                        @endif
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-brand-600">
                            Visit Shop
                            <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">{{ $vendors->links() }}</div>
        @endif
    </div>
</x-layouts.storefront>
