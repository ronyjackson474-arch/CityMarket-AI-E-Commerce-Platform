<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'CityMarket — Premium Shopping' }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛍️</text></svg>">
    {{-- PWA --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="CityMarket">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
    <script>
        // PWA service worker + install prompt
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
        window.__cmDeferredPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.__cmDeferredPrompt = e;
            document.dispatchEvent(new CustomEvent('cm:pwa-installable'));
        });
        window.cmInstallPwa = function () {
            if (!window.__cmDeferredPrompt) return;
            window.__cmDeferredPrompt.prompt();
            window.__cmDeferredPrompt.userChoice.finally(() => { window.__cmDeferredPrompt = null; });
        };
        // Header Install App icon
        (function () {
            var btn = document.getElementById('cm-install-app');
            if (!btn) return;
            var standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
            if (standalone || localStorage.getItem('cm-app-installed') === '1') return;
            var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            var show = function () { btn.classList.remove('hidden'); btn.classList.add('flex'); };
            btn.addEventListener('click', function () {
                if (window.__cmDeferredPrompt) {
                    window.cmInstallPwa();
                    return;
                }
                if (isIOS) {
                    // Guide iOS users to Add to Home Screen
                    var hint = document.getElementById('cm-ios-install-hint');
                    if (hint) { hint.classList.remove('opacity-0', 'translate-y-4'); localStorage.removeItem('cm-ios-hint-dismissed'); return; }
                    if (btn.dispatchEvent(new CustomEvent('cm:show-ios-hint', { bubbles: true }))) return;
                    alert('To install the CityMarket app on iPhone:\n\n1. Tap the Share button in Safari\'s toolbar\n2. Choose "Add to Home Screen"\n3. Tap Add');
                }
            });
            // Chrome/Android: show as soon as install prompt is available
            window.addEventListener('beforeinstallprompt', function () { show(); });
            // Safari desktop / fallback: show on mobile Safari too
            if (isIOS || (/Safari/.test(navigator.userAgent) && !/Chrome/.test(navigator.userAgent))) show();
            window.addEventListener('appinstalled', function () {
                btn.classList.add('hidden'); btn.classList.remove('flex');
                localStorage.setItem('cm-app-installed', '1');
            });
        })();
    </script>
</head>

<body class="min-h-screen">
    <!-- Ambient animated background blobs -->
    <div class="ambient-blobs" aria-hidden="true"></div>

    <!-- Scroll Progress Bar -->
    <div id="scroll-progress" class="scroll-progress"></div>

    <!-- Cursor Glow -->
    <div id="cursor-glow" class="cursor-glow"></div>

    <!-- Announcement Marquee Bar -->
    <div class="bg-ink-900 text-white text-xs overflow-hidden">
        <div class="flex whitespace-nowrap marquee py-2">
            <span class="mx-8">✨ EXTRA 10% OFF on first order · Use code WELCOME10</span>
            <span class="mx-8">🚚 FREE SHIPPING on orders above ₹500</span>
            <span class="mx-8">🎁 Shop & Earn · 1% Cashback on every purchase</span>
            <span class="mx-8">⚡ Flash Sale LIVE · Up to 70% OFF</span>
            <span class="mx-8">✨ EXTRA 10% OFF on first order · Use code WELCOME10</span>
            <span class="mx-8">🚚 FREE SHIPPING on orders above ₹500</span>
            <span class="mx-8">🎁 Shop & Earn · 1% Cashback on every purchase</span>
            <span class="mx-8">⚡ Flash Sale LIVE · Up to 70% OFF</span>
        </div>
    </div>

    <!-- Main header -->
    <header id="main-header" class="bg-white shadow-sm sticky top-0 z-40 border-b border-ink-100 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center gap-4 py-3">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                    <span class="text-2xl">🛍️</span>
                    <span class="text-xl font-bold tracking-tight text-ink-900 hidden sm:block">City<span class="text-brand-600">Market</span></span>
                </a>

                <!-- Search bar -->
                <form action="{{ route('shop') }}" method="GET" class="flex-1 max-w-2xl">
                    <div class="relative group">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search for products, brands and more..."
                            class="w-full pl-4 pr-12 py-2.5 rounded-lg bg-ink-50 border border-ink-200 focus:bg-white focus:border-ink-900 focus:ring-1 focus:ring-ink-900 outline-none text-sm transition-all">
                        <button type="submit" class="absolute right-1 top-1 bottom-1 px-3 bg-ink-900 text-white rounded-md hover:bg-ink-800 flex items-center transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>
                    </div>
                </form>

                <!-- Actions -->
                <div class="flex items-center gap-1">
                    <button id="cm-install-app" type="button" class="hidden flex-col items-center px-2 py-1 text-brand-600 hover:text-brand-700 transition-colors relative" title="Install App">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 3v13m0 0l-4-4m4 4l4-4"/></svg>
                        <span class="text-[10px] font-semibold hidden lg:block">Install</span>
                        <span class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-brand-500 rounded-full animate-pulse"></span>
                    </button>
                    @auth
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                            <button type="button" @click="open = !open" class="flex flex-col items-center px-2 py-1 text-ink-700 hover:text-brand-600 transition-colors" title="My Account">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span class="text-[10px] font-medium hidden lg:block">Account</span>
                            </button>
                            <div x-show="open" x-transition.opacity.duration.150ms
                                class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-ink-100 py-2 z-50">
                                <div class="px-4 py-2 border-b border-ink-100">
                                    <div class="text-sm font-semibold text-ink-900 truncate">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-ink-400 truncate">{{ auth()->user()->email }}</div>
                                </div>
                                <a href="{{ route('account') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-ink-700 hover:bg-brand-50 hover:text-brand-700 transition-colors">👤 My Account</a>
                                <a href="{{ route('account.orders') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-ink-700 hover:bg-brand-50 hover:text-brand-700 transition-colors">📦 My Orders</a>
                                <a href="{{ route('wishlist') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-ink-700 hover:bg-brand-50 hover:text-brand-700 transition-colors">❤️ Wishlist</a>
                                <a href="{{ route('account.wallet') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-ink-700 hover:bg-brand-50 hover:text-brand-700 transition-colors">💰 Wallet</a>
                                <div class="border-t border-ink-100 mt-1 pt-1">
                                    <button type="button" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">🚪 Logout</button>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('wishlist') }}" class="flex flex-col items-center px-2 py-1 text-ink-700 hover:text-brand-600 transition-colors relative" title="Wishlist">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            <span class="text-[10px] font-medium hidden lg:block">Wishlist</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="flex flex-col items-center px-2 py-1 text-ink-700 hover:text-brand-600 transition-colors" title="Login">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="text-[10px] font-medium hidden lg:block">Login</span>
                        </a>
                    @endauth
                    <a href="{{ route('cart') }}" class="flex flex-col items-center px-2 py-1 text-ink-700 hover:text-brand-600 transition-colors relative" title="Cart">
                        <div class="relative">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span id="cart-count-badge" class="absolute -top-1.5 -right-1.5 bg-brand-600 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center" style="display:none"></span>
                        </div>
                        <span class="text-[10px] font-medium hidden lg:block">Cart</span>
                    </a>
                </div>
            </div>

            <!-- Category nav bar -->
            <nav class="flex items-center gap-0 overflow-x-auto no-scrollbar text-sm border-t border-ink-100">
                <a href="{{ route('home') }}" class="px-3.5 py-2.5 text-ink-700 hover:text-brand-600 whitespace-nowrap font-medium hover-underline transition-colors">Home</a>
                <a href="{{ route('vendors') }}" class="px-3.5 py-2.5 text-ink-700 hover:text-brand-600 whitespace-nowrap font-medium hover-underline transition-colors">🏪 Vendors</a>
                @foreach(\App\Models\Category::where('is_active', true)->whereNull('parent_id')->orderBy('position')->limit(8)->get(['id','name','slug','icon']) as $cat)
                    <a href="{{ route('category', $cat->slug) }}" class="px-3.5 py-2.5 text-ink-700 hover:text-brand-600 whitespace-nowrap font-medium hover-underline transition-colors">{{ $cat->icon }} {{ $cat->name }}</a>
                @endforeach
                <a href="{{ route('assistant') }}" class="px-3.5 py-2.5 text-brand-600 whitespace-nowrap font-bold hover-underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/></svg>
                    AI Search
                </a>
            </nav>
        </div>
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

    <!-- Toast container -->
    <div id="toast-container" class="fixed bottom-6 right-6 z-[100] space-y-2 pointer-events-none"></div>

    <!-- Footer -->
    <footer class="bg-ink-900 text-ink-300 mt-12">
        <!-- Top: trust + newsletter -->
        <div class="border-b border-ink-800">
            <div class="max-w-7xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-brand-600/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    </div>
                    <div><div class="text-white font-semibold text-sm">Free Delivery</div><div class="text-xs text-ink-400">On orders above ₹500</div></div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-600/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div><div class="text-white font-semibold text-sm">Secure Payment</div><div class="text-xs text-ink-400">100% protected</div></div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-600/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div><div class="text-white font-semibold text-sm">Easy Returns</div><div class="text-xs text-ink-400">7-day return policy</div></div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-600/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div><div class="text-white font-semibold text-sm">24/7 Support</div><div class="text-xs text-ink-400">Always here to help</div></div>
                </div>
            </div>
        </div>

        <!-- Links grid -->
        <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-2 md:grid-cols-5 gap-8">
            <div class="col-span-2 md:col-span-1">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-2xl">🛍️</span>
                    <span class="text-lg font-bold text-white">City<span class="text-brand-400">Market</span></span>
                </div>
                <p class="text-sm text-ink-400">Your premium shopping destination. Powered by AI for smarter shopping.</p>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3 text-sm uppercase tracking-wider">Shop</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('shop') }}" class="hover:text-white transition-colors">All Products</a></li>
                    <li><a href="{{ route('shop', ['on_sale' => 1]) }}" class="hover:text-white transition-colors">Flash Deals</a></li>
                    <li><a href="{{ route('assistant') }}" class="hover:text-white transition-colors">AI Assistant</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3 text-sm uppercase tracking-wider">Account</h4>
                <ul class="space-y-2 text-sm">
                    @auth
                        <li><a href="{{ route('account') }}" class="hover:text-white transition-colors">My Account</a></li>
                        <li><a href="{{ route('account.orders') }}" class="hover:text-white transition-colors">My Orders</a></li>
                        <li><a href="{{ route('wishlist') }}" class="hover:text-white transition-colors">Wishlist</a></li>
                        <li><a href="{{ route('support') }}" class="hover:text-white transition-colors">Support</a></li>
                        <li><a href="{{ route('logout') }}" class="hover:text-white transition-colors" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Login</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Register</a></li>
                    @endauth
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3 text-sm uppercase tracking-wider">Help</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('assistant') }}" class="hover:text-white transition-colors">AI Support</a></li>
                    <li><a href="{{ route('shop') }}" class="hover:text-white transition-colors">Returns & Refunds</a></li>
                    @auth
                        <li><a href="{{ route('account.orders') }}" class="hover:text-white transition-colors">Track Order</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Track Order</a></li>
                    @endauth
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3 text-sm uppercase tracking-wider">Newsletter</h4>
                <p class="text-sm text-ink-400 mb-2">Get exclusive deals & updates</p>
                <form class="flex gap-2" onsubmit="event.preventDefault(); this.reset(); showToast('Subscribed successfully!', 'success');">
                    <input type="email" required placeholder="Email" class="flex-1 bg-ink-800 text-white text-sm rounded-lg border border-ink-700 px-3 py-2 placeholder-ink-500 focus:border-brand-500 focus:outline-none">
                    <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors">→</button>
                </form>
            </div>
        </div>
        <div class="border-t border-ink-800 py-4">
            <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-ink-500">
                <span>© {{ date('Y') }} CityMarket. All rights reserved.</span>
                <span class="flex items-center gap-3">
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin</a>
                    @endif
                    <span>Built with Laravel + AI</span>
                </span>
            </div>
        </div>
    </footer>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>

    <!-- Global JS: toast, quick add to cart, wishlist toggle, cart count -->
    <script>
    (function() {
        window.showToast = function(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const colors = { success: 'bg-green-600', error: 'bg-brand-600', info: 'bg-ink-800' };
            const icons = { success: '✓', error: '✕', info: 'ℹ' };
            toast.className = `flex items-center gap-2 ${colors[type] || colors.info} text-white text-sm font-medium px-4 py-3 rounded-xl shadow-lg pointer-events-auto transform transition-all duration-300 translate-y-4 opacity-0`;
            toast.innerHTML = `<span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-xs">${icons[type]}</span>${message}`;
            container.appendChild(toast);
            requestAnimationFrame(() => { toast.classList.remove('translate-y-4', 'opacity-0'); });
            setTimeout(() => { toast.classList.add('translate-y-4', 'opacity-0'); setTimeout(() => toast.remove(), 300); }, 3000);
        };

        window.updateCartBadge = function(count) {
            const badge = document.getElementById('cart-count-badge');
            if (!badge) return;
            if (count > 0) { badge.textContent = count > 99 ? '99+' : count; badge.style.display = 'flex'; }
            else { badge.style.display = 'none'; }
        };

        window.quickAddToCart = function(productId, event) {
            if (event) { event.preventDefault(); event.stopPropagation(); }
            const btn = (event && event.target instanceof Element) ? event.target.closest('button') : null;
            if (btn) { btn.disabled = true; btn.style.opacity = '0.5'; }
            fetch('{{ route("cart.add") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: JSON.stringify({ product_id: productId, quantity: 1 })
            }).then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            }).then(data => {
                const count = data.cart_count || data.data?.item_count || 1;
                updateCartBadge(count);
                showToast('Added to cart!', 'success');
                // Fire confetti from button position
                if (window.CityMarketFX && btn) {
                    const rect = btn.getBoundingClientRect();
                    window.CityMarketFX.fireConfetti(rect.left + rect.width / 2, rect.top + rect.height / 2);
                }
                if (btn) { btn.disabled = false; btn.style.opacity = ''; }
            }).catch(() => {
                showToast('Failed to add. Try again.', 'error');
                if (btn) { btn.disabled = false; btn.style.opacity = ''; }
            });
        };

        window.toggleWishlist = function(productId, btn) {
            const icon = document.querySelector('.wishlist-icon-' + productId);
            fetch('{{ route("wishlist.toggle") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            }).then(r => {
                if (r.status === 401) { showToast('Please login to use wishlist', 'info'); setTimeout(() => window.location.href = '{{ route("login") }}', 900); return null; }
                return r.json();
            }).then(data => {
                if (!data) return;
                const isWishlisted = data.action === 'added' || data.wishlisted === true;
                if (icon) {
                    if (isWishlisted) { icon.setAttribute('fill', '#dc2626'); icon.classList.add('text-brand-500'); }
                    else { icon.setAttribute('fill', 'none'); icon.classList.remove('text-brand-500'); }
                }
                showToast(isWishlisted ? 'Added to wishlist' : 'Removed from wishlist', isWishlisted ? 'success' : 'info');
            }).catch(() => showToast('Action failed. Try again.', 'error'));
        };

        fetch('{{ route("cart.count") }}', { headers: { 'Accept': 'application/json' }})
            .then(r => r.json()).then(data => {
                const count = data.count ?? data.data?.item_count ?? 0;
                if (count > 0) updateCartBadge(count);
            }).catch(() => {});

        // PWA install banner
        const banner = document.getElementById('pwa-install-banner');
        const dismissed = localStorage.getItem('cm_pwa_dismissed') === '1';
        const standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
        if (banner && !dismissed && !standalone) {
            document.addEventListener('cm:pwa-installable', () => banner.classList.add('show'), { once: true });
            setTimeout(() => banner.classList.add('show'), 3000); // fallback if prompt fired earlier
        }
        const dismissBtn = document.getElementById('pwa-dismiss');
        if (dismissBtn) dismissBtn.addEventListener('click', () => {
            banner.classList.remove('show');
            localStorage.setItem('cm_pwa_dismissed', '1');
        });
        const installBtn = document.getElementById('pwa-install');
        if (installBtn) installBtn.addEventListener('click', () => {
            window.cmInstallPwa();
            banner.classList.remove('show');
        });

        // ---- Scroll-reveal (IntersectionObserver) ----
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        const observeReveals = (root) => (root || document).querySelectorAll('.reveal:not(.in-view), .section-title:not(.in-view)').forEach((el) => revealObserver.observe(el));
        observeReveals();

        // ---- 3D tilt on cards (desktop only) ----
        if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
            document.addEventListener('mousemove', (e) => {
                const card = e.target.closest?.('.tilt-card');
                document.querySelectorAll('.tilt-card.tilting').forEach((c) => {
                    if (c !== card) { c.style.setProperty('--tilt-x', '0deg'); c.style.setProperty('--tilt-y', '0deg'); c.classList.remove('tilting'); }
                });
                if (!card) return;
                card.classList.add('tilting');
                const rect = card.getBoundingClientRect();
                const px = (e.clientX - rect.left) / rect.width - 0.5;
                const py = (e.clientY - rect.top) / rect.height - 0.5;
                card.style.setProperty('--tilt-y', (px * 8).toFixed(2) + 'deg');
                card.style.setProperty('--tilt-x', (-py * 8).toFixed(2) + 'deg');
            }, { passive: true });
            document.addEventListener('mouseleave', (e) => {
                if (e.target?.classList?.contains('tilt-card')) {
                    e.target.style.setProperty('--tilt-x', '0deg');
                    e.target.style.setProperty('--tilt-y', '0deg');
                }
            }, true);
        }

        // ---- Ripple on tap for .ripple-host ----
        document.addEventListener('pointerdown', (e) => {
            const host = e.target.closest?.('.ripple-host');
            if (!host) return;
            const rect = host.getBoundingClientRect();
            const ink = document.createElement('span');
            const size = Math.max(rect.width, rect.height);
            ink.className = 'ripple-ink';
            ink.style.width = ink.style.height = size + 'px';
            ink.style.left = (e.clientX - rect.left - size / 2) + 'px';
            ink.style.top = (e.clientY - rect.top - size / 2) + 'px';
            host.appendChild(ink);
            ink.addEventListener('animationend', () => ink.remove());
        }, { passive: true });

        // Re-observe reveals injected later (e.g. Alpine-rendered sections)
        window.cmObserveReveals = observeReveals;

        // ---- Product rail arrows (desktop) ----
        const setupRail = (rail) => {
            if (rail.dataset.railReady) return;
            rail.dataset.railReady = '1';
            const wrap = document.createElement('div');
            wrap.className = 'rail-wrap';
            rail.parentNode.insertBefore(wrap, rail);
            wrap.appendChild(rail);
            const mk = (dir) => {
                const btn = document.createElement('button');
                btn.className = 'rail-arrow ' + dir;
                btn.setAttribute('aria-label', dir === 'prev' ? 'Scroll left' : 'Scroll right');
                btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="' + (dir === 'prev' ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7') + '"/></svg>';
                btn.addEventListener('click', () => {
                    rail.scrollBy({ left: dir === 'prev' ? -rail.clientWidth * 0.8 : rail.clientWidth * 0.8, behavior: 'smooth' });
                });
                wrap.appendChild(btn);
                return btn;
            };
            const prevBtn = mk('prev'), nextBtn = mk('next');
            const update = () => {
                const max = rail.scrollWidth - rail.clientWidth - 2;
                prevBtn.classList.toggle('visible', rail.scrollLeft > 4);
                nextBtn.classList.toggle('visible', rail.scrollLeft < max);
            };
            rail.addEventListener('scroll', update, { passive: true });
            window.addEventListener('resize', update);
            update();
        };
        const setupRails = (root) => (root || document).querySelectorAll('.overflow-x-auto:not([data-rail-ready])').forEach(setupRail);
        setupRails();
        window.cmSetupRails = setupRails;
    })();
    </script>

    {{-- PWA Install Banner --}}
    <div id="pwa-install-banner" class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-6 sm:bottom-6 sm:max-w-sm z-50 bg-ink-900 text-white rounded-2xl shadow-2xl p-4 flex items-center gap-3 opacity-0 translate-y-4 pointer-events-none transition-all duration-300" style="box-shadow: 0 20px 50px rgba(15,23,42,.5);">
        <img src="{{ asset('icons/icon-96.png') }}" alt="CityMarket" class="w-12 h-12 rounded-xl shrink-0">
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold">Install CityMarket</p>
            <p class="text-xs text-ink-300 truncate">Add to home screen for a faster, app-like experience</p>
        </div>
        <button id="pwa-install" class="bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold px-4 py-2 rounded-full transition-colors shrink-0">Install</button>
        <button id="pwa-dismiss" aria-label="Dismiss" class="text-ink-400 hover:text-white transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    {{-- iOS Add to Home Screen hint (no beforeinstallprompt on Safari) --}}
    @if(!(isset($_SERVER['HTTP_USER_AGENT']) && str_contains($_SERVER['HTTP_USER_AGENT'], 'Mobile') && str_contains($_SERVER['HTTP_USER_AGENT'], 'Safari') && !str_contains($_SERVER['HTTP_USER_AGENT'], 'Chrome')))
        @once
        <script>
            (function () {
                var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
                var standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
                if (!isIOS || standalone) return;
                if (localStorage.getItem('cm-ios-hint-dismissed')) return;
                var hint = document.createElement('div');
                hint.id = 'cm-ios-install-hint';
                hint.className = 'fixed left-4 right-4 z-50 bg-ink-900 text-white rounded-2xl shadow-2xl p-4 transition-all duration-300 opacity-0 translate-y-4';
                hint.style.boxShadow = '0 20px 50px rgba(15,23,42,.5)';
                hint.style.bottom = 'calc(5rem + env(safe-area-inset-bottom, 0px))';
                hint.innerHTML =
                    '<div class="flex items-start gap-3">' +
                    '<img src="/icons/icon-96.png" alt="" class="w-10 h-10 rounded-xl shrink-0">' +
                    '<div class="flex-1 text-sm">' +
                    '<p class="font-semibold mb-1">Install CityMarket on iPhone</p>' +
                    '<p class="text-xs text-ink-300 leading-relaxed">1. Tap the <b>Share</b> button <span style="display:inline-block;vertical-align:-3px;">⎋</span> in Safari&rsquo;s toolbar<br>' +
                    '2. Choose <b>&ldquo;Add to Home Screen&rdquo;</b><br>' +
                    '3. Tap <b>Add</b> — the app appears on your home screen</p>' +
                    '</div>' +
                    '<button id="cm-ios-hint-close" aria-label="Dismiss" class="text-ink-400 hover:text-white shrink-0 p-1">' +
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' +
                    '</button></div>';
                document.body.appendChild(hint);
                requestAnimationFrame(function () { requestAnimationFrame(function () { hint.classList.remove('opacity-0', 'translate-y-4'); }); });
                document.getElementById('cm-ios-hint-close').addEventListener('click', function () {
                    hint.classList.add('opacity-0', 'translate-y-4');
                    localStorage.setItem('cm-ios-hint-dismissed', '1');
                    setTimeout(function () { hint.remove(); }, 350);
                });
            })();
        </script>
        @endonce
    @endif
    <style>
        #pwa-install-banner.show { opacity: 1; transform: translateY(0); pointer-events: auto; }
    </style>
</body>

</html>
