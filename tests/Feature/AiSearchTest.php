<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\AiSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_search_endpoint_returns_results(): void
    {
        // Seed products for the query to match
        Product::factory()->create([
            'name' => 'Nike Running Shoes',
            'price' => 3000,
            'rating' => 4.5,
            'is_active' => true,
        ]);
        Product::factory()->create([
            'name' => 'Adidas Sneakers',
            'price' => 4000,
            'rating' => 4.5,
            'is_active' => true,
        ]);

        // Mock the LLM HTTP call
        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'keywords' => 'shoes',
                                'price_max' => 5000,
                                'sort' => 'rating',
                                'min_rating' => 4,
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/ai/search', [
            'query' => 'best shoes under 5000',
        ])
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => ['query', 'interpretation', 'products'],
            ]);

        $this->assertTrue($response->json('data.interpretation.ai_interpreted'));
        $this->assertGreaterThan(0, count($response->json('data.products')));
    }

    public function test_ai_search_validates_query(): void
    {
        $this->postJson('/api/ai/search', ['query' => ''])->assertUnprocessable();
    }

    public function test_ai_search_falls_back_on_llm_error(): void
    {
        Product::factory()->create(['name' => 'Headphones', 'is_active' => true]);

        Http::fake([
            '*/chat/completions' => Http::response(['error' => 'unavailable'], 500),
        ]);

        $response = $this->postJson('/api/ai/search', [
            'query' => 'Headphones',
        ])->assertOk();

        $this->assertFalse($response->json('data.interpretation.ai_interpreted'));
    }

    public function test_ai_search_service_returns_interpretation(): void
    {
        $service = app(AiSearchService::class);

        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"keywords":"laptop","price_max":50000}']],
                ],
            ], 200),
        ]);

        $result = $service->search('laptop under 50000');

        $this->assertArrayHasKey('interpretation', $result);
        $this->assertArrayHasKey('products', $result);
        $this->assertEquals('laptop under 50000', $result['query']);
    }
}
