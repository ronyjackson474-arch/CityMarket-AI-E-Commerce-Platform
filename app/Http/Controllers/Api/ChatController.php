<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\AiAssistantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __construct(
        private AiAssistantService $ai
    ) {}

    public function conversations(Request $request)
    {
        $conversations = $request->user()
            ->conversations()
            ->latest('updated_at')
            ->limit(20)
            ->get(['id', 'status', 'context', 'created_at', 'updated_at']);

        return response()->json(['data' => $conversations]);
    }

    public function messages(Request $request, int $conversationId)
    {
        $conversation = ChatConversation::where('id', $conversationId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $messages = ChatMessage::where('conversation_id', $conversation->id)
            ->orderBy('id')
            ->limit(100)
            ->get(['id', 'role', 'content', 'created_at']);

        return response()->json(['data' => $messages]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'conversation_id' => 'nullable|integer|exists:chat_conversations,id',
        ]);

        $user = $request->user();

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
                'context' => ['topic' => 'general'],
            ]);
        }

        // Store user message
        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        // Get AI response
        try {
            $reply = $this->ai->chat($conversation, $validated['message']);
        } catch (\Throwable $e) {
            Log::error('AI chat error: ' . $e->getMessage());
            $reply = "I'm sorry, I'm having trouble right now. Could you please try again in a moment?";
        }

        $assistantMessage = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $reply,
        ]);

        $conversation->touch();

        return response()->json([
            'conversation_id' => $conversation->id,
            'message' => $reply,
            'assistant_message_id' => $assistantMessage->id,
        ]);
    }
}
