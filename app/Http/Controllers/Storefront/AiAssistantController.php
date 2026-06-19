<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\AiAssistantService;
use App\Services\AiSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AiAssistantController extends Controller
{
    public function __construct(
        private AiSearchService $aiSearch,
        private AiAssistantService $aiAssistant,
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

    /**
     * Handle web-based support chat (session auth instead of Sanctum).
     */
    public function supportSend(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'conversation_id' => ['nullable', 'integer'],
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Please log in to chat.'], 401);
        }

        // Get or create conversation
        if (!empty($validated['conversation_id'])) {
            $conversation = ChatConversation::where('id', $validated['conversation_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
        } else {
            $conversation = ChatConversation::create([
                'user_id' => $user->id,
                'session_id' => session()->getId(),
                'status' => 'active',
                'context' => ['topic' => 'support'],
            ]);
        }

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        try {
            $reply = $this->aiAssistant->chat($conversation, $validated['message']);
        } catch (\Throwable $e) {
            Log::error('Support chat AI error: ' . $e->getMessage());
            $reply = "I'm sorry, I'm having trouble right now. Could you please try again?";
        }

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $reply,
        ]);

        $conversation->touch();

        return response()->json([
            'conversation_id' => $conversation->id,
            'message' => $reply,
        ]);
    }
}
