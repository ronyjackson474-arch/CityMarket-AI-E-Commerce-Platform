@extends('components.layouts.admin')

@section('title', 'Coupons — Admin')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Coupons</h1>
    <a href="{{ route('admin.coupons.create') }}" class="px-4 py-2 bg-rose-600 text-white rounded-xl font-medium hover:bg-rose-700">+ New Coupon</a>
</div>

@if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 text-green-700 px-4 py-3 text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 py-3 text-left">Code</th>
                <th class="px-4 py-3 text-left">Type</th>
                <th class="px-4 py-3 text-left">Value</th>
                <th class="px-4 py-3 text-left">Usage</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($coupons as $coupon)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono font-medium text-gray-900">{{ $coupon->code }}</td>
                    <td class="px-4 py-3 text-sm">{{ ucfirst($coupon->type) }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($coupon->type === 'percentage'){{ $coupon->value }}% @else ₹{{ number_format($coupon->value, 0) }} @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}</td>
                    <td class="px-4 py-3">
                        @if($coupon->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-rose-600 hover:underline text-sm">Edit</a>
                        <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Delete coupon?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:underline text-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No coupons found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $coupons->links() }}</div>
@endsection
