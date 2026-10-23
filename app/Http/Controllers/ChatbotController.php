<?php

namespace App\Http\Controllers;

use App\Services\ChatbotAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    /**
     * Handle incoming chatbot message and return human assistant response with voice text.
     */
    public function message(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array',
        ]);

        $response = ChatbotAssistant::respond(
            message: $data['message'],
            history: $data['history'] ?? []
        );

        return response()->json($response);
    }
}
