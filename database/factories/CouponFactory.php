<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['percentage', 'fixed']);

        return [
            'code' => strtoupper(fake()->bothify('SAVE##??')),
            'type' => $type,
            'value' => $type === 'percentage' ? fake()->numberBetween(5, 30) : fake()->randomFloat(2, 50, 500),
            'min_order_amount' => fake()->randomFloat(2, 0, 500),
            'max_discount' => $type === 'percentage' ? fake()->randomFloat(2, 100, 1000) : null,
            'usage_limit' => fake()->optional()->numberBetween(10, 100),
            'used_count' => 0,
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
            'is_active' => true,
        ];
    }
}
