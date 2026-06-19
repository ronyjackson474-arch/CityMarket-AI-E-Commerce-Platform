<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_add_to_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/cart/add', [
                'product_id' => $product->id,
                'quantity' => 2,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Product added to cart.');

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $user->cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_guest_cannot_use_cart(): void
    {
        $product = Product::factory()->create();

        $this->postJson('/api/cart/add', ['product_id' => $product->id])
            ->assertUnauthorized();
    }

    public function test_cannot_exceed_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/cart/add', [
                'product_id' => $product->id,
                'quantity' => 10,
            ])
            ->assertBadRequest();
    }

    public function test_can_update_cart_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $cart = Cart::factory()->for($user)->create();
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/cart/update', [
                'product_id' => $product->id,
                'quantity' => 5,
            ])
            ->assertOk();

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);
    }

    public function test_can_remove_from_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $cart = Cart::factory()->for($user)->create();
        $item = $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/cart/remove', ['product_id' => $product->id])
            ->assertOk();

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_can_view_cart_with_summary(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->for($user)->create();
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 3]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/cart')
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'items'],
                'summary' => ['item_count', 'subtotal', 'shipping', 'total'],
            ]);
    }
}
