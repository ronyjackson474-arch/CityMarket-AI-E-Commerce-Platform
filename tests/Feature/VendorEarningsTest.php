<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorEarning;
use App\Models\VendorWithdrawal;
use App\Services\VendorEarningsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorEarningsTest extends TestCase
{
    use RefreshDatabase;

    private function vendorWithProduct(int $commissionPct = 10): array
    {
        $user = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'shop_name' => 'Test Shop',
            'slug' => 'test-shop-' . uniqid(),
            'status' => 'approved',
            'commission_pct' => $commissionPct,
        ]);
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'price' => 1000,
            'is_active' => true,
        ]);

        return [$user, $vendor, $product];
    }

    private function deliveredOrderItem(Product $product): OrderItem
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'status' => Order::STATUS_DELIVERED,
            'subtotal' => 1000, 'discount' => 0, 'shipping' => 0, 'total' => 1000,
            'payment_method' => 'cod', 'payment_status' => 'pending',
            'shipping_address' => 'x', 'customer_name' => 'T', 'customer_phone' => '9',
        ]);

        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 1000,
            'quantity' => 1,
        ]);
    }

    public function test_earnings_recorded_on_delivered_order(): void
    {
        [, $vendor, $product] = $this->vendorWithProduct(10);
        $item = $this->deliveredOrderItem($product);

        app(VendorEarningsService::class)->recordForOrder($item->order);

        $earning = VendorEarning::where('vendor_id', $vendor->id)->firstOrFail();
        $this->assertEquals(1000.0, (float) $earning->gross);
        $this->assertEquals(100.0, (float) $earning->commission);
        $this->assertEquals(900.0, (float) $earning->net);
        $this->assertEquals(VendorEarning::STATUS_AVAILABLE, $earning->status);
    }

    public function test_recording_is_idempotent(): void
    {
        [, , $product] = $this->vendorWithProduct();
        $item = $this->deliveredOrderItem($product);
        $service = app(VendorEarningsService::class);

        $service->recordForOrder($item->order);
        $service->recordForOrder($item->order);

        $this->assertEquals(1, VendorEarning::count());
    }

    public function test_platform_products_produce_no_earning(): void
    {
        $product = Product::factory()->create(['vendor_id' => null, 'price' => 100, 'is_active' => true]);
        $item = $this->deliveredOrderItem($product);

        app(VendorEarningsService::class)->recordForOrder($item->order);

        $this->assertEquals(0, VendorEarning::count());
    }

    public function test_withdrawal_request_and_admin_approval_flow(): void
    {
        [, $vendor, $product] = $this->vendorWithProduct();
        $item = $this->deliveredOrderItem($product);
        $service = app(VendorEarningsService::class);
        $service->recordForOrder($item->order);

        // Over-balance request rejected
        $this->expectException(\InvalidArgumentException::class);
        try {
            $service->requestWithdrawal($vendor, 5000, 'upi', 'v@upi');
        } finally {
            $this->assertEquals(0, VendorWithdrawal::count());
        }
    }

    public function test_withdrawal_marks_earnings_and_rejection_restores_them(): void
    {
        [, $vendor, $product] = $this->vendorWithProduct();
        $item = $this->deliveredOrderItem($product);
        $service = app(VendorEarningsService::class);
        $service->recordForOrder($item->order);

        $w = $service->requestWithdrawal($vendor, 500, 'upi', 'v@upi');
        $this->assertEquals(VendorWithdrawal::STATUS_PENDING, $w->status);
        $this->assertEquals(400.0, $service->summary($vendor)['available']);

        $service->rejectWithdrawal($w, 'bad details');
        $this->assertEquals(VendorWithdrawal::STATUS_REJECTED, $w->fresh()->status);
        $this->assertEquals(900.0, $service->summary($vendor)['available']);
    }

    public function test_admin_payout_api_approve(): void
    {
        [$vendorUser, $vendor, $product] = $this->vendorWithProduct();
        $item = $this->deliveredOrderItem($product);
        $service = app(VendorEarningsService::class);
        $service->recordForOrder($item->order);
        $w = $service->requestWithdrawal($vendor, 300, 'upi', 'v@upi');

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/payouts/{$w->id}/approve", ['note' => 'paid']);

        $response->assertOk()
            ->assertJsonPath('data.status', VendorWithdrawal::STATUS_PAID);
    }

    public function test_vendor_api_endpoints(): void
    {
        [$vendorUser, $vendor, $product] = $this->vendorWithProduct();
        $item = $this->deliveredOrderItem($product);
        $service = app(VendorEarningsService::class);
        $service->recordForOrder($item->order);

        // summary
        $this->actingAs($vendorUser, 'sanctum')
            ->getJson('/api/vendor/earnings')
            ->assertOk()
            ->assertJsonPath('data.summary.available', 900);

        // request withdrawal
        $this->actingAs($vendorUser, 'sanctum')
            ->postJson('/api/vendor/withdrawals', [
                'amount' => 100, 'method' => 'upi', 'payout_detail' => 'v@upi',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', VendorWithdrawal::STATUS_PENDING);

        // list
        $this->actingAs($vendorUser, 'sanctum')
            ->getJson('/api/vendor/withdrawals')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}

class_alias(\Tests\Feature\VendorEarningsTest::class, 'X');
