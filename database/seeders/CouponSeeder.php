<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            ['code' => 'WELCOME10', 'type' => 'percentage', 'value' => 10, 'min_order_amount' => 500, 'max_discount' => 500],
            ['code' => 'SAVE500', 'type' => 'fixed', 'value' => 500, 'min_order_amount' => 2000, 'max_discount' => null],
            ['code' => 'FLASH25', 'type' => 'percentage', 'value' => 25, 'min_order_amount' => 1000, 'max_discount' => 1000],
            ['code' => 'FREESHIP', 'type' => 'fixed', 'value' => 40, 'min_order_amount' => 0, 'max_discount' => null],
        ];

        foreach ($coupons as $c) {
            Coupon::updateOrCreate(
                ['code' => $c['code']],
                array_merge($c, [
                    'is_active' => true,
                    'used_count' => 0,
                    'usage_limit' => 100,
                    'starts_at' => now(),
                    'expires_at' => now()->addDays(60),
                ])
            );
        }
    }
}
