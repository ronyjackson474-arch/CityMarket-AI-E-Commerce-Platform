<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-' . strtoupper(fake()->bothify('??##??##')),
            'status' => fake()->randomElement(['pending', 'confirmed', 'shipped', 'delivered']),
            'subtotal' => fake()->randomFloat(2, 100, 5000),
            'discount' => 0,
            'shipping' => 40,
            'total' => fake()->randomFloat(2, 100, 5040),
            'payment_method' => fake()->randomElement(['cod', 'upi', 'card']),
            'payment_status' => fake()->randomElement(['pending', 'paid']),
            'shipping_address' => fake()->address(),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->numerify('+91##########'),
            'notes' => null,
            'coupon_code' => null,
        ];
    }
}
