<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAdminAssistantService
{
    private function apiKey(): string
    {
        return env('OPENAI_API_KEY');
    }

    private function baseUrl(): string
    {
        return env('OPENAI_BASE_URL', 'https://api.openai.com/v1');
    }

    private function model(): string
    {
        return env('AI_MODEL', 'drytis/kimi-k2.5');
    }

    /**
     * Answer an admin's natural-language question about store performance.
     */
    public function query(string $question): array
    {
        $insights = $this->gatherInsights($question);

        $prompt = $this->buildPrompt($question, $insights);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey(),
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post($this->baseUrl() . '/chat/completions', [
                    'model' => $this->model(),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are CityMarket AI Admin Assistant. You help store administrators understand their business data. Answer concisely with actionable insights. Use the provided data context. Format numbers with ₹ symbol.',
                        ],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens' => 600,
                    'temperature' => 0.4,
                ]);

            if ($response->failed()) {
                Log::error('AI admin query failed', ['status' => $response->status(), 'body' => $response->body()]);
                return [
                    'answer' => $this->fallbackAnswer($question, $insights),
                    'data' => $insights,
                ];
            }

            $answer = $response->json('choices.0.message.content');

            return [
                'answer' => $answer ?? $this->fallbackAnswer($question, $insights),
                'data' => $insights,
            ];
        } catch (\Throwable $e) {
            Log::error('AI admin exception: ' . $e->getMessage());
            return [
                'answer' => $this->fallbackAnswer($question, $insights),
                'data' => $insights,
            ];
        }
    }

    /**
     * Gather relevant business data based on the question keywords.
     */
    private function gatherInsights(string $question): array
    {
        $q = strtolower($question);
        $data = [];

        // Always include today's summary
        $today = now()->startOfDay();
        $data['today'] = [
            'orders' => Order::where('created_at', '>=', $today)->count(),
            'revenue' => (float) Order::where('created_at', '>=', $today)
                ->whereNotIn('status', [Order::STATUS_CANCELLED])
                ->sum('total'),
            'new_users' => User::where('created_at', '>=', $today)->count(),
        ];

        // Sales this month
        $monthStart = now()->startOfMonth();
        $data['this_month'] = [
            'orders' => Order::where('created_at', '>=', $monthStart)->count(),
            'revenue' => (float) Order::where('created_at', '>=', $monthStart)
                ->whereNotIn('status', [Order::STATUS_CANCELLED])
                ->sum('total'),
        ];

        // Best sellers
        $data['best_sellers'] = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as sold'), DB::raw('SUM(order_items.quantity * order_items.price) as revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('sold')
            ->limit(5)
            ->get()
            ->toArray();

        // Low stock
        $data['low_stock'] = Product::where('is_active', true)
            ->where('stock', '<=', 10)
            ->orderBy('stock')
            ->limit(5)
            ->get(['name', 'stock', 'price'])
            ->toArray();

        // Order status breakdown
        $data['order_status_breakdown'] = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Total stats
        $data['totals'] = [
            'products' => Product::where('is_active', true)->count(),
            'users' => User::count(),
            'orders' => Order::count(),
        ];

        return $data;
    }

    private function buildPrompt(string $question, array $data): string
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);

        return "Question from admin: \"{$question}\"\n\nCurrent business data:\n{$json}\n\nProvide a clear, actionable answer based on this data.";
    }

    private function fallbackAnswer(string $question, array $data): string
    {
        $q = strtolower($question);

        if (str_contains($q, 'today') || str_contains($q, 'sales')) {
            $t = $data['today'];
            return "Today: {$t['orders']} orders, ₹" . number_format($t['revenue'], 0) . " revenue, {$t['new_users']} new users.";
        }

        if (str_contains($q, 'best') || str_contains($q, 'selling') || str_contains($q, 'popular')) {
            $sellers = collect($data['best_sellers'])->take(3);
            if ($sellers->isNotEmpty()) {
                $list = $sellers->map(fn($s) => "{$s->name} ({$s->sold} sold)")->implode(', ');
                return "Top sellers: {$list}";
            }
        }

        if (str_contains($q, 'stock') || str_contains($q, 'inventory')) {
            $lowStock = collect($data['low_stock']);
            if ($lowStock->isNotEmpty()) {
                $list = $lowStock->map(fn($p) => "{$p['name']} ({$p['stock']} left)")->implode(', ');
                return "Low stock alert: {$list}";
            }
            return "All products are well-stocked.";
        }

        if (str_contains($q, 'month')) {
            $m = $data['this_month'];
            return "This month: {$m['orders']} orders, ₹" . number_format($m['revenue'], 0) . " revenue.";
        }

        $t = $data['today'];
        return "Here's a quick summary: {$t['orders']} orders today, ₹" . number_format($t['revenue'], 0) . " in revenue. Total: {$data['totals']['products']} products, {$data['totals']['users']} users, {$data['totals']['orders']} orders.";
    }
}
