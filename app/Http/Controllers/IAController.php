<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IAController extends Controller
{
    public function genererTexte(Request $request)
    {
        // Validation des champs reçus
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Construction de la prompt
        $prompt = "Titre : {$data['title']}\n";
        $prompt .= "Contenu : {$data['content']}\n";
        $prompt .= "Merci de générer un document structuré qui reprend ces informations de façon claire et professionnelle.";

        try {
            // Appel vers le microservice Flask
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('http://localhost:5000/generate', [
                'prompt' => $prompt,
            ]);

            $json = $response->json();
            if ($response->successful() && isset($json['generated_text'])) {
                return response()->json([
                    'success' => true,
                    'generated_text' => $json['generated_text'],
                ], 200);
            } elseif ($response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Réponse du microservice IA mal formée.',
                    'details' => $json,
                ], 500);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la génération du texte.',
                    'details' => $response->body(),
                ], $response->status() ?: 500);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Le microservice IA est indisponible.',
                'error' => $e->getMessage(),
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
