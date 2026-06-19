<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiSearchService;
use Illuminate\Http\Request;

class AiSearchController extends Controller
{
    public function __construct(
        private AiSearchService $aiSearch,
    ) {}

    public function search(Request $request)
    {
        $data = $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:500'],
        ]);

        $result = $this->aiSearch->search($data['query']);

        return response()->json([
            'message' => 'Here are the products I found for you.',
            'data' => $result,
        ]);
    }
}
