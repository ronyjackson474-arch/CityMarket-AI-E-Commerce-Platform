<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_add_to_wishlist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/wishlist/add', ['product_id' => $product->id]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_user_can_view_wishlist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
        ]);
        $user->wishlist()->attach($product);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/wishlist');

        $response->assertOk();
        $response->assertJsonFragment(['id' => $product->id]);
    }

    public function test_user_can_remove_from_wishlist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
        ]);
        $user->wishlist()->attach($product);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/wishlist/remove', ['product_id' => $product->id]);

        $response->assertOk();
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_guest_cannot_access_wishlist(): void
    {
        $response = $this->getJson('/api/wishlist');
        $response->assertStatus(401);
    }
}
