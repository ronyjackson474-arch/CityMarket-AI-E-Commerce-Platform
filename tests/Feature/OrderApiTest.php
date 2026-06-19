<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_place_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 200]);

        $cart = Cart::factory()->for($user)->create();
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/orders', [
                'customer_name' => 'Test Customer',
                'customer_phone' => '+919999999999',
                'shipping_address' => '123 Test St, Mumbai, 400001',
                'payment_method' => 'cod',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => Order::STATUS_PENDING,
            'customer_name' => 'Test Customer',
        ]);

        // Stock should be decremented
        $this->assertEquals(8, $product->fresh()->stock);

        // Cart should be cleared
        $this->assertEquals(0, $cart->fresh()->items()->count());
    }

    public function test_cannot_place_order_with_empty_cart(): void
    {
        $user = User::factory()->create();
        Cart::factory()->for($user)->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/orders', [
                'customer_name' => 'Test',
                'customer_phone' => '+919999999999',
                'shipping_address' => '123 St',
                'payment_method' => 'cod',
            ])
            ->assertBadRequest();
    }

    public function test_cannot_order_more_than_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 3]);

        $cart = Cart::factory()->for($user)->create();
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 5]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/orders', [
                'customer_name' => 'Test',
                'customer_phone' => '+919999999999',
                'shipping_address' => '123 St',
                'payment_method' => 'cod',
            ])
            ->assertBadRequest();
    }

    public function test_can_apply_coupon(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 1000, 'sale_price' => null]);

        $cart = Cart::factory()->for($user)->create();
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);

        $coupon = Coupon::factory()->create([
            'code' => 'SAVE20',
            'type' => 'percentage',
            'value' => 20,
            'min_order_amount' => 0,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/orders', [
                'customer_name' => 'Test',
                'customer_phone' => '+919999999999',
                'shipping_address' => '123 St',
                'payment_method' => 'cod',
                'coupon_code' => 'SAVE20',
            ])
            ->assertCreated();

        // 2000 subtotal - 400 discount (20%) + 0 shipping (>=500) = 1600
        $this->assertEquals(1600, (float) $response->json('data.total'));
    }

    public function test_can_list_own_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->for($user)->count(3)->create();
        Order::factory()->count(2)->create(); // other users

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/orders')
            ->assertOk();

        $this->assertEquals(3, count($response->json('data')));
    }

    public function test_can_cancel_pending_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create(['status' => Order::STATUS_PENDING]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/orders/{$order->order_number}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', Order::STATUS_CANCELLED);
    }

    public function test_cannot_cancel_delivered_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create(['status' => Order::STATUS_DELIVERED]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/orders/{$order->order_number}/cancel")
            ->assertBadRequest();
    }
}
