<?php

namespace App\Services;

use App\Models\PolicyRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotAssistant
{
    /**
     * Process user message and return versatile AI assistant reply with human voice text.
     *
     * @param string $message
     * @param array $history
     * @return array{
     *     reply: string,
     *     voice_text: string,
     *     intent: string,
     *     quick_actions: array<string>,
     *     policy_data: array|null
     * }
     */
    public static function respond(string $message, array $history = []): array
    {
        $clean = strtolower(trim($message));

        // 1. Policy Tracking lookup by ID or Ref (e.g. "track 1", "policy #1", "status of 748291", "check policy 2")
        // Trigger direct DB lookup if user asks to track/check policy or provides a policy ID
        $isTrackingQuery = self::contains($clean, ['track', 'status', 'check', 'where is', 'find', 'lookup', 'policy #', 'application #', 'req-', 'pol-']) 
            || preg_match('/^\s*#?[0-9]{1,8}\s*$/', $message);

        $extractedId = self::extractIdentifier($message);
        if ($extractedId !== null && $isTrackingQuery) {
            $policy = self::findPolicy($extractedId);
            if ($policy) {
                return self::formatPolicyTrackingResponse($policy);
            }
        }

        // 2. Direct intent: Specific tracking prompt when user asks to track but didn't provide ID
        if (self::contains($clean, ['where is my policy', 'check status', 'tracking']) && !self::contains($clean, ['how', 'plan', 'price', 'tell', 'what', 'who', 'why'])) {
            return [
                'reply' => "I would be delighted to check your policy status! Please share your Policy Request ID (like #1) or your 6-digit Application ID (like 748291), and I'll pull up the latest status right away.",
                'voice_text' => "I would be happy to check your policy status. Please tell me your policy ID or application number.",
                'intent' => 'track_prompt',
                'quick_actions' => ['Track Policy #1', 'Check recent requests', 'Show Insurance Plans'],
                'policy_data' => null,
            ];
        }

        // 3. AI Brain (Gemini): Answers EVERYTHING (General knowledge, science, coding, stories, advice, casual banter, travel, insurance)
        $geminiKey = config('services.gemini.api_key');
        if (!empty($geminiKey)) {
            $geminiResponse = self::callGemini($message, $history, $geminiKey);
            if ($geminiResponse !== null) {
                return $geminiResponse;
            }
        }

        // 4. Local Deterministic Fallback Engine (Active when offline or in test suite with null API key)
        return self::handleLocalFallback($clean);
    }

    /**
     * Call Google Gemini API to get a versatile, human-like AI assistant reply.
     */
    private static function callGemini(string $message, array $history, string $apiKey): ?array
    {
        $systemPrompt = "You are Maya, an exceptionally intelligent, warm, and versatile AI companion and assistant on PolicyFlow.\n\n" .
            "CORE CAPABILITIES:\n" .
            "- You are a full-fledged AI assistant capable of discussing and answering questions on ANY topic in the universe: science, technology, world history, arts, creative writing, poetry, philosophy, coding, travel destinations, daily advice, casual banter, humor, and general knowledge.\n" .
            "- Always respond naturally, warmly, and intelligently like a true advanced conversational AI (like ChatGPT or Gemini). Never refuse non-insurance topics or restrict the user to travel insurance.\n\n" .
            "POLICYFLOW INSURANCE EXPERTISE:\n" .
            "You are also the expert AI ambassador for PolicyFlow travel insurance:\n" .
            "- Basic Plan: ₹12 per travel day (essential medical emergencies, flight delays).\n" .
            "- Plus Plan: ₹20 per travel day (most popular; high hospitalization limits, checked baggage loss/delay, trip curtailment).\n" .
            "- Premium Plan: ₹30 per travel day (maximum ₹500,000 coverage, full trip cancellation, worldwide evacuation, adventure sports).\n" .
            "- Pricing Formula: Base Daily Rate × Inclusive Travel Days × Age Multiplier × Destination Multiplier × Coverage Multiplier.\n" .
            "- Multipliers:\n" .
            "  * Age: 0–17 (1.10x), 18–60 (1.00x), 61–70 (1.50x), 71+ (2.00x)\n" .
            "  * Destination: Domestic (1.00x), International (1.80x)\n" .
            "  * Coverage (Sum Insured): ₹100,000 (1.00x), ₹300,000 (1.25x), ₹500,000 (1.50x)\n" .
            "- Policy Statuses: VALIDATED (checks passed), PENDING (sent to insurer), ISSUED (active with policy number), FAILED (rejected).\n\n" .
            "RESPONSE GUIDELINES:\n" .
            "- If the user asks about travel insurance, plans, or pricing, provide accurate PolicyFlow guidance.\n" .
            "- If the user asks about ANYTHING ELSE, give a rich, comprehensive, and engaging answer as an advanced AI companion.\n" .
            "- Format with clean markdown (bolding, lists, code blocks when requested). Keep tone friendly, empathetic, and natural for human speech.";

        $contents = [];
        // Add conversation history if available
        foreach (array_slice($history, -6) as $item) {
            $role = ($item['role'] ?? '') === 'user' ? 'user' : 'model';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $item['content'] ?? '']],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]],
        ];

        // Valid Gemini models supporting generateContent on this key
        $preferredModel = config('services.gemini.model', 'gemini-3.5-flash-lite');
        $modelsToTry = array_unique([
            $preferredModel,
            'gemini-3.5-flash-lite',
            'gemini-3.5-flash',
            'gemini-3.1-flash-lite',
            'gemini-flash-latest',
            'gemma-4-26b-a4b-it',
        ]);

        foreach ($modelsToTry as $model) {
            try {
                $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $apiKey,
                ])
                ->withoutVerifying()
                ->timeout(8)
                ->post($endpoint, [
                    'system_instruction' => [
                        'parts' => [['text' => $systemPrompt]],
                    ],
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 800,
                    ],
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $reply = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if (!empty($reply)) {
                        // Create clean voice text without markdown symbols and special punctuation
                        $voiceText = str_replace(['—', '–', '&mdash;', '&ndash;'], [' ', ' ', ' ', ' '], $reply);
                        $voiceText = preg_replace('/[*_#`•\[\]\(\)]/', '', $voiceText);
                        $voiceText = preg_replace('/\s+/', ' ', trim($voiceText));
                        if (mb_strlen($voiceText, 'UTF-8') > 280) {
                            $voiceText = mb_substr($voiceText, 0, 277, 'UTF-8') . '...';
                        }

                        $quickActions = self::generateQuickActions($message, $reply);

                        return [
                            'reply' => trim($reply),
                            'voice_text' => $voiceText,
                            'intent' => 'gemini_response',
                            'quick_actions' => $quickActions,
                            'policy_data' => null,
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Gemini API call to {$model} failed: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Local Deterministic Fallback Engine (for offline or unit tests).
     */
    private static function handleLocalFallback(string $clean): array
    {
        // A. Greetings
        if (self::contains($clean, ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'who are you', 'what is your name', 'maya'])) {
            return [
                'reply' => "Hello! I'm Maya, your personal PolicyFlow AI assistant. I'm here to converse with you about anything—from science and travel destinations to calculating insurance premiums and tracking policies. How can I assist you today?",
                'voice_text' => "Hello! I'm Maya, your PolicyFlow AI assistant. How can I assist with your journey today?",
                'intent' => 'greeting',
                'quick_actions' => ['Compare Plans', 'How are premiums calculated?', 'Track a Policy', 'What is covered in Plus?'],
                'policy_data' => null,
            ];
        }

        // B. Plan comparison & Plan Information
        if (self::contains($clean, ['plan', 'plans', 'package', 'tier', 'difference', 'compare', 'basic', 'plus', 'premium plan'])) {
            if (self::contains($clean, ['basic'])) {
                return [
                    'reply' => "Our **Basic Plan** is ₹12 per travel day (before multipliers). It is designed for domestic or budget travellers seeking essential emergency medical care, flight delay assistance, and 24/7 emergency support. Would you like me to help calculate an estimate for this plan?",
                    'voice_text' => "The Basic plan is 12 rupees per travel day before multipliers. It covers essential medical emergencies and flight delays.",
                    'intent' => 'plan_info',
                    'quick_actions' => ['Calculate Basic Plan', 'Tell me about Plus Plan', 'Tell me about Premium Plan'],
                    'policy_data' => null,
                ];
            }

            if (self::contains($clean, ['plus'])) {
                return [
                    'reply' => "Our **Plus Plan** is ₹20 per travel day (before multipliers) and is our most popular tier! It provides comprehensive coverage including high-limit hospitalization, checked baggage loss or delay, and trip curtailment. It is ideal for both domestic and overseas holidays.",
                    'voice_text' => "The Plus plan is 20 rupees per travel day. It includes high-limit hospitalization, baggage protection, and trip interruptions.",
                    'intent' => 'plan_info',
                    'quick_actions' => ['Select Plus Plan', 'Compare all 3 plans', 'Calculate an estimate'],
                    'policy_data' => null,
                ];
            }

            if (self::contains($clean, ['premium plan', 'premium tier']) || ($clean === 'premium')) {
                return [
                    'reply' => "Our **Premium Plan** is ₹30 per travel day (before multipliers). It offers top-tier travel peace of mind: up to ₹500,000 sum insured support, full trip cancellation protection, worldwide evacuation, and adventure activity coverage.",
                    'voice_text' => "The Premium plan is 30 rupees per travel day and offers up to 500,000 rupees coverage, trip cancellation, and international evacuation.",
                    'intent' => 'plan_info',
                    'quick_actions' => ['Select Premium Plan', 'How are premiums calculated?', 'What is Plus Plan?'],
                    'policy_data' => null,
                ];
            }

            return [
                'reply' => "We offer 3 distinct travel insurance plans:\n\n" .
                    "• **Basic (₹12/day):** Essential medical emergency and flight delay support.\n" .
                    "• **Plus (₹20/day - Most Popular):** Comprehensive medical, checked luggage loss/delay, and trip curtailment.\n" .
                    "• **Premium (₹30/day):** Maximum ₹500,000 coverage, trip cancellation, and international evacuation.\n\n" .
                    "Note that final rates depend on travel duration, age, destination, and coverage amount.",
                'voice_text' => "We offer three plans: Basic at 12 rupees per day, Plus at 20 rupees per day, and Premium at 30 rupees per day.",
                'intent' => 'plans_overview',
                'quick_actions' => ['How is premium calculated?', 'Check Basic Plan', 'Check Plus Plan', 'Check Premium Plan'],
                'policy_data' => null,
            ];
        }

        // C. How premium is calculated / Multipliers
        if (self::contains($clean, ['calculate', 'calculation', 'multiplier', 'formula', 'price', 'pricing', 'rate', 'cost', 'how much'])) {
            return [
                'reply' => "PolicyFlow uses transparent, deterministic pricing:\n\n" .
                    "**Formula:** `Base Rate × Travel Days × Age Multiplier × Destination Multiplier × Coverage Multiplier`\n\n" .
                    "• **Base Daily Rates:** Basic (₹12), Plus (₹20), Premium (₹30)\n" .
                    "• **Age at Trip Start:** 0–17 (1.10x), 18–60 (1.00x), 61–70 (1.50x), 71+ (2.00x)\n" .
                    "• **Destination:** Domestic (1.00x), International (1.80x)\n" .
                    "• **Coverage (Sum Insured):** ₹100k (1.00x), ₹300k (1.25x), ₹500k (1.50x)\n\n" .
                    "You can test any trip in our Premium Calculator on this page!",
                'voice_text' => "Our premiums are calculated by multiplying the base daily rate with your travel days, age multiplier, destination multiplier, and coverage multiplier.",
                'intent' => 'calculation_explained',
                'quick_actions' => ['Fill in Calculator', 'Compare Plans', 'Track a Policy'],
                'policy_data' => null,
            ];
        }

        // D. General Conversation Fallback
        return [
            'reply' => "I'm Maya, your PolicyFlow AI companion! I can talk with you about **any subject**—travel destinations, recommendations, science, life, or stories—and help you choose the best insurance plan, calculate premiums, or track your policy in real-time. What's on your mind?",
            'voice_text' => "I'm Maya, your AI companion. I can discuss any topic, recommend travel spots, or help with insurance. What's on your mind?",
            'intent' => 'fallback',
            'quick_actions' => ['Compare Plans', 'How is premium calculated?', 'Track Policy #1', 'Explore Destinations'],
            'policy_data' => null,
        ];
    }

    /**
     * Generate dynamic quick actions relevant to the current conversation topic.
     */
    private static function generateQuickActions(string $message, string $reply): array
    {
        $combined = strtolower($message . ' ' . $reply);

        if (str_contains($combined, 'code') || str_contains($combined, 'python') || str_contains($combined, 'script') || str_contains($combined, 'function')) {
            return ['Explain how it works', 'Show another example', 'Calculate Travel Premium', 'Compare Insurance Plans'];
        }

        if (str_contains($combined, 'destinat') || str_contains($combined, 'travel') || str_contains($combined, 'visit') || str_contains($combined, 'trip') || str_contains($combined, 'holiday') || str_contains($combined, 'switzerland') || str_contains($combined, 'japan') || str_contains($combined, 'beach')) {
            return ['Best time to visit', 'Calculate Travel Premium', 'Compare Insurance Plans', 'Track Policy #1'];
        }

        if (str_contains($combined, 'insur') || str_contains($combined, 'plan') || str_contains($combined, 'premium') || str_contains($combined, 'claim') || str_contains($combined, 'cover')) {
            return ['Compare Plans', 'How are premiums calculated?', 'Fill in Calculator', 'Track Policy #1'];
        }

        return ['Tell me more', 'Give an example', 'Another fun fact', 'Show Insurance Plans'];
    }

    /**
     * Extract identifier candidate from message.
     */
    private static function extractIdentifier(string $message): ?string
    {
        // 1. Explicit reference with prefix e.g. REQ-..., POL-...
        if (preg_match('/\b(REQ-[a-zA-Z0-9\-_]+|POL-[a-zA-Z0-9\-_]+)\b/i', $message, $m)) {
            return trim($m[1]);
        }

        // 2. Hash notation or "policy #1", "application 748291", "track 748291"
        if (preg_match('/(?:policy|request|application|check|track|status|id)\s*#?\s*([0-9]+)\b/i', $message, $m)) {
            return trim($m[1]);
        }

        if (preg_match('/#([0-9]+)\b/', $message, $m)) {
            return trim($m[1]);
        }

        // 3. Standalone 4 to 8 digit number (e.g. "748291")
        if (preg_match('/\b([0-9]{4,8})\b/', $message, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    /**
     * Helper to lookup policy by ID or client_request_id.
     */
    private static function findPolicy(string $identifier): ?PolicyRequest
    {
        $identifier = trim($identifier);

        // Check by client_request_id (memorable application number) or policy_number first
        $policy = PolicyRequest::where('client_request_id', $identifier)
            ->orWhere('policy_number', $identifier)
            ->first();

        if (!$policy && is_numeric($identifier)) {
            $policy = PolicyRequest::find((int) $identifier);
        }

        return $policy;
    }

    /**
     * Format tracking response in a human-like assistant manner.
     */
    private static function formatPolicyTrackingResponse(PolicyRequest $policy): array
    {
        $status = $policy->status;
        $name = $policy->customer_name;
        $id = $policy->id;
        $premium = number_format((float) $policy->premium, 2);

        $statusMessage = match ($status) {
            'ISSUED' => "Great news! Policy request **#{$id}** (Application ID: **{$policy->client_request_id}**) for **{$name}** has been officially **ISSUED**! Your policy number is **{$policy->policy_number}**. Total premium is ₹{$premium}.",
            'PENDING' => "Policy request **#{$id}** (Application ID: **{$policy->client_request_id}**) for **{$name}** is currently **PENDING** underwriter review with provider reference `{$policy->provider_reference}`. Premium is ₹{$premium}.",
            'VALIDATED' => "Policy request **#{$id}** (Application ID: **{$policy->client_request_id}**) for **{$name}** is **VALIDATED** and ready for insurer submission. Premium is ₹{$premium}.",
            'FAILED' => "Policy request **#{$id}** (Application ID: **{$policy->client_request_id}**) for **{$name}** is marked as **FAILED**. Underwriting criteria were not met.",
            default => "Policy request **#{$id}** for **{$name}** is currently in **{$status}** status. Premium is ₹{$premium}."
        };

        $voiceMessage = match ($status) {
            'ISSUED' => "Great news! Policy number {$policy->policy_number} for {$name} has been officially issued. Total premium is {$premium} rupees.",
            'PENDING' => "Policy request {$id} for {$name} is currently pending underwriter review.",
            'VALIDATED' => "Policy request {$id} for {$name} is validated and ready for submission.",
            'FAILED' => "Policy request {$id} for {$name} was not approved by the insurer.",
            default => "Policy request {$id} is currently in {$status} status."
        };

        return [
            'reply' => $statusMessage . "\n\n• **Destination:** {$policy->destination}\n• **Plan:** {$policy->plan_code}\n• **Sum Insured:** ₹" . number_format($policy->sum_insured) . "\n• **Trip Dates:** " . ($policy->trip_start ? $policy->trip_start->format('Y-m-d') : 'N/A') . " to " . ($policy->trip_end ? $policy->trip_end->format('Y-m-d') : 'N/A'),
            'voice_text' => $voiceMessage,
            'intent' => 'policy_found',
            'quick_actions' => ['Track another policy', 'Compare Plans', 'How are premiums calculated?'],
            'policy_data' => [
                'id' => $policy->id,
                'status' => $policy->status,
                'policy_number' => $policy->policy_number,
                'premium' => $policy->premium,
                'customer_name' => $policy->customer_name,
            ],
        ];
    }

    /**
     * Check if string contains any of the search needles.
     */
    private static function contains(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, strtolower($needle))) {
                return true;
            }
        }
        return false;
    }
}
