<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentController extends Controller
{
    /**
     * Créer un nouveau document.
     */
    public function store(Request $request)
    {
        $data = $request->input('document');

        $validated = \Validator::make($data, [
            'title' => 'required|string|max:255',
            'content' => 'required|string', // JSON string
        ])->validate();

        // Décoder le JSON du champ content
        $contentArray = json_decode($validated['content'], true);

        // Construire le prompt amélioré
        $prompt = "Données du document :\n";
        foreach ($contentArray as $key => $value) {
            $prompt .= "$key : $value\n";
        }
        $prompt .= "\nMerci de générer un contrat de prestation de service complet, structuré et professionnel, en français, avec les consignes suivantes :\n";
        $prompt .= "- Chaque section doit être dans un paragraphe séparé, avec des titres clairs pour chaque partie.\n";
        $prompt .= "- Utilise des balises HTML <p> pour chaque paragraphe et <h2> pour chaque section.\n";
        $prompt .= "- À la fin du document, ajoute une section 'Signatures' avec :<br>\n";
        $prompt .= "  - 'Date et lieu' aligné à droite, suivi d'un champ vide à remplir (exemple : _________)<br>\n";
        $prompt .= "  - 'Le prestataire' aligné à droite, suivi d'un champ vide à remplir (exemple : _________)<br>\n";
        $prompt .= "  - 'Le client' aligné à droite, suivi d'un champ vide à remplir (exemple : _________)<br>\n";
        $prompt .= "- Les champs de signature doivent être vides pour permettre la signature manuscrite.\n";
        $prompt .= "- Le contrat doit être rédigé dans un style juridique, clair et précis, comme un vrai contrat utilisé par des professionnels.\n";

        \Log::info('Prompt envoyé au microservice /generate', ['prompt' => $prompt]);

        // Appel au microservice Python
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders(['Content-Type' => 'application/json'])
                ->post('http://127.0.0.1:5000/generate', [
                    'prompt' => $prompt,
                ]);
            $json = $response->json();
            if ($response->successful() && isset($json['generated_text'])) {
                $generatedText = $json['generated_text'];
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la génération du texte.',
                    'details' => $json,
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Le microservice IA est indisponible.',
                'error' => $e->getMessage(),
            ], 503);
        }

        // Stocker le texte généré dans le champ content (JSON)
        $validated['content'] = json_encode([
            'fields' => $contentArray,
            'generated_text' => $generatedText,
        ]);

        // Lier à l'utilisateur si besoin
        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        } else {
            // Si pas d'utilisateur, mettre null ou un user_id par défaut si besoin
            $validated['user_id'] = null;
        }

        $document = \App\Models\Document::create($validated);

        return response()->json([
            'message' => 'Document créé avec succès.',
            'document' => $document,
        ], 201);
    }

    /**
     * Créer un document à partir d'un template dynamique (microservice /generate-template).
     */
    public function storeFromTemplate(Request $request)
    {
        $data = $request->all(); // Doit contenir type_template et tous les champs nécessaires

        // Validation minimale (ajuste selon tes besoins réels)
        $validated = \Validator::make($data, [
            'type_template' => 'required|string',
            'title' => 'required|string|max:255',
            // Ajoute ici d'autres règles si tu veux valider les champs dynamiques
        ])->validate();

        \Log::info('Données reçues du frontend (React)', ['data' => $data]);

        // Appel au microservice Python
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders(['Content-Type' => 'application/json'])
                ->post('http://localhost:5000/generate-template', $data);
            $json = $response->json();
            \Log::info('Réponse microservice /generate-template', ['response' => $json]);
            if ($response->successful() && isset($json['generated_text'])) {
                $generatedText = $json['generated_text'];
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la génération du texte.',
                    'details' => $json,
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Le microservice IA est indisponible.',
                'error' => $e->getMessage(),
            ], 503);
        }

        // Stocker le texte généré dans le champ content (JSON)
        $contentArray = $data;
        unset($contentArray['title']); // On ne stocke pas le titre dans le content
        $validated['content'] = json_encode([
            'fields' => $contentArray,
            'generated_text' => $generatedText,
        ]);

        // Lier à l'utilisateur si besoin
        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        } else {
            $validated['user_id'] = null;
        }

        $document = \App\Models\Document::create($validated);

        return response()->json([
            'message' => 'Document créé avec succès (template) !',
            'document' => $document,
        ], 201);
    }

    /**
     * Lister tous les documents avec pagination et recherche.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10);

        $query = Document::query();

        if ($search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
        }

        $documents = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json($documents);
    }

    /**
     * Afficher un document spécifique.
     */
    public function show($id)
    {
        $document = Document::findOrFail($id);

        return response()->json([
            'document' => $document,
        ], 200);
    }

    /**
     * Mettre à jour un document.
     */
    public function update(Request $request, $id)
    {
        $document = Document::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ]);

        $document->update($validated);

        return response()->json([
            'message' => 'Document mis à jour.',
            'document' => $document,
        ], 200);
    }

    /**
     * Supprimer un document.
     */
    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        $document->delete();

        return response()->json([
            'message' => 'Document supprimé avec succès.',
        ], 200);
    }

    /**
     * Télécharger un document en PDF.
     */
    public function download($id)
    {
        $document = \App\Models\Document::findOrFail($id);

        // Décoder le champ content pour récupérer le texte généré
        $content = json_decode($document->content, true);
        $generatedText = $content['generated_text'] ?? '';

        $pdf = \PDF::loadView('pdf.document', [
            'document' => $document,
            'generated_text' => $generatedText,
        ]);

        return $pdf->download("document_{$document->id}.pdf");
    }

    /**
     * Prévisualiser un document en PDF (affichage dans le navigateur).
     */
    public function preview($id)
    {
        $document = \App\Models\Document::findOrFail($id);

        // Décoder le champ content pour récupérer le texte généré
        $content = json_decode($document->content, true);
        $generatedText = $content['generated_text'] ?? '';

        $pdf = \PDF::loadView('pdf.document', [
            'document' => $document,
            'generated_text' => $generatedText,
        ]);

        // Affiche le PDF dans le navigateur (inline)
        return $pdf->stream("document_{$document->id}.pdf");
    }
}
