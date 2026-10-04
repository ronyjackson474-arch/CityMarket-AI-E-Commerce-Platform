<x-layouts.storefront title="Addresses — CityMarket">

<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-ink-900 mb-6">Saved Addresses</h1>

    @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        @forelse($addresses as $address)
            <div class="bg-white rounded-2xl shadow-sm border border-ink-100 p-5 relative hover:shadow-md transition-shadow">
                @if($address->is_default)
                    <span class="absolute top-3 right-3 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-700">⭐ Default</span>
                @endif
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-brand-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-ink-900 mb-2">{{ $address->name }}</h3>
                        <p class="text-sm text-ink-600">{{ $address->address_line1 }}</p>
                        @if($address->address_line2)<p class="text-sm text-ink-600">{{ $address->address_line2 }}</p>@endif
                        <p class="text-sm text-ink-600">{{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}</p>
                        <p class="text-sm text-ink-500 mt-1 flex items-center gap-1">📞 {{ $address->phone }}</p>
                    </div>
                </div>
                <form action="{{ route('account.addresses.delete', $address) }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="text-xs text-rose-500 hover:text-rose-700 font-medium" onclick="return confirm('Delete this address?')">🗑 Delete Address</button>
                </form>
            </div>
        @empty
            <div class="col-span-2 bg-white rounded-2xl shadow-sm border border-ink-100 p-12 text-center">
                <div class="text-5xl mb-3">📍</div>
                <p class="text-ink-400">No saved addresses yet.</p>
            </div>
        @endforelse
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-ink-100 p-6">
        <h2 class="font-semibold text-ink-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Add New Address
        </h2>
        <form action="{{ route('account.addresses.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-ink-700 mb-1">Full Name</label>
                <input type="text" name="name" required class="w-full rounded-xl border border-ink-200 px-3 py-2.5 text-sm focus:border-brand-500 outline-none transition-colors">
            </div>
            <div>
                <label class="block text-sm font-medium text-ink-700 mb-1">Phone</label>
                <input type="text" name="phone" required class="w-full rounded-xl border border-ink-200 px-3 py-2.5 text-sm focus:border-brand-500 outline-none transition-colors">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-ink-700 mb-1">Address Line 1</label>
                <input type="text" name="address_line1" required class="w-full rounded-xl border border-ink-200 px-3 py-2.5 text-sm focus:border-brand-500 outline-none transition-colors">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-ink-700 mb-1">Address Line 2 (optional)</label>
                <input type="text" name="address_line2" class="w-full rounded-xl border border-ink-200 px-3 py-2.5 text-sm focus:border-brand-500 outline-none transition-colors">
            </div>
            <div>
                <label class="block text-sm font-medium text-ink-700 mb-1">City</label>
                <input type="text" name="city" required class="w-full rounded-xl border border-ink-200 px-3 py-2.5 text-sm focus:border-brand-500 outline-none transition-colors">
            </div>
            <div>
                <label class="block text-sm font-medium text-ink-700 mb-1">State</label>
                <input type="text" name="state" required class="w-full rounded-xl border border-ink-200 px-3 py-2.5 text-sm focus:border-brand-500 outline-none transition-colors">
            </div>
            <div>
                <label class="block text-sm font-medium text-ink-700 mb-1">Pincode</label>
                <input type="text" name="pincode" required class="w-full rounded-xl border border-ink-200 px-3 py-2.5 text-sm focus:border-brand-500 outline-none transition-colors">
            </div>
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" name="is_default" id="is_default" class="rounded text-brand-600 focus:ring-brand-500">
                <label for="is_default" class="text-sm text-ink-700">Set as default</label>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="px-6 py-3 bg-brand-600 text-white rounded-xl font-medium hover:bg-brand-700 transition-colors">Save Address</button>
            </div>
        </form>
    </div>
</div>

</x-layouts.storefront>
