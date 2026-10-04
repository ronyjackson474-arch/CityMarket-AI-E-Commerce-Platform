<x-layouts.vendor title="Earnings & Payouts">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['label' => 'Available Balance', 'value' => '₹' . number_format($summary['available'], 2), 'icon' => '💰', 'bg' => 'bg-green-50 text-green-600'],
            ['label' => 'Pending Payouts', 'value' => '₹' . number_format($summary['pending_payouts'], 2), 'icon' => '⏳', 'bg' => 'bg-amber-50 text-amber-600'],
            ['label' => 'Total Withdrawn', 'value' => '₹' . number_format($summary['withdrawn'], 2), 'icon' => '🏧', 'bg' => 'bg-blue-50 text-blue-600'],
            ['label' => 'Gross Sales', 'value' => '₹' . number_format($summary['total_gross'], 2), 'icon' => '📈', 'bg' => 'bg-purple-50 text-purple-600'],
        ] as $card)
            <div class="bg-white rounded-2xl border border-ink-100 p-5 shadow-sm">
                <div class="w-10 h-10 rounded-xl {{ $card['bg'] }} flex items-center justify-center text-lg mb-3">{{ $card['icon'] }}</div>
                <div class="text-xl font-extrabold text-ink-900">{{ $card['value'] }}</div>
                <div class="text-xs text-ink-500 font-medium mt-0.5">{{ $card['label'] }}</div>
            </div>
        @endforeach
    </div>

    <!-- Withdrawal request -->
    <div class="bg-white rounded-2xl border border-ink-100 p-5 mb-6">
        <h2 class="font-bold text-ink-900 mb-1">Request a Withdrawal</h2>
        <p class="text-xs text-ink-400 mb-4">Money is credited to your account after admin approval. Only one pending request at a time.</p>

        @if($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('vendor.withdrawals.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs font-bold text-ink-600 mb-1">Amount (₹)</label>
                <input type="number" name="amount" min="1" step="0.01" max="{{ $summary['available'] }}" required
                       class="w-full rounded-lg border-ink-200 focus:border-brand-500 focus:ring-brand-500 text-sm" placeholder="0.00">
            </div>
            <div>
                <label class="block text-xs font-bold text-ink-600 mb-1">Method</label>
                <select name="method" class="w-full rounded-lg border-ink-200 focus:border-brand-500 focus:ring-brand-500 text-sm">
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="upi">UPI</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-ink-600 mb-1">Account / UPI ID</label>
                <input type="text" name="payout_detail" required maxlength="200"
                       class="w-full rounded-lg border-ink-200 focus:border-brand-500 focus:ring-brand-500 text-sm" placeholder="e.g. name@upi or A/C no.">
            </div>
            <button type="submit" class="h-[38px] px-4 rounded-lg bg-brand-600 text-white text-sm font-bold hover:bg-brand-700 transition-colors whitespace-nowrap">
                Request Payout
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Withdrawals -->
        <div class="bg-white rounded-2xl border border-ink-100 p-5">
            <h2 class="font-bold text-ink-900 mb-4">Withdrawal Requests</h2>
            @forelse($withdrawals as $w)
                <div class="flex items-center justify-between py-2.5 border-b border-ink-50 last:border-0">
                    <div>
                        <p class="text-sm font-semibold text-ink-800">₹{{ number_format($w->amount, 2) }}</p>
                        <p class="text-xs text-ink-400">{{ ucfirst($w->method) }} · {{ $w->payout_detail }} · {{ $w->created_at->format('d M Y') }}</p>
                        @if($w->admin_note)
                            <p class="text-xs text-ink-400 italic mt-0.5">Note: {{ $w->admin_note }}</p>
                        @endif
                    </div>
                    @if($w->status === 'paid')
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-50 text-green-700">PAID</span>
                    @elseif($w->status === 'pending')
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">PENDING</span>
                    @elseif($w->status === 'rejected')
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-50 text-red-700">REJECTED</span>
                    @else
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">{{ strtoupper($w->status) }}</span>
                    @endif
                </div>
            @empty
                <p class="text-sm text-ink-400 py-6 text-center">No withdrawal requests yet.</p>
            @endforelse
        </div>

        <!-- Earnings ledger -->
        <div class="bg-white rounded-2xl border border-ink-100 p-5">
            <h2 class="font-bold text-ink-900 mb-4">Earnings Ledger</h2>
            @forelse($ledger as $e)
                <div class="flex items-center justify-between py-2.5 border-b border-ink-50 last:border-0">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-ink-800 truncate">{{ $e->orderItem->product_name ?? ('Item #' . $e->order_item_id) }}</p>
                        <p class="text-xs text-ink-400">
                            Gross ₹{{ number_format($e->gross, 2) }} − commission ₹{{ number_format($e->commission, 2) }}
                            @if($e->orderItem?->order)
                                · {{ $e->orderItem->order->order_number }}
                            @endif
                        </p>
                    </div>
                    <div class="text-right shrink-0 ml-3">
                        <p class="text-sm font-bold {{ $e->status === 'available' ? 'text-green-600' : 'text-ink-400' }}">+₹{{ number_format($e->net, 2) }}</p>
                        <p class="text-[10px] font-bold text-ink-400 uppercase">{{ $e->status }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-ink-400 py-6 text-center">No earnings yet — they appear when an order containing your products is delivered.</p>
            @endforelse
            <div class="mt-3">{{ $ledger->links() }}</div>
        </div>
    </div>
</x-layouts.vendor>
