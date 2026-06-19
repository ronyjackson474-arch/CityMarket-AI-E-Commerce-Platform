<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Services\WalletService;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function store(Request $request, string $orderNumber, WalletService $walletService)
    {
        $order = $request->user()
            ->orders()
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if (! in_array($order->status, [Order::STATUS_DELIVERED])) {
            return response()->json([
                'message' => 'Returns can only be requested for delivered orders.',
            ], 400);
        }

        $existing = ReturnRequest::where('order_id', $order->id)
            ->whereIn('status', [ReturnRequest::STATUS_PENDING, ReturnRequest::STATUS_APPROVED])
            ->exists();

        if ($existing) {
            return response()->json(['message' => 'A return request already exists for this order.'], 400);
        }

        $data = $request->validate([
            'reason' => ['required', 'in:damaged,wrong_item,not_as_described,changed_mind'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $return = ReturnRequest::create([
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'reason' => $data['reason'],
            'description' => $data['description'] ?? null,
            'refund_amount' => $order->total,
            'status' => ReturnRequest::STATUS_PENDING,
        ]);

        return response()->json([
            'message' => 'Return request submitted.',
            'data' => $return,
        ], 201);
    }
}
