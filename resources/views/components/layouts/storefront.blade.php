<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'CityMarket — Shop Smarter' }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛍️</text></svg>">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
</head>

<body class="min-h-screen bg-ink-50">
    <!-- Top bar -->
    <div class="bg-ink-900 text-ink-100 text-xs">
        <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="hidden sm:flex items-center gap-1">📍 Deliver to: <strong>Mumbai 400001</strong></span>
                <a href="{{ route('assistant') }}" class="flex items-center gap-1 text-brand-300 hover:text-brand-200 font-medium">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6c0 1.886.876 3.568 2.244 4.65L6 14v2a1 1 0 001 1h6a1 1 0 001-1v-2l-.244-1.35A5.978 5.978 0 0016 8a6 6 0 00-6-6z"/></svg>
                    AI Assistant
                </a>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('shop') }}" class="hover:text-white">All Products</a>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin Panel</a>
                    @endif
                    <a href="{{ route('logout') }}" class="hover:text-white" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-white">Login</a>
                    <a href="{{ route('register') }}" class="hover:text-white">Sign Up</a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Main header -->
    <header class="bg-white shadow-sm sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-4">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                <span class="text-2xl">🛍️</span>
                <span class="text-xl font-bold text-ink-900 hidden sm:block">City<span class="text-brand-600">Market</span></span>
            </a>

            <!-- Search bar -->
            <form action="{{ route('shop') }}" method="GET" class="flex-1 max-w-2xl">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search for products, brands and more..."
                        class="w-full pl-4 pr-10 py-2.5 rounded-lg border border-ink-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none text-sm">
                    <button type="submit" class="absolute right-1 top-1 bottom-1 px-3 bg-brand-600 text-white rounded-md hover:bg-brand-700 flex items-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </div>
            </form>

            <!-- Actions -->
            <div class="flex items-center gap-1">
                @auth
                    <a href="{{ route('account') }}" class="p-2.5 text-ink-600 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors" title="My Account">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </a>
                    <a href="{{ route('wishlist') }}" class="p-2.5 text-ink-600 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors" title="Wishlist">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </a>
                @endauth
                <a href="{{ route('cart') }}" class="relative p-2.5 text-ink-600 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors" title="Cart">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span id="cart-count-badge" class="absolute -top-1 -right-1 bg-brand-600 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center"></span>
                </a>
            </div>
        </div>

        <!-- Category nav -->
        <nav class="border-t border-ink-100">
            <div class="max-w-7xl mx-auto px-4 flex items-center gap-1 overflow-x-auto no-scrollbar text-sm">
                <a href="{{ route('home') }}" class="px-3 py-2 text-ink-600 hover:text-brand-600 whitespace-nowrap font-medium">Home</a>
                @foreach(\App\Models\Category::where('is_active', true)->whereNull('parent_id')->orderBy('position')->limit(8)->get() as $cat)
                    <a href="{{ route('category', $cat->slug) }}" class="px-3 py-2 text-ink-600 hover:text-brand-600 whitespace-nowrap">{{ $cat->icon }} {{ $cat->name }}</a>
                @endforeach
            </div>
        </nav>
    </header>

    <!-- Flash messages -->
    @if(session('success') || session('error'))
        <div class="max-w-7xl mx-auto px-4 mt-3">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3 animate-fade-in">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg px-4 py-3 animate-fade-in">{{ session('error') }}</div>
            @endif
        </div>
    @endif

    <!-- Main content -->
    <main class="max-w-7xl mx-auto px-4 py-6">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-ink-900 text-ink-300 mt-12">
        <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-2 md:grid-cols-4 gap-8">
            <div class="col-span-2 md:col-span-1">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-2xl">🛍️</span>
                    <span class="text-lg font-bold text-white">City<span class="text-brand-400">Market</span></span>
                </div>
                <p class="text-sm text-ink-400">Your one-stop marketplace for everything. Powered by AI for smarter shopping.</p>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3 text-sm">Shop</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('shop') }}" class="hover:text-white">All Products</a></li>
                    <li><a href="{{ route('home') }}" class="hover:text-white">Flash Deals</a></li>
                    <li><a href="{{ route('assistant') }}" class="hover:text-white">AI Assistant</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3 text-sm">Account</h4>
                <ul class="space-y-2 text-sm">
                    @auth
                        <li><a href="{{ route('account') }}" class="hover:text-white">My Account</a></li>
                        <li><a href="{{ route('account.orders') }}" class="hover:text-white">My Orders</a></li>
                        <li><a href="{{ route('wishlist') }}" class="hover:text-white">Wishlist</a></li>
                        <li><a href="{{ route('support') }}" class="hover:text-white">Support</a></li>
                        <li><a href="{{ route('logout') }}" class="hover:text-white" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="hover:text-white">Login</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white">Register</a></li>
                    @endauth
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3 text-sm">Help</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('assistant') }}" class="hover:text-white">AI Support</a></li>
                    <li><span class="text-ink-500">Returns & Refunds</span></li>
                    <li><span class="text-ink-500">Track Order</span></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-ink-800 py-4 text-center text-xs text-ink-500">
            © {{ date('Y') }} CityMarket. Built with Laravel + AI. Demo platform.
        </div>
    </footer>
</body>

</html>
