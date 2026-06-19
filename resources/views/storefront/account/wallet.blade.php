<x-layouts.storefront title="My Wallet — CityMarket">

<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">My Wallet</h1>

    <div class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl p-8 text-white mb-6">
        <p class="text-rose-100 text-sm">Available Balance</p>
        <p class="text-4xl font-bold mt-1">₹{{ number_format($wallet->balance ?? 0, 2) }}</p>
        <p class="text-rose-100 text-xs mt-2">Earn 1% cashback on every order automatically</p>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-50 text-green-700 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Transaction History</h2>
        @if($transactions->isEmpty())
            <p class="text-gray-400 text-sm py-8 text-center">No transactions yet.</p>
        @else
            <div class="space-y-2">
                @foreach($transactions as $tx)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $tx->type === 'credit' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                {{ $tx->type === 'credit' ? '↓' : '↑' }}
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 text-sm">{{ $tx->description }}</div>
                                <div class="text-xs text-gray-500">{{ $tx->created_at->format('M d, Y \a\t h:i A') }}</div>
                            </div>
                        </div>
                        <div class="font-semibold {{ $tx->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
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
