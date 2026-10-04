<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Session;

/**
 * Resolves or creates the current user's cart (for API token auth and web session).
 */
class CartService
{
    public function forUser(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    public function current(?User $user): Cart
    {
        if ($user) {
            return $this->forUser($user);
        }

        $sessionId = Session::getId();
        $cart = Cart::firstOrCreate(['session_id' => $sessionId]);

        // If guest later logs in, migrate guest cart to user cart.
        return $cart;
    }
}
