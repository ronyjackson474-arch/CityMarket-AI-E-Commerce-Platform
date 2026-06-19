<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
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
        ];

        $recentOrders = Order::with('user:id,name', 'items')->latest()->limit(8)->get();
        $lowStockProducts = Product::where('stock', '<', 10)->with('category:id,name')->limit(5)->get();

        // Simple sales chart: last 7 days
        $salesData = Order::selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->where('status', '!=', 'cancelled')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'lowStockProducts', 'salesData'));
    }
}
