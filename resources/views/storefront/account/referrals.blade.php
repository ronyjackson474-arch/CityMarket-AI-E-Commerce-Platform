@extends('components.layouts.storefront')

@section('title', 'Refer & Earn — CityMarket')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Refer & Earn</h1>

    <div class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl p-8 text-white mb-6 text-center">
        <h2 class="text-xl font-bold mb-2">Your Referral Code</h2>
        <p class="text-4xl font-bold tracking-wider bg-white/20 rounded-xl py-3 px-6 inline-block mt-2">{{ $wallet->referral_code ?? 'N/A' }}</p>
        <p class="text-rose-100 text-sm mt-4">Share this code with friends. When they sign up, you both get ₹100 bonus!</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Your Referrals</h3>
        @if($referrals->isEmpty())
            <p class="text-gray-400 text-sm py-8 text-center">No referrals yet. Start sharing your code!</p>
        @else
            <div class="space-y-3">
                @foreach($referrals as $ref)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center">
                                <span class="text-rose-600 font-bold text-xs">{{ strtoupper(substr($ref->user->name ?? '?', 0, 1)) }}</span>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 text-sm">{{ $ref->user->name ?? 'Unknown' }}</div>
                                <div class="text-xs text-gray-500">Joined {{ $ref->created_at->format('M d, Y') }}</div>
                            </div>
                        </div>
                        <span class="text-green-600 font-semibold text-sm">+₹100</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
