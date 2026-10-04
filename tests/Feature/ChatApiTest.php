<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_send_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/chat/send', [
                'message' => 'Hello, I need help finding a product',
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['conversation_id', 'message']);
    }

    public function test_message_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/chat/send', [
                'message' => '',
            ]);

        $response->assertStatus(422);
    }

    public function test_guest_cannot_send_message(): void
    {
        $response = $this->postJson('/api/chat/send', [
            'message' => 'Hello',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_list_conversations(): void
    {
        $user = User::factory()->create();

        // Create a conversation by sending a message first
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/chat/send', ['message' => 'Hi']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/chat/conversations');

        $response->assertOk();
        $response->assertJsonStructure(['data']);
    }
}
