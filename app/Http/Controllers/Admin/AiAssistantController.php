<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AiAdminAssistantService;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function index()
    {
        return view('admin.ai-assistant.index');
    }

    public function query(Request $request, AiAdminAssistantService $ai)
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:500'],
        ]);

        $result = $ai->query($validated['question']);

        return response()->json($result);
    }
}
