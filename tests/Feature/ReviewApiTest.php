<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_list_reviews_for_product(): void
    {
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
        ]);

        Review::factory()->count(3)->create([
            'product_id' => $product->id,
            'user_id' => fn () => User::factory()->create()->id,
        ]);

        $response = $this->getJson("/api/products/{$product->slug}/reviews");

        $response->assertOk();
        $response->assertJsonStructure(['data']);
    }

    public function test_authenticated_user_can_post_review(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'rating' => 5,
                'title' => 'Great product',
                'body' => 'Really loved it!',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);
    }

    public function test_user_cannot_review_twice(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
        ]);

        Review::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'rating' => 4,
            ]);

        $response->assertStatus(400);
    }

    public function test_review_rating_must_be_valid(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'rating' => 10,
            ]);

        $response->assertStatus(422);
    }

    public function test_guest_cannot_post_review(): void
    {
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
        ]);

        $response = $this->postJson("/api/products/{$product->slug}/reviews", [
            'rating' => 5,
        ]);

        $response->assertStatus(401);
    }
}
