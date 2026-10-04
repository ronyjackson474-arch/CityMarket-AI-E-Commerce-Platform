<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_notified_on_order_status_change(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->for($customer)->create(['status' => Order::STATUS_PENDING]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/orders/{$order->id}/status", ['status' => 'confirmed'])
            ->assertOk();

        $this->assertDatabaseHas('site_notifications', [
            'user_id' => $customer->id,
            'type' => 'order_status',
        ]);
    }

    public function test_admin_can_broadcast_notification(): void
    {
        User::factory()->count(3)->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/admin/notifications/broadcast', [
                'type' => 'promo', 'title' => 'Sale!',
            ])
            ->assertCreated();

        // 3 customers + the active admin user all receive the broadcast
        $this->assertEquals(4, \App\Models\SiteNotification::count());
    }
}
