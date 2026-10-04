<x-layouts.storefront title="My Wallet — CityMarket">

<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-ink-900 mb-6">My Wallet</h1>

    <div class="bg-gradient-to-br from-brand-500 to-brand-700 rounded-2xl p-8 text-white mb-6 relative overflow-hidden">
        <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-white/10 skew-x-12"></div>
        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-1">
                <p class="text-brand-100 text-sm">Available Balance</p>
            </div>
            <p class="text-4xl font-bold mt-1">₹{{ number_format($wallet->balance ?? 0, 2) }}</p>
            <p class="text-brand-100 text-xs mt-2 flex items-center gap-1">Earn 1% cashback on every order automatically</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-ink-100 p-6">
        <h2 class="font-semibold text-ink-900 mb-4">Transaction History</h2>
        @if($transactions->isEmpty())
            <div class="text-center py-10">
                <div class="text-4xl mb-3">🧾</div>
                <p class="text-ink-400 text-sm">No transactions yet.</p>
            </div>
        @else
            <div class="space-y-2">
                @foreach($transactions as $tx)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-ink-50 hover:bg-ink-100 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center {{ $tx->type === 'credit' ? 'bg-green-100 text-green-600' : 'bg-rose-100 text-rose-600' }}">
                                {{ $tx->type === 'credit' ? '↓' : '↑' }}
                            </div>
                            <div>
                                <div class="font-medium text-ink-900 text-sm">{{ $tx->description }}</div>
                                <div class="text-xs text-ink-500">{{ $tx->created_at->format('M d, Y \a\t h:i A') }}</div>
                            </div>
                        </div>
                        <div class="font-semibold {{ $tx->type === 'credit' ? 'text-green-600' : 'text-rose-600' }}">
                            {{ $tx->type === 'credit' ? '+' : '-' }}₹{{ number_format($tx->amount, 2) }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        <div class="mt-4">{{ $transactions->links() }}</div>
    </div>
</div>

</x-layouts.storefront>
