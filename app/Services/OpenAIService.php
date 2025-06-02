<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenAIService
{
    public function generateText($prompt)
    {
        // Vérifie si on est en mode "fake"
        if (env('OPENAI_FAKE_MODE', false)) {
            return "Réponse simulée pour : " . $prompt;
        }

        try {
            $response = Http::withToken(env('OPENAI_API_KEY'))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                ]);

            return $response->json()['choices'][0]['message']['content'] ?? null;

        } catch (\Illuminate\Http\Client\RequestException $e) {
            return null;
        }
    }
}
