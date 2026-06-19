<x-layouts.storefront title="My Wishlist — CityMarket">

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My Wishlist</h1>
        @if($products->isNotEmpty())
            <span class="text-sm text-gray-500">{{ $products->total() }} item(s)</span>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-50 text-green-700 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    @if($products->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
            <p class="text-gray-400 mb-4">Your wishlist is empty.</p>
            <a href="{{ route('shop') }}" class="inline-flex items-center px-6 py-3 bg-rose-600 text-white rounded-xl font-medium hover:bg-rose-700">Browse Products</a>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($products as $product)
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden group">
                    <a href="{{ route('product', $product->slug) }}">
                        @if($product->images->isNotEmpty())
                            <img src="{{ asset('storage/' . ($product->images->firstWhere('is_primary', true)?->image_path ?? $product->images->first()->image_path)) }}" alt="{{ $product->name }}" class="w-full h-44 object-cover">
                        @else
                            <div class="w-full h-44 bg-gray-100 flex items-center justify-center text-gray-300 text-sm">No image</div>
                        @endif
                    </a>
                    <div class="p-3">
                        <a href="{{ route('product', $product->slug) }}" class="block">
                            <h3 class="font-medium text-gray-900 text-sm truncate">{{ $product->name }}</h3>
                        </a>
                        <div class="flex items-center justify-between mt-2">
                            <div>
                                @if($product->sale_price)
                                    <span class="font-bold text-rose-600">₹{{ number_format($product->sale_price, 0) }}</span>
                                    <span class="text-xs text-gray-400 line-through ml-1">₹{{ number_format($product->price, 0) }}</span>
                                @else
                                    <span class="font-bold text-gray-900">₹{{ number_format($product->price, 0) }}</span>
                                @endif
                            </div>
                        </div>
                        <form action="{{ route('wishlist.remove') }}" method="POST" class="mt-2">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="text-xs text-gray-400 hover:text-red-500">✕ Remove</button>
                        </form>
                    </div>
                    <div class="px-3 pb-3">
                        <form action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="w-full px-3 py-2 bg-rose-600 text-white rounded-xl text-xs font-medium hover:bg-rose-700">Add to Cart</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">{{ $products->links() }}</div>
    @endif
</div>

</x-layouts.storefront>
