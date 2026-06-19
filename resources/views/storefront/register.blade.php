<x-layouts.storefront title="Sign Up — CityMarket">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-2xl border border-ink-100 p-8">
            <div class="text-center mb-6">
                <div class="text-4xl mb-2">🛍️</div>
                <h1 class="text-2xl font-bold text-ink-900">Create Account</h1>
                <p class="text-sm text-ink-500">Join CityMarket for smarter shopping</p>
            </div>

            <form action="{{ route('register.post') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none @error('name') border-red-400 @enderror">
                        @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none @error('email') border-red-400 @enderror">
                        @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Phone (optional)</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}"
                            class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Password</label>
                        <input type="password" name="password" required
                            class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none @error('password') border-red-400 @enderror">
                        @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink-700 mb-1 block">Confirm Password</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full px-3 py-2.5 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
                    </div>
                    <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-lg hover:bg-brand-700 transition-colors">Create Account</button>
                </div>
            </form>

            <div class="text-center text-sm text-ink-500 mt-5">
                Already have an account? <a href="{{ route('login') }}" class="text-brand-600 font-medium hover:underline">Sign in</a>
            </div>
        </div>
    </div>
</x-layouts.storefront>
