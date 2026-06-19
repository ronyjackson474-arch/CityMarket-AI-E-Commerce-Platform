<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — Admin' : 'CityMarket Admin' }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚙️</text></svg>">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink-100 min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-ink-900 text-ink-300 flex flex-col fixed inset-y-0 left-0 z-30 hidden md:flex">
            <div class="p-5 border-b border-ink-800">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    <span class="text-2xl">⚙️</span>
                    <div>
                        <div class="text-white font-bold">CityMarket</div>
                        <div class="text-xs text-ink-500">Admin Panel</div>
                    </div>
                </a>
            </div>
            <nav class="flex-1 p-3 space-y-1 text-sm">
                @foreach([
                    ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => '📊'],
                    ['route' => 'admin.products.index', 'label' => 'Products', 'icon' => '📦'],
                    ['route' => 'admin.categories.index', 'label' => 'Categories', 'icon' => '🗂️'],
                    ['route' => 'admin.brands.index', 'label' => 'Brands', 'icon' => '🏷️'],
                    ['route' => 'admin.orders.index', 'label' => 'Orders', 'icon' => '🧾'],
                    ['route' => 'admin.users.index', 'label' => 'Users', 'icon' => '👥'],
                ] as $item)
                    <a href="{{ route($item['route']) }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-ink-800 transition-colors {{ (request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route']))) ? 'bg-brand-600 text-white hover:bg-brand-600' : '' }}">
                        <span class="text-base">{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="p-3 border-t border-ink-800">
                <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-ink-800 text-sm">
                    🏪 View Storefront
                </a>
                <a href="{{ route('admin.logout') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-ink-800 text-sm" onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                    🚪 Logout
                </a>
                <form id="admin-logout-form" action="{{ route('admin.logout') }}" method="POST" class="hidden">@csrf</form>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 md:ml-64 flex flex-col">
            <!-- Top bar -->
            <header class="bg-white border-b border-ink-200 px-6 py-3 flex items-center justify-between sticky top-0 z-20">
                <div class="md:hidden flex items-center gap-2">
                    <span class="text-xl">⚙️</span>
                    <span class="font-bold text-ink-900">Admin</span>
                </div>
                <h1 class="text-lg font-bold text-ink-900 hidden md:block">{{ $title ?? 'Dashboard' }}</h1>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-ink-500">Hi, {{ auth()->user()->name }}</span>
                    <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </div>
            </header>

            <!-- Mobile nav -->
            <nav class="md:hidden bg-white border-b border-ink-200 px-4 py-2 flex gap-1 overflow-x-auto no-scrollbar">
                @foreach([
                    ['route' => 'admin.dashboard', 'label' => 'Dashboard'],
                    ['route' => 'admin.products.index', 'label' => 'Products'],
                    ['route' => 'admin.categories.index', 'label' => 'Categories'],
                    ['route' => 'admin.brands.index', 'label' => 'Brands'],
                    ['route' => 'admin.orders.index', 'label' => 'Orders'],
                    ['route' => 'admin.users.index', 'label' => 'Users'],
                ] as $item)
                    <a href="{{ route($item['route']) }}" class="px-3 py-1.5 rounded-lg text-sm whitespace-nowrap {{ request()->routeIs($item['route']) ? 'bg-brand-600 text-white' : 'bg-ink-100 text-ink-600' }}">{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <!-- Page content -->
            <main class="flex-1 p-6">
                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3 mb-4">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg px-4 py-3 mb-4">{{ session('error') }}</div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
