<x-layouts.storefront title="My Account — CityMarket">

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Sidebar --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-14 h-14 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white shadow-md">
                        <span class="font-bold text-xl">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-ink-900">{{ $user->name }}</h3>
                        <p class="text-sm text-ink-500">{{ $user->email }}</p>
                    </div>
                </div>

                <nav class="space-y-1">
                    <a href="{{ route('account') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-brand-50 text-brand-600 font-medium text-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM13 10a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('account.orders') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-ink-600 hover:bg-ink-50 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5l-2-1H6a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2v10"/></svg>
                        My Orders
                    </a>
                    <a href="{{ route('wishlist') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-ink-600 hover:bg-ink-50 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        Wishlist ({{ $wishlistCount }})
                    </a>
                    <a href="{{ route('account.addresses') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-ink-600 hover:bg-ink-50 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Addresses
                    </a>
                    <a href="{{ route('account.wallet') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-ink-600 hover:bg-ink-50 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Wallet
                    </a>
                    <a href="{{ route('account.referrals') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-ink-600 hover:bg-ink-50 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Refer & Earn
                    </a>
                </nav>
            </div>
        </div>

        {{-- Main content --}}
        <div class="lg:col-span-3 space-y-6">
            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-ink-100 hover:shadow-md transition-shadow">
                    <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center mb-2">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <div class="text-2xl font-bold text-ink-900">{{ $orders->count() }}</div>
                    <div class="text-sm text-ink-500 mt-0.5">Total Orders</div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-ink-100 hover:shadow-md transition-shadow">
                    <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center mb-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div class="text-2xl font-bold text-green-600">₹{{ number_format($wallet->balance ?? 0, 0) }}</div>
                    <div class="text-sm text-ink-500 mt-0.5">Wallet Balance</div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-ink-100 hover:shadow-md transition-shadow">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center mb-2">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </div>
                    <div class="text-2xl font-bold text-ink-900">{{ $wishlistCount }}</div>
                    <div class="text-sm text-ink-500 mt-0.5">Wishlist Items</div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-ink-100 hover:shadow-md transition-shadow">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center mb-2">
                        <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </div>
                    <div class="text-2xl font-bold text-ink-900">{{ $user->reviews()->count() }}</div>
                    <div class="text-sm text-ink-500 mt-0.5">Reviews Given</div>
                </div>
            </div>

            {{-- Recent Orders --}}
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-ink-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-ink-900">Recent Orders</h2>
                    <a href="{{ route('account.orders') }}" class="text-sm text-brand-600 hover:text-brand-700">View All →</a>
                </div>

                @if($orders->isEmpty())
                    <div class="text-center py-10">
                        <div class="text-5xl mb-3">📦</div>
                        <p class="text-ink-400 text-sm mb-4">No orders yet. Start shopping!</p>
                        <a href="{{ route('shop') }}" class="inline-flex items-center px-5 py-2.5 bg-brand-600 text-white rounded-lg font-medium text-sm hover:bg-brand-700 transition-colors">Start Shopping</a>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($orders as $order)
                            <div class="flex items-center justify-between p-4 rounded-xl bg-ink-50 hover:bg-ink-100 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center">
                                        <span class="text-lg">📦</span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-ink-900 text-sm">{{ $order->order_number }}</div>
                                        <div class="text-xs text-ink-500">{{ $order->created_at->format('M d, Y') }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @switch($order->status)
                                            @case('delivered') bg-green-100 text-green-700 @break
                                            @case('cancelled') bg-red-100 text-red-700 @break
                                            @case('shipped') bg-blue-100 text-blue-700 @break
                                            @default bg-amber-100 text-amber-700
                                        @endswitch">
                                        {{ \Illuminate\Support\Str::title($order->status) }}
                                    </span>
                                    <div class="text-right">
                                        <div class="font-semibold text-ink-900">₹{{ number_format($order->total, 0) }}</div>
                                        <a href="{{ route('account.order-detail', $order->order_number) }}" class="text-xs text-brand-600 hover:text-brand-700">Details →</a>
                                    </div>
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
