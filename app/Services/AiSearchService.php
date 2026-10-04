<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Translates natural-language shopping queries into structured product filters
 * using an LLM, then runs the resulting query against the catalog.
 *
 * Falls back to keyword search if the LLM is unavailable.
 */
class AiSearchService
{
    public function search(string $query): array
    {
        $interpretation = $this->interpretQuery($query);

        $products = $this->runQuery($interpretation);

        return [
            'query' => $query,
            'interpretation' => $interpretation,
            'products' => $products,
        ];
    }

    /**
     * Ask the LLM to turn a natural-language query into structured filters.
     * Returns an array with keys: keywords, category, price_min, price_max, sort, on_sale, min_rating
     */
    public function interpretQuery(string $query): array
    {
        $categories = Category::where('is_active', true)->pluck('name')->implode(', ');

        $systemPrompt = <<<PROMPT
You are a product search assistant for an e-commerce marketplace called CityMarket.
Your job: convert the user's natural-language request into a JSON object describing structured search filters.

Available categories: {$categories}

Return ONLY a valid JSON object (no markdown, no explanation) with these keys (omit any you cannot determine):
- "keywords": string — the core product search terms (e.g. "running shoes")
- "category": string — one of the available categories above that best matches, or omit
- "price_min": number — minimum price, if mentioned
- "price_max": number — maximum price, if mentioned (e.g. "under 1000" => 1000)
- "sort": string — one of "price_asc", "price_desc", "rating", "newest" — inferred from words like "cheapest", "best", "lowest"
- "on_sale": boolean — true if the user wants deals/discounts/sale items
- "min_rating": number — minimum rating if they say "top rated", "best", etc. (default 4 if implied)

Examples:
"Show me best shoes under 1000" => {"keywords":"shoes","price_max":1000,"sort":"rating","min_rating":4}
"cheapest headphones" => {"keywords":"headphones","sort":"price_asc"}
"sale on electronics" => {"keywords":"","category":"Electronics","on_sale":true}
PROMPT;

        try {
            $response = Http::withToken(config('services.openai.key'))
                ->timeout(20)
                ->post(rtrim(config('services.openai.base_url'), '/') . '/chat/completions', [
                    'model' => config('services.openai.model'),
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $query],
                    ],
                    'max_tokens' => 200,
                    'temperature' => 0.2,
                ]);

            if (! $response->successful()) {
                Log::warning('AI search LLM call failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->fallbackInterpretation($query);
            }

            $content = $response->json('choices.0.message.content', '');

            // Strip markdown code fences if present
            $content = preg_replace('/^```(?:json)?|```$/m', '', trim($content));
            $content = trim($content);

            $parsed = json_decode($content, true);

            if (! is_array($parsed)) {
                Log::warning('AI search LLM returned non-JSON', ['content' => $content]);

                return $this->fallbackInterpretation($query);
            }

            $parsed['ai_interpreted'] = true;

            return $parsed;
        } catch (ConnectionException $e) {
            Log::warning('AI search LLM connection error', ['error' => $e->getMessage()]);

            return $this->fallbackInterpretation($query);
        }
    }

    /**
     * Keyword-only fallback when the LLM is unavailable.
     */
    protected function fallbackInterpretation(string $query): array
    {
        return [
            'keywords' => $query,
            'ai_interpreted' => false,
        ];
    }

    /**
     * Turn an interpretation into a Product query and return results.
     */
    protected function runQuery(array $interpretation): array
    {
        $filters = [];

        if (! empty($interpretation['keywords'])) {
            $filters['search'] = $interpretation['keywords'];
        }

        if (! empty($interpretation['category'])) {
            $cat = Category::where('name', $interpretation['category'])->first();
            if ($cat) {
                $filters['category'] = $cat->id;
            }
        }

        if (isset($interpretation['price_min'])) {
            $filters['price_min'] = (float) $interpretation['price_min'];
        }

        if (isset($interpretation['price_max'])) {
            $filters['price_max'] = (float) $interpretation['price_max'];
        }

        if (! empty($interpretation['sort'])) {
            $filters['sort'] = $interpretation['sort'];
        }

        if (! empty($interpretation['on_sale'])) {
            $filters['on_sale'] = true;
        }

        if (! empty($interpretation['min_rating'])) {
            $filters['min_rating'] = (float) $interpretation['min_rating'];
        }

        // Try the full interpretation first; if the LLM-inferred category or
        // rating filter yields nothing, relax those filters step by step so the
        // user still gets results (a 0-result AI search feels broken).
        $attempts = [ $filters ];
        if (! empty($filters['category'])) {
            $withoutCat = $filters; unset($withoutCat['category']); $attempts[] = $withoutCat;
        }
        if (! empty($filters['min_rating'])) {
            foreach ($attempts as $a) {
                $relaxed = $a; unset($relaxed['min_rating']);
                if ($relaxed !== $a) { $attempts[] = $relaxed; }
            }
        }

        foreach ($attempts as $attempt) {
            $results = $this->runFilteredQuery($attempt);
            if (! empty($results)) {
                return $results;
            }
        }

        return [];
    }

    protected function runFilteredQuery(array $filters): array
    {
        return Product::query()
            ->where('is_active', true)
            ->applyFilters($filters)
            ->with(['category:id,name,slug', 'brand:id,name,slug', 'images' => fn ($q) => $q->where('is_primary', true)])
            ->limit(24)
            ->get()
            ->toArray();
    }
}
