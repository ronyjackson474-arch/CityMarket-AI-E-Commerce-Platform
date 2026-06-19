@extends('components.layouts.admin')

@section('title', 'Edit Coupon — Admin')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Coupon</h1>

    <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST" class="bg-white rounded-2xl shadow-sm p-6 space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Coupon Code</label>
            <input type="text" name="code" value="{{ old('code', $coupon->code) }}" required class="w-full rounded-xl border-gray-300">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full rounded-xl border-gray-300">
                    <option value="percentage" {{ $coupon->type === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                    <option value="fixed" {{ $coupon->type === 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Value</label>
                <input type="number" name="value" step="0.01" min="0" value="{{ old('value', $coupon->value) }}" required class="w-full rounded-xl border-gray-300">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Min Order Amount (₹)</label>
                <input type="number" name="min_order_amount" step="0.01" min="0" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" class="w-full rounded-xl border-gray-300">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Max Discount (₹)</label>
                <input type="number" name="max_discount" step="0.01" min="0" value="{{ old('max_discount', $coupon->max_discount) }}" class="w-full rounded-xl border-gray-300">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Usage Limit</label>
            <input type="number" name="usage_limit" min="1" value="{{ old('usage_limit', $coupon->usage_limit) }}" class="w-full rounded-xl border-gray-300">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="starts_at" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d')) }}" class="w-full rounded-xl border-gray-300">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                <input type="date" name="expires_at" value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d')) }}" class="w-full rounded-xl border-gray-300">
            </div>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" {{ $coupon->is_active ? 'checked' : '' }} class="rounded text-rose-600">
            <label for="is_active" class="text-sm text-gray-700">Active</label>
            <span class="ml-auto text-sm text-gray-500">Used: {{ $coupon->used_count }} time(s)</span>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-3 bg-rose-600 text-white rounded-xl font-medium hover:bg-rose-700">Update Coupon</button>
            <a href="{{ route('admin.coupons.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl font-medium text-gray-600 hover:bg-gray-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
