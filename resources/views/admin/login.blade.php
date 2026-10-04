<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login — CityMarket</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚙️</text></svg>">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-brand-600 rounded-2xl text-3xl mb-3 shadow-xl">⚙️</div>
            <h1 class="text-2xl font-bold text-white">CityMarket Admin</h1>
            <p class="text-sm text-ink-400">Sign in to manage your store</p>
        </div>

        <div class="bg-white rounded-2xl p-8 shadow-2xl">
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-2 mb-4">{{ session('error') }}</div>
            @endif

            <form action="{{ route('admin.login.post') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none @error('email') border-red-400 @enderror">
                        @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Password</label>
                        <input type="password" name="password" required
                            class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                    </div>
                    <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-lg hover:bg-brand-700 transition-colors">Sign In to Admin</button>
                </div>
            </form>

            <div class="mt-5 p-3 bg-ink-50 rounded-lg text-xs text-ink-500">
                <p class="font-semibold text-ink-700 mb-1">Demo admin:</p>
                <p>admin@citymarket.test / password</p>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('home') }}" class="text-sm text-ink-400 hover:text-white">← Back to Storefront</a>
        </div>
    </div>
</body>
</html>
