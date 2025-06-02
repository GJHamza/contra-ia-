<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TemplateController extends Controller
{
    // ✅ Lister tous les templates
    public function index()
    {
        return response()->json(Template::all(), 200);
    }

    // ✅ Créer un nouveau template
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'language' => 'nullable|string',
            'description' => 'nullable|string',
            'category' => 'nullable|string'
        ]);

        $template = Template::create($request->all());
        return response()->json($template, 201);
    }

    // ✅ Afficher un template spécifique
    public function show($id)
    {
        $template = Template::find($id);

        if (!$template) {
            return response()->json(['message' => 'Modèle non trouvé'], 404);
        }

        return response()->json($template);
    }

    // ✅ Mettre à jour un template
    public function update(Request $request, $id)
    {
        $template = Template::find($id);

        if (!$template) {
            return response()->json(['message' => 'Modèle non trouvé'], 404);
        }

        $template->update($request->all());

        return response()->json($template);
    }

    // ✅ Supprimer un template
    public function destroy($id)
    {
        $template = Template::find($id);

        if (!$template) {
            return response()->json(['message' => 'Modèle non trouvé'], 404);
        }

        $template->delete();

        return response()->json(['message' => 'Modèle supprimé avec succès']);
    }

    // ✅ Génération de contenu avec l'IA Hugging Face
    public function generateWithAI(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'prompt' => 'required|string',
            'language' => 'nullable|string',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
        ]);

        try {
            $token = env('HUGGINGFACE_API_TOKEN');
            $baseUrl = env('HUGGINGFACE_BASE_URL', 'https://api-inference.huggingface.co');
            $model = env('HUGGINGFACE_DEFAULT_MODEL', 'gpt2');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post("{$baseUrl}/models/{$model}", [
                'inputs' => "Tu es un assistant juridique marocain. Génère un document conforme à la loi marocaine. " . $request->prompt,
                'parameters' => [
                    'temperature' => 0.7,
                    'max_new_tokens' => 1500,
                ]
            ]);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Erreur lors de l’appel à l’API Hugging Face.',
                    'details' => $response->json()
                ], $response->status());
            }

            $data = $response->json();

            $generatedContent = is_array($data) && isset($data[0]['generated_text'])
                ? $data[0]['generated_text']
                : 'Erreur de génération';

            // Sauvegarde dans la base
            $template = Template::create([
                'title' => $request->title,
                'content' => $generatedContent,
                'description' => $request->description ?? null,
                'language' => $request->language ?? 'fr',
                'category' => $request->category ?? 'général',
            ]);

            return response()->json($template, 201);

        } catch (\Exception $e) {
            Log::error('Erreur Hugging Face IA', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Erreur lors de la génération avec Hugging Face.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
