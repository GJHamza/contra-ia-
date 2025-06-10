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
            'document' => 'required|array',
            'document.title' => 'required|string|max:255',
            'document.content' => 'required', // accepte tout type
        ]);

        // Si content est une chaîne JSON, on la décode en tableau associatif
        if (is_string($data['document']['content'])) {
            $decoded = json_decode($data['document']['content'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data['document']['content'] = $decoded;
            }
        }

        // Construction de la prompt
        $prompt = "Données du document :\n";
        if (is_array($data['document']['content'])) {
            foreach ($data['document']['content'] as $key => $value) {
                $prompt .= "$key : $value\n";
            }
        } else {
            $prompt .= $data['document']['content'] . "\n";
        }
        $prompt .= "\nMerci de générer un document structuré qui reprend ces informations de façon claire et professionnelle.";

        // Log de la prompt pour debug
        \Log::info('Prompt envoyée à Python : ' . $prompt);

        try {
            // Appel vers le microservice Flask
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('http://127.0.0.1:5000/generate', [
                'prompt' => $prompt,
            ]);

            $json = $response->json();
            if ($response->successful() && isset($json['generated_text'])) {
                $generatedText = $json['generated_text'];

                // Générer le PDF avec DomPDF
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.document', [
                    'document' => (object)[
                        'title' => $data['document']['title'],
                        'type' => '', // tu peux adapter si besoin
                        'content' => $generatedText,
                    ]
                ]);

                // Retourner le PDF pour affichage dans React
                return $pdf->stream('document_genere.pdf');
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
