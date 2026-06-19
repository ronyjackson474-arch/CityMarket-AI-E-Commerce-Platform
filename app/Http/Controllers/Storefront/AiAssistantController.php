<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\AiSearchService;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function __construct(
        private AiSearchService $aiSearch,
    ) {}

    public function index()
    {
        return view('storefront.ai-assistant');
    }

    public function search(Request $request)
    {
        $data = $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:500'],
        ]);

        $result = $this->aiSearch->search($data['query']);

        return view('storefront.ai-assistant', [
            'result' => $result,
            'query' => $data['query'],
        ]);
    }
}
