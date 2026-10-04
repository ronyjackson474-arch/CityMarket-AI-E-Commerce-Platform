<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantService
{
    private function apiKey(): string
    {
        return config('services.openai.key') ?? env('OPENAI_API_KEY', '');
    }

    private function baseUrl(): string
    {
        return rtrim(config('services.openai.base_url') ?? env('OPENAI_BASE_URL', 'https://api.openai.com/v1'), '/');
    }

    private function model(): string
    {
        return config('services.openai.model') ?? env('AI_MODEL', 'drytis/kimi-k2.5');
    }

    /**
     * Main chat function for the AI shopping assistant.
     * Provides product discovery, recommendations, and support.
     */
    public function chat(ChatConversation $conversation, string $userMessage): string
    {
        // Build context from conversation history
        $history = $conversation->messages()
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        $messages = [];

        // System prompt
        $messages[] = [
            'role' => 'system',
            'content' => $this->buildSystemPrompt($conversation),
        ];

        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey(),
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post($this->baseUrl() . '/chat/completions', [
                    'model' => $this->model(),
                    'messages' => $messages,
                    'max_tokens' => 500,
                    'temperature' => 0.7,
                ]);

            if ($response->failed()) {
                Log::error('AI chat API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->fallbackResponse($userMessage);
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                return $this->fallbackResponse($userMessage);
            }

            return $content;
        } catch (\Throwable $e) {
            Log::error('AI chat exception: ' . $e->getMessage());
            return $this->fallbackResponse($userMessage);
        }
    }

    /**
     * Build the system prompt with product catalog context.
     */
    private function buildSystemPrompt(ChatConversation $conversation): string
    {
        $productCount = Product::active()->count();
        $categories = \App\Models\Category::whereNull('parent_id')->where('is_active', true)
            ->with('children:id,parent_id,name,slug')
            ->orderBy('position')
            ->get(['id', 'name', 'slug']);

        $categoryList = $categories->map(fn($c) => $categories->count() > 0 ? "- {$c->name}" : '')->implode("\n");

        return <<<PROMPT
You are CityMarket AI Shopping Assistant, a friendly and helpful shopping companion for CityMarket e-commerce platform.
Your role:
- Help customers discover products, compare options, and make purchasing decisions
- Answer questions about orders, shipping, returns, payments, and wallet
- Provide personalized recommendations based on the customer's interests
- Keep responses concise and conversational (max 3-4 sentences)

Catalog context:
- {$productCount} active products available
- Categories: {$categoryList}

Payment methods: Cash on Delivery (COD), UPI, Credit/Debit Card, Wallet
Shipping: Free shipping on orders above ₹500, standard delivery 3-5 days
Returns: 7-day return policy on most items

Always be warm, professional, and customer-focused. Use a helpful tone.
PROMPT;
    }

    /**
     * Fallback response when AI is unavailable.
     */
    private function fallbackResponse(string $userMessage): string
    {
        $lower = strtolower($userMessage);

        if (str_contains($lower, 'track') || str_contains($lower, 'order')) {
            return "You can track your orders from the Orders section. Go to My Orders to see live status updates and tracking information.";
        }

        if (str_contains($lower, 'return') || str_contains($lower, 'refund')) {
            return "We offer a 7-day easy return policy. You can initiate a return from your order details page. Refunds are processed within 5-7 business days.";
        }

        if (str_contains($lower, 'wallet')) {
            return "Your CityMarket wallet lets you pay for orders and earn cashback! You earn 1% cashback on every order automatically.";
        }

        if (str_contains($lower, 'pay') || str_contains($lower, 'cod') || str_contains($lower, 'upi')) {
            return "We accept Cash on Delivery (COD), UPI, Credit/Debit Cards, and CityMarket Wallet. Choose what's convenient for you at checkout!";
        }

        // Keyword-based product suggestions (grouped OR so active() scope holds)
        $suggestions = Product::active()
            ->where(function ($q) use ($userMessage) {
                $q->where('name', 'like', "%{$userMessage}%")
                  ->orWhere('description', 'like', "%{$userMessage}%");
            })
            ->limit(3)
            ->get(['name', 'sale_price', 'price']);

        if ($suggestions->isNotEmpty()) {
            $list = $suggestions->map(function ($p) {
                $price = $p->sale_price ?? $p->price;
                return "- {$p->name} — ₹{$price}";
            })->implode("\n");
            return "I found these products that might interest you:\n{$list}\n\nWould you like more details on any of these?";
        }

        return "I'd be happy to help! You can browse our categories or ask me about specific products, your orders, shipping, or payment options. What are you looking for today?";
    }
}
