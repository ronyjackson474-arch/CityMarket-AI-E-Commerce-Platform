<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        Product::factory()->count(3)->create();

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name', 'slug', 'price']],
                'current_page',
                'total',
            ]);
    }

    public function test_can_filter_by_price_range(): void
    {
        Product::factory()->create(['price' => 100, 'is_active' => true]);
        Product::factory()->create(['price' => 500, 'is_active' => true]);
        Product::factory()->create(['price' => 1000, 'is_active' => true]);

        $response = $this->getJson('/api/products?price_min=200&price_max=600')
            ->assertOk();

        $prices = collect($response->json('data'))->pluck('price')->map(fn ($p) => (float) $p);

        $this->assertTrue($prices->every(fn ($p) => $p >= 200 && $p <= 600));
    }

    public function test_can_search_products(): void
    {
        Product::factory()->create(['name' => 'Nike Running Shoes', 'is_active' => true]);
        Product::factory()->create(['name' => 'Adidas Football', 'is_active' => true]);

        $response = $this->getJson('/api/products?search=Nike')
            ->assertOk();

        $this->assertCount(1, $response->json('data'));
        $this->assertStringContainsString('Nike', $response->json('data.0.name'));
    }

    public function test_can_get_product_by_slug(): void
    {
        $product = Product::factory()->create(['slug' => 'test-product']);
        Product::factory()->create(['category_id' => $product->category_id]); // related

        $this->getJson('/api/products/test-product')
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonStructure(['data' => ['id', 'name', 'images'], 'related']);
    }

    public function test_returns_404_for_missing_product(): void
    {
        $this->getJson('/api/products/non-existent')->assertNotFound();
    }

    public function test_inactive_products_are_hidden(): void
    {
        Product::factory()->create(['is_active' => false, 'name' => 'Hidden Product']);

        $response = $this->getJson('/api/products')->assertOk();

        $names = collect($response->json('data'))->pluck('name');
        $this->assertFalse($names->contains('Hidden Product'));
    }

    public function test_can_list_categories_as_tree(): void
    {
        $parent = Category::factory()->create(['name' => 'Parent Cat']);
        Category::factory()->create(['parent_id' => $parent->id, 'name' => 'Child Cat']);

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name', 'children']]]);
    }

    public function test_can_get_featured_products(): void
    {
        Product::factory()->create(['is_featured' => true, 'is_active' => true]);
        Product::factory()->create(['is_featured' => false, 'is_active' => true]);

        $response = $this->getJson('/api/products/featured')->assertOk();

        $this->assertTrue(collect($response->json('data'))->every(fn ($p) => $p['is_featured']));
    }
}
