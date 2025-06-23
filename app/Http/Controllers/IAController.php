<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IAController extends Controller
{
    public function genererTexte(Request $request)
    {
        // Log du body brut reçu
        \Log::info('Body reçu par Laravel : ' . json_encode($request->all()));
        // Validation des champs reçus
        $data = $request->validate([
            'document' => 'required|array',
            'document.title' => 'required|string|max:255',
            'document.content' => 'required', // accepte tout type
        ]);

        // Toujours essayer de décoder le content, même si déjà un tableau
        $content = $data['document']['content'];
        if (!is_array($content)) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $content = $decoded;
            }
        }
        $data['document']['content'] = $content;

        // Construction dynamique de la prompt
        $prompt = "Données du document :\n";
        if (is_array($data['document']['content'])) {
            foreach ($data['document']['content'] as $key => $value) {
                $prompt .= "$key : $value\n";
            }
        } else {
            $prompt .= $data['document']['content'] . "\n";
        }
        // Utiliser une instruction personnalisée si fournie
        $instruction = $data['document']['instruction'] ?? "Merci de générer un document structuré à partir de ces informations.";
        $prompt .= "\n" . $instruction;

        // Log de la prompt pour debug
        \Log::info('Prompt envoyée à Python : ' . $prompt);

        try {
            // Appel vers le microservice Flask (URL corrigée si besoin)
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('http://127.0.0.1:5000/generate', [
                'prompt' => $prompt,
            ]);

            $json = $response->json();
            \Log::info('Réponse du microservice IA : ' . json_encode($json));
            if ($response->successful() && isset($json['generated_text'])) {
                $generatedText = $json['generated_text'];

                // Générer le PDF avec DomPDF
                
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.document', [
                    'document' => (object)[
                        'title' => $data['document']['title'],
                        'generated_text' => $generatedText,
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

    public function genererTemplate(Request $request)
    {
        // On suppose que le body contient déjà tous les champs dynamiques nécessaires
        $fields = $request->all();

        // Log pour debug
        \Log::info('Champs envoyés à /generate : ' . json_encode($fields));

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('http://127.0.0.1:5000/generate', $fields);

            $json = $response->json();
            if ($response->successful() && isset($json['generated_text'])) {
                // Ici, tu peux retourner le texte généré ou générer un PDF comme tu veux
                return response()->json([
                    'success' => true,
                    'generated_text' => $json['generated_text'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la génération du document.',
                    'error' => $json['error'] ?? 'Erreur inconnue',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Le microservice IA est indisponible.',
                'error' => $e->getMessage(),
            ], 503);
        }
    }
}
