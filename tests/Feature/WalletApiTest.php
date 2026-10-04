<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_wallet(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/wallet');

        $response->assertOk();
        $response->assertJsonFragment(['balance' => $wallet->balance]);
    }

    public function test_wallet_creates_on_first_access(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/wallet');

        $response->assertOk();
        $this->assertDatabaseHas('wallets', ['user_id' => $user->id]);
    }

    public function test_can_view_transactions(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/wallet/transactions');

        $response->assertOk();
    }

    public function test_guest_cannot_access_wallet(): void
    {
        $response = $this->getJson('/api/wallet');
        $response->assertStatus(401);
    }
}
