<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductImage>
 */
class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        $seed = fake()->uuid();

        return [
            'image_path' => 'https://picsum.photos/seed/' . $seed . '/600/600',
            'is_primary' => false,
            'position' => fake()->numberBetween(0, 10),
        ];
    }

    public function primary(): static
    {
        return $this->state(fn () => ['is_primary' => true, 'position' => 0]);
    }
}
