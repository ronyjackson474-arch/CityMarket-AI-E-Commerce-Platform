<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorFulfilmentTest extends TestCase
{
    use RefreshDatabase;

    private function setupVendorOrder(): array
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'shop_name' => 'Shop', 'slug' => 'shop-' . uniqid(),
            'status' => 'approved', 'commission_pct' => 10,
        ]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'is_active' => true]);

        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'status' => Order::STATUS_PENDING,
            'subtotal' => 100, 'discount' => 0, 'shipping' => 0, 'total' => 100,
            'payment_method' => 'cod', 'payment_status' => 'pending',
            'shipping_address' => 'x', 'customer_name' => 'T', 'customer_phone' => '9',
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $product->id,
            'product_name' => $product->name, 'price' => 100, 'quantity' => 1,
        ]);

        return [$vendorUser, $order];
    }

    public function test_vendor_can_advance_confirmed_order_to_packed_and_shipped(): void
    {
        [$vendorUser, $order] = $this->setupVendorOrder();

        // pending → shipped blocked
        $this->actingAs($vendorUser, 'sanctum')
            ->postJson("/api/vendor/orders/{$order->order_number}/status", ['status' => 'shipped'])
            ->assertStatus(422);

        // admin confirms, vendor packs
        $order->update(['status' => Order::STATUS_CONFIRMED]);
        $this->actingAs($vendorUser, 'sanctum')
            ->postJson("/api/vendor/orders/{$order->order_number}/status", ['status' => 'packed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'packed');

        // vendor ships
        $this->actingAs($vendorUser, 'sanctum')
            ->postJson("/api/vendor/orders/{$order->order_number}/status", ['status' => 'shipped'])
            ->assertOk()
            ->assertJsonPath('data.status', 'shipped');
    }

    public function test_vendor_cannot_touch_orders_without_own_products(): void
    {
        [$vendorUser, $order] = $this->setupVendorOrder();

        // Remove the vendor product link → order no longer theirs
        $order->items()->update(['product_id' => Product::factory()->create(['vendor_id' => null])->id]);

        $this->actingAs($vendorUser, 'sanctum')
            ->postJson("/api/vendor/orders/{$order->order_number}/status", ['status' => 'packed'])
            ->assertNotFound();
    }
}

