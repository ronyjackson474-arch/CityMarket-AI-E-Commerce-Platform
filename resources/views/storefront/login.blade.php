<x-layouts.storefront title="Login — CityMarket">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-2xl border border-ink-100 p-8">
            <div class="text-center mb-6">
                <div class="text-4xl mb-2">🛍️</div>
                <h1 class="text-2xl font-bold text-ink-900">Welcome back</h1>
                <p class="text-sm text-ink-500">Sign in to your CityMarket account</p>
            </div>

            <form action="{{ route('login.post') }}" method="POST">
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
                    <label class="flex items-center gap-2 text-sm text-ink-600">
                        <input type="checkbox" name="remember" class="rounded text-brand-600 focus:ring-brand-500">
                        Remember me
                    </label>
                    <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-lg hover:bg-brand-700 transition-colors">Sign In</button>
                </div>
            </form>

            <div class="text-center text-sm text-ink-500 mt-5">
                Don't have an account? <a href="{{ route('register') }}" class="text-brand-600 font-medium hover:underline">Sign up</a>
            </div>

            <div class="mt-6 p-3 bg-ink-50 rounded-lg text-xs text-ink-500">
                <p class="font-semibold text-ink-700 mb-1">Demo accounts:</p>
                <p>👤 Customer: customer@citymarket.test / password</p>
                <p>🔧 Admin: admin@citymarket.test / password</p>
            </div>
        </div>
    </div>
</x-layouts.storefront>
