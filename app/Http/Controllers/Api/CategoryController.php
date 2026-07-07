<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('position')])
            ->orderBy('position');

        $categories = $query->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function show(string $slug)
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('position'), 'parent'])
            ->first();

        if (! $category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        return response()->json([
            'data' => $category,
        ]);
    }
}
