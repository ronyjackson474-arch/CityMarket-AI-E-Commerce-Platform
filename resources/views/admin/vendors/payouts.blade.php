<x-layouts.admin title="Vendor Payouts">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-extrabold text-ink-900">Vendor Payouts</h1>
        <div class="flex gap-1 text-xs font-bold">
            @foreach(['all' => 'All', 'pending' => 'Pending', 'paid' => 'Paid', 'rejected' => 'Rejected'] as $key => $label)
                <a href="{{ route('admin.vendors.payouts', ['status' => $key]) }}"
                   class="px-3 py-1.5 rounded-lg {{ $status === $key ? 'bg-brand-600 text-white' : 'bg-white border border-ink-200 text-ink-600 hover:bg-ink-50' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-ink-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-ink-50 text-ink-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-5 py-3 font-bold">Vendor</th>
                    <th class="text-left px-5 py-3 font-bold">Amount</th>
                    <th class="text-left px-5 py-3 font-bold">Method</th>
                    <th class="text-left px-5 py-3 font-bold">Payout Detail</th>
                    <th class="text-left px-5 py-3 font-bold">Requested</th>
                    <th class="text-left px-5 py-3 font-bold">Status</th>
                    <th class="text-right px-5 py-3 font-bold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($withdrawals as $w)
                    <tr class="border-t border-ink-50">
                        <td class="px-5 py-3">
                            <p class="font-semibold text-ink-800">{{ $w->vendor->shop_name }}</p>
                            <p class="text-xs text-ink-400">{{ $w->vendor->user?->email }}</p>
                        </td>
                        <td class="px-5 py-3 font-bold text-ink-900">₹{{ number_format($w->amount, 2) }}</td>
                        <td class="px-5 py-3 text-ink-600">{{ ucfirst($w->method) }}</td>
                        <td class="px-5 py-3 text-ink-600 font-mono text-xs">{{ $w->payout_detail }}</td>
                        <td class="px-5 py-3 text-ink-500 text-xs">{{ $w->created_at->format('d M Y H:i') }}</td>
                        <td class="px-5 py-3">
                            @if($w->status === 'paid')
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-50 text-green-700">PAID</span>
                            @elseif($w->status === 'pending')
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">PENDING</span>
                            @elseif($w->status === 'rejected')
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-50 text-red-700">REJECTED</span>
                            @else
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">{{ strtoupper($w->status) }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            @if($w->status === 'pending')
                                <form action="{{ route('admin.withdrawals.approve', $w) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="note" value="Paid via {{ ucfirst($w->method) }}">
                                    <button class="text-xs font-bold px-3 py-1.5 rounded-lg bg-green-600 text-white hover:bg-green-700">Mark Paid</button>
                                </form>
                                <form action="{{ route('admin.withdrawals.reject', $w) }}" method="POST" class="inline ml-1">
                                    @csrf
                                    <input type="hidden" name="note" value="Rejected by admin">
                                    <button class="text-xs font-bold px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100">Reject</button>
                                </form>
                            @elseif($w->admin_note)
                                <span class="text-xs text-ink-400 italic">{{ $w->admin_note }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-10 text-center text-ink-400">No payout requests {{ $status !== 'all' ? "with status \"{$status}\"" : 'yet' }}.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $withdrawals->links() }}</div>
    </div>
</x-layouts.admin>
