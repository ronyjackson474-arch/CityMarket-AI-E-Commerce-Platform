<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::where('role', 'customer')->count(),
            'orders' => Order::count(),
            'revenue' => (float) Order::whereIn('status', ['confirmed', 'packed', 'shipped', 'delivered'])->sum('total'),
            'products' => Product::count(),
            'low_stock' => Product::where('stock', '<', 10)->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'pending_vendors' => Vendor::where('status', 'pending')->count(),
            'vendors' => Vendor::where('status', 'approved')->count(),
        ];

        $recentOrders = Order::with('user:id,name', 'items')->latest()->limit(8)->get();
        $lowStockProducts = Product::where('stock', '<', 10)->with('category:id,name')->limit(5)->get();

        // Sales trend: last 30 days (Chart.js line chart)
        $salesData = Order::selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->where('status', '!=', 'cancelled')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        // Fill missing days with zeros so the line is continuous
        $salesByDate = $salesData->keyBy(fn ($d) => $d->date);
        $salesSeries = collect(range(29, 0))->map(function ($i) use ($salesByDate) {
            $date = now()->subDays($i)->toDateString();
            $day = $salesByDate->get($date);

            return [
                'date' => $date,
                'total' => (float) ($day->total ?? 0),
                'count' => (int) ($day->count ?? 0),
            ];
        });

        // Orders by status (doughnut)
        $ordersByStatus = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('admin.dashboard', compact(
            'stats', 'recentOrders', 'lowStockProducts', 'salesSeries', 'ordersByStatus'
        ));
    }
}
