<?php

namespace Tests\Feature;

use App\Models\PolicyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Set gemini API key to null by default in tests to test local deterministic engine quickly
        config(['services.gemini.api_key' => null]);
    }

    /**
     * Test greeting response and voice text.
     */
    public function test_chatbot_greeting(): void
    {
        $response = $this->postJson('/api/chatbot', [
            'message' => 'Hello Maya!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('intent', 'greeting')
            ->assertJsonStructure(['reply', 'voice_text', 'quick_actions']);
    }

    /**
     * Test plan comparison response.
     */
    public function test_chatbot_plans_inquiry(): void
    {
        $response = $this->postJson('/api/chatbot', [
            'message' => 'What is the Plus Plan?',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('intent', 'plan_info');

        $this->assertStringContainsString('₹20 per travel day', $response->json('reply'));
    }

    /**
     * Test pricing calculation explanation.
     */
    public function test_chatbot_pricing_explanation(): void
    {
        $response = $this->postJson('/api/chatbot', [
            'message' => 'How are the premiums calculated?',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('intent', 'calculation_explained');

        $this->assertStringContainsString('Formula:', $response->json('reply'));
    }

    /**
     * Test policy lookup through chatbot.
     */
    public function test_chatbot_track_policy(): void
    {
        $policy = PolicyRequest::create([
            'client_request_id' => '748291',
            'agent_id' => '1001',
            'customer_name' => 'Michael Scott',
            'date_of_birth' => '1985-03-15',
            'destination' => 'DOMESTIC',
            'trip_start' => '2026-11-01',
            'trip_end' => '2026-11-05',
            'plan_code' => 'PLUS',
            'sum_insured' => 300000,
            'premium' => 125.00,
            'status' => 'ISSUED',
            'policy_number' => 'POL-SCRANTON-777',
        ]);

        $response = $this->postJson('/api/chatbot', [
            'message' => "Can you track policy #{$policy->id}?",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('intent', 'policy_found');

        $this->assertStringContainsString('POL-SCRANTON-777', $response->json('reply'));
        $this->assertStringContainsString('Michael Scott', $response->json('reply'));
    }

    /**
     * Test Gemini API response integration when API key is active.
     */
    public function test_chatbot_gemini_api_integration(): void
    {
        config(['services.gemini.api_key' => 'AQ.TEST-MOCK-KEY']);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Hello! I am Maya powered by Gemini, ready to assist your travel insurance needs.']
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->postJson('/api/chatbot', [
            'message' => 'Tell me a travel tip!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('intent', 'gemini_response')
            ->assertSee('Maya powered by Gemini');
    }

    /**
     * Test Maya can converse on open-ended general AI topics (e.g., science, coding, philosophy).
     */
    public function test_chatbot_can_talk_about_everything_like_general_ai(): void
    {
        config(['services.gemini.api_key' => 'AQ.TEST-MOCK-KEY']);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Stars twinkle due to atmospheric refraction of starlight as it passes through shifting air layers. Here is a Python function for you: def reverse(s): return s[::-1]']
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->postJson('/api/chatbot', [
            'message' => 'Why do stars twinkle and can you write a python function to reverse a string?',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('intent', 'gemini_response')
            ->assertSee('atmospheric refraction')
            ->assertSee('def reverse(s)');
    }

    /**
     * Test offline fallback when no API key is provided returns a conversational response.
     */
    public function test_chatbot_offline_fallback_for_general_questions(): void
    {
        config(['services.gemini.api_key' => null]);

        $response = $this->postJson('/api/chatbot', [
            'message' => 'Tell me about the universe and quantum mechanics.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('intent', 'fallback')
            ->assertSee('Maya');
    }
}
