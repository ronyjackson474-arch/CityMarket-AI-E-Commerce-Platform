<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 99, 9999);
        $onSale = fake()->boolean(40);

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraphs(2, true),
            'specifications' => json_encode([
                'warranty' => fake()->randomElement(['6 months', '1 year', '2 years']),
                'color' => fake()->safeColorName(),
                'material' => fake()->randomElement(['Cotton', 'Plastic', 'Metal', 'Leather']),
            ]),
            'sku' => strtoupper(fake()->bothify('??-####')),
            'price' => $price,
            'sale_price' => $onSale ? round($price * fake()->randomFloat(2, 0.5, 0.9), 2) : null,
            'stock' => fake()->numberBetween(0, 100),
            'rating' => fake()->randomFloat(1, 3, 5),
            'review_count' => fake()->numberBetween(0, 500),
            'is_featured' => fake()->boolean(20),
            'is_flash_deal' => fake()->boolean(15),
            'is_active' => true,
            'flash_deal_ends_at' => fake()->boolean(15) ? now()->addHours(fake()->numberBetween(1, 12)) : null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (\App\Models\Product $product) {
            if (! $product->images()->exists()) {
                \App\Models\ProductImage::factory()
                    ->count(fake()->numberBetween(1, 4))
                    ->create(['product_id' => $product->id]);
            }
        });
    }
}
