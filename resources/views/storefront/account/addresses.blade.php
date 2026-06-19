@extends('components.layouts.storefront')

@section('title', 'Addresses — CityMarket')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Saved Addresses</h1>

    @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-50 text-green-700 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        @forelse($addresses as $address)
            <div class="bg-white rounded-2xl shadow-sm p-5 relative">
                @if($address->is_default)
                    <span class="absolute top-3 right-3 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-700">Default</span>
                @endif
                <h3 class="font-semibold text-gray-900 mb-2">{{ $address->name }}</h3>
                <p class="text-sm text-gray-600">{{ $address->address_line1 }}</p>
                @if($address->address_line2)<p class="text-sm text-gray-600">{{ $address->address_line2 }}</p>@endif
                <p class="text-sm text-gray-600">{{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}</p>
                <p class="text-sm text-gray-600 mt-1">📞 {{ $address->phone }}</p>
                <form action="{{ route('account.addresses.delete', $address) }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete Address</button>
                </form>
            </div>
        @empty
            <div class="col-span-2 bg-white rounded-2xl shadow-sm p-12 text-center">
                <p class="text-gray-400">No saved addresses yet.</p>
            </div>
        @endforelse
    </div>

    {{-- Add new address --}}
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Add New Address</h2>
        <form action="{{ route('account.addresses.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" name="name" required class="w-full rounded-xl border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" name="phone" required class="w-full rounded-xl border-gray-300 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 1</label>
                <input type="text" name="address_line1" required class="w-full rounded-xl border-gray-300 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 2 (optional)</label>
                <input type="text" name="address_line2" class="w-full rounded-xl border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <input type="text" name="city" required class="w-full rounded-xl border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                <input type="text" name="state" required class="w-full rounded-xl border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                <input type="text" name="pincode" required class="w-full rounded-xl border-gray-300 text-sm">
            </div>
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" name="is_default" id="is_default" class="rounded text-rose-600">
                <label for="is_default" class="text-sm text-gray-700">Set as default</label>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="px-6 py-3 bg-rose-600 text-white rounded-xl font-medium hover:bg-rose-700">Save Address</button>
            </div>
        </form>
    </div>
</div>
@endsection
