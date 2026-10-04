<x-layouts.admin title="Return Requests — Admin">

<h1 class="text-2xl font-bold text-gray-900 mb-6">Return Requests</h1>

@if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 text-green-700 px-4 py-3 text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 py-3 text-left">Order</th>
                <th class="px-4 py-3 text-left">Customer</th>
                <th class="px-4 py-3 text-left">Reason</th>
                <th class="px-4 py-3 text-left">Refund Amount</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($returns as $return)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $return->order->order_number ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $return->user->name ?? 'Unknown' }}</td>
                    <td class="px-4 py-3 text-sm">{{ str_replace('_', ' ', ucfirst($return->reason)) }}</td>
                    <td class="px-4 py-3 font-semibold text-gray-900">₹{{ number_format($return->refund_amount, 0) }}</td>
                    <td class="px-4 py-3">
                        @switch($return->status)
                            @case('pending') <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span> @break
                            @case('approved') <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Approved</span> @break
                            @case('completed') <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Completed</span> @break
                            @case('rejected') <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Rejected</span> @break
                        @endswitch
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($return->status === 'pending')
                            <form action="{{ route('admin.returns.action', $return) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="text-green-600 hover:underline text-sm mr-2">Approve</button>
                            </form>
                            <form action="{{ route('admin.returns.action', $return) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="text-red-500 hover:underline text-sm">Reject</button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400">No actions</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No return requests.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $returns->links() }}</div>

</x-layouts.admin>
