<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IAController extends Controller
{
    public function genererTexte(Request $request)
    {
        // Validation du prompt
        $request->validate([
            'prompt' => 'required|string|max:1000'
        ]);

        try {
            // Appel vers le microservice Flask (nouvelle URL et format)
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('http://localhost:5000/generate', [
                'prompt' => $request->input('prompt'),
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
