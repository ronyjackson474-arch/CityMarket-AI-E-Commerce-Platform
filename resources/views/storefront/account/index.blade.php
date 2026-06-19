<x-layouts.storefront title="My Account — CityMarket">

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Sidebar --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center">
                        <span class="text-rose-600 font-bold text-lg">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    </div>
                </div>

                <nav class="space-y-1">
                    <a href="{{ route('account') }}" class="block px-4 py-2.5 rounded-lg bg-rose-50 text-rose-600 font-medium text-sm">Dashboard</a>
                    <a href="{{ route('account.orders') }}" class="block px-4 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 text-sm">My Orders</a>
                    <a href="{{ route('wishlist') }}" class="block px-4 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 text-sm">Wishlist ({{ $wishlistCount }})</a>
                    <a href="{{ route('account.addresses') }}" class="block px-4 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 text-sm">Addresses</a>
                    <a href="{{ route('account.wallet') }}" class="block px-4 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 text-sm">Wallet</a>
                    <a href="{{ route('account.referrals') }}" class="block px-4 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 text-sm">Refer & Earn</a>
                </nav>
            </div>
        </div>

        {{-- Main content --}}
        <div class="lg:col-span-3 space-y-6">
            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl shadow-sm p-5">
                    <div class="text-2xl font-bold text-gray-900">{{ $orders->count() }}</div>
                    <div class="text-sm text-gray-500 mt-1">Total Orders</div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-5">
                    <div class="text-2xl font-bold text-rose-600">₹{{ number_format($wallet->balance ?? 0, 0) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Wallet Balance</div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-5">
                    <div class="text-2xl font-bold text-gray-900">{{ $wishlistCount }}</div>
                    <div class="text-sm text-gray-500 mt-1">Wishlist Items</div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-5">
                    <div class="text-2xl font-bold text-gray-900">{{ $user->reviews()->count() }}</div>
                    <div class="text-sm text-gray-500 mt-1">Reviews Given</div>
                </div>
            </div>

            {{-- Recent Orders --}}
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Recent Orders</h2>
                    <a href="{{ route('account.orders') }}" class="text-sm text-rose-600 hover:text-rose-700">View All →</a>
                </div>

                @if($orders->isEmpty())
                    <p class="text-gray-400 text-sm py-8 text-center">No orders yet. Start shopping!</p>
                @else
                    <div class="space-y-3">
                        @foreach($orders as $order)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                                <div>
                                    <div class="font-medium text-gray-900 text-sm">{{ $order->order_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $order->created_at->format('M d, Y') }} · {{ \Illuminate\Support\Str::title($order->status) }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-gray-900">₹{{ number_format($order->total, 0) }}</div>
                                    <a href="{{ route('account.order-detail', $order->order_number) }}" class="text-xs text-rose-600">Details</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

</x-layouts.storefront>
