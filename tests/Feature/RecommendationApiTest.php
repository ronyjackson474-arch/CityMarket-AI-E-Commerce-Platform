<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_recommendations_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        Product::factory()->count(5)->create([
            'category_id' => Category::factory()->create()->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/recommendations');

        $response->assertOk();
        $response->assertJsonStructure(['data', 'meta']);
    }

    public function test_guest_cannot_access_recommendations(): void
    {
        $response = $this->getJson('/api/recommendations');
        $response->assertStatus(401);
    }

    public function test_recommendations_respect_limit(): void
    {
        $user = User::factory()->create();
        Product::factory()->count(15)->create([
            'category_id' => Category::factory()->create()->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/recommendations?limit=5');

        $response->assertOk();
        $this->assertLessThanOrEqual(5, count($response->json('data')));
    }
}
