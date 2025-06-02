<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GPTPDFController extends Controller
{
    public function generateText(Request $request)
    {
        try {
            $prompt = $request->input('prompt', 'Explique le contrat de bail au Maroc.');

            if (env('OPENAI_FAKE_MODE', false)) {
                return response()->json([
                    'generated_text' => 'Réponse simulée pour : ' . $prompt
                ]);
            }

            $token = env('HUGGINGFACE_API_TOKEN');
            $baseUrl = env('HUGGINGFACE_BASE_URL', 'https://api-inference.huggingface.co');
            $model = env('HUGGINGFACE_DEFAULT_MODEL', 'gpt2');

            Log::info("Appel HuggingFace avec prompt : $prompt");

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("{$baseUrl}/models/{$model}", [
                'inputs' => $prompt,
                'parameters' => [
                    'temperature' => 0.7,
                    'max_new_tokens' => 500,
                ]
            ]);

            // Nouveau : log complet si erreur
            if ($response->failed()) {
                Log::error('Réponse HuggingFace échouée', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'error' => 'Erreur lors de l’appel à l’API Hugging Face.',
                    'details' => $response->json()
                ], $response->status());
            }

            $data = $response->json();

            $generatedText = is_array($data) && isset($data[0]['generated_text'])
                ? $data[0]['generated_text']
                : null;

            if (!$generatedText) {
                Log::warning('Réponse vide ou mal formée', ['data' => $data]);
                return response()->json(['error' => 'Aucune réponse générée.'], 500);
            }

            return response()->json(['generated_text' => $generatedText]);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Erreur HTTP Hugging Face', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Erreur HTTP Hugging Face : ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Erreur GPTPDFController', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Erreur lors de la génération avec Hugging Face.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
