<x-layouts.admin title="Vendors">
    <div class="mb-5 flex flex-wrap items-center gap-2">
        @foreach(['all' => 'All', 'pending' => '⏳ Pending', 'approved' => '✅ Approved', 'suspended' => '🚫 Suspended'] as $key => $label)
            <a href="{{ route('admin.vendors.index', ['status' => $key]) }}"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors {{ $status === $key ? 'bg-ink-900 text-white' : 'bg-white border border-ink-200 text-ink-600 hover:bg-ink-50' }}">
                {{ $label }} <span class="opacity-60">({{ $counts[$key] }})</span>
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-ink-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-ink-400 border-b border-ink-100">
                        <th class="px-5 py-3 font-semibold">Shop</th>
                        <th class="px-5 py-3 font-semibold">Owner</th>
                        <th class="px-5 py-3 font-semibold">Products</th>
                        <th class="px-5 py-3 font-semibold">Rating</th>
                        <th class="px-5 py-3 font-semibold">Commission</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                        <tr class="border-b border-ink-50 hover:bg-ink-50/50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    @if($vendor->logo_url)
                                        <img src="{{ $vendor->logo_url }}" class="w-9 h-9 rounded-lg object-cover border border-ink-100" alt="">
                                    @else
                                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">{{ mb_substr($vendor->shop_name, 0, 1) }}</div>
                                    @endif
                                    <div>
                                        <a href="{{ route('vendor.shop', $vendor->slug) }}" class="font-semibold text-ink-800 hover:text-brand-600">{{ $vendor->shop_name }}</a>
                                        <p class="text-[11px] text-ink-400">/vendor/{{ $vendor->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <p class="text-ink-700 font-medium">{{ $vendor->user?->name }}</p>
                                <p class="text-[11px] text-ink-400">{{ $vendor->user?->email }}</p>
                            </td>
                            <td class="px-5 py-3">{{ $vendor->products_count }}</td>
                            <td class="px-5 py-3">★ {{ number_format($vendor->rating, 1) }}</td>
                            <td class="px-5 py-3">
                                <form action="{{ route('admin.vendors.commission', $vendor) }}" method="POST" class="flex items-center gap-1.5">
                                    @csrf
                                    <input type="number" name="commission_pct" value="{{ $vendor->commission_pct }}" min="0" max="100"
                                        class="w-16 px-2 py-1 border border-ink-200 rounded-md text-xs">
                                    <button class="text-xs font-bold text-blue-600 hover:underline">Save</button>
                                </form>
                            </td>
                            <td class="px-5 py-3">
                                @if($vendor->status === 'approved')
                                    <span class="text-[10px] font-bold px-2 py-1 rounded-full bg-green-50 text-green-700">APPROVED</span>
                                @elseif($vendor->status === 'pending')
                                    <span class="text-[10px] font-bold px-2 py-1 rounded-full bg-amber-50 text-amber-700">PENDING</span>
                                @else
                                    <span class="text-[10px] font-bold px-2 py-1 rounded-full bg-red-50 text-red-600">SUSPENDED</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                @if($vendor->status !== 'approved')
                                    <form action="{{ route('admin.vendors.approve', $vendor) }}" method="POST" class="inline">
                                        @csrf
                                        <button class="text-xs font-bold text-green-600 hover:underline mr-3">Approve</button>
                                    </form>
                                @endif
                                @if($vendor->status !== 'suspended')
                                    <form action="{{ route('admin.vendors.suspend', $vendor) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Suspend {{ $vendor->shop_name }}? Their shop and products will be hidden.')">
                                        @csrf
                                        <button class="text-xs font-bold text-red-600 hover:underline">Suspend</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-ink-400">No vendors found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $vendors->links() }}</div>
    </div>
</x-layouts.admin>
