<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show(Request $request, WalletService $walletService)
    {
        $wallet = $walletService->forUser($request->user());

        return response()->json([
            'data' => $wallet->load(['transactions' => fn ($q) => $q->latest()->limit(20)]),
        ]);
    }

    public function transactions(Request $request, WalletService $walletService)
    {
        $wallet = $walletService->forUser($request->user());

        $transactions = $wallet->transactions()->latest()->paginate(20);

        return response()->json($transactions);
    }

    public function applyReferral(Request $request, WalletService $walletService)
    {
        $data = $request->validate([
            'referral_code' => ['required', 'string'],
        ]);

        $applied = $walletService->applyReferralCode($request->user(), $data['referral_code']);

        if (! $applied) {
            return response()->json(['message' => 'Invalid or already used referral code.'], 400);
        }

        return response()->json([
            'message' => 'Referral code applied! You received 100 bonus points.',
        ]);
    }
}
