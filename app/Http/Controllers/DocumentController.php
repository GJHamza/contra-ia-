<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use League\CommonMark\CommonMarkConverter;
use Illuminate\Support\Facades\Http;

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
        $prompt .= "\nMerci de générer un document complet, professionnel et structuré, en français, en utilisant la syntaxe Markdown, selon les instructions suivantes :\n";
        $prompt .= "- Commence par un titre principal centré, en majuscules, correspondant au type de contrat (exemple : \"CONTRAT DE TRAVAIL\").\n";
        $prompt .= "- Ajoute immédiatement sous le titre principal un paragraphe d’introduction qui présente le contexte, l’objet du document, les parties concernées et le but du document, avant de commencer les articles.\n";
        $prompt .= "- Ajoute une clause de définitions après l’introduction, pour clarifier les termes importants utilisés dans le document.\n";
        $prompt .= "- Structure le contrat en articles numérotés, chaque article commençant sur une nouvelle ligne, avec un titre de niveau 2 (##) en Markdown, en gras et souligné (exemple : \"## _Article 1 : Parties concernées_\").\n";
        $prompt .= "- Inclue systématiquement des clauses de confidentialité, force majeure, protection des données personnelles, loi applicable, modalités de résiliation, et règlement des litiges, même si elles ne sont pas explicitement demandées.\n";
        $prompt .= "- Le contenu de chaque article doit être rédigé en paragraphes courts, séparés par des sauts de ligne, et utiliser des listes à puces pour les obligations ou droits multiples.\n";
        $prompt .= "- Utilise un langage juridique clair, formel et professionnel, sans ambiguïté.\n";
        $prompt .= "- La mise en page doit être aérée, avec des marges harmonieuses et des espaces suffisants entre les articles.\n";
        $prompt .= "- À la fin du document, ajoute une formule de clôture élégante et centrée, par exemple :\n";
        $prompt .= "- N’ajoute pas de section signature : la zone de signature sera gérée automatiquement dans la mise en page du document.\n";
        $prompt .= "- N’utilise pas de crochets, de champs à compléter, ni de liens ou de tableaux Markdown pour les signatures.\n";
        $prompt .= "- La présentation doit être claire, professionnelle, avec des titres bien visibles, une structure aérée, et aucune section vide.\n";
        $prompt .= "\nUtilise exactement les informations fournies pour remplir tous les champs du document, quel que soit le type de document demandé.";

        \Log::info('Prompt envoyé au microservice /generate', ['prompt' => $prompt]);

        // Appel au microservice Python
        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post('http://127.0.0.1:5000/generate', [
                    'prompt' => $prompt,
                ]);
            $json = $response->json();
            if ($response->successful() && isset($json['generated_text'])) {
                $generatedText = $json['generated_text'];

                // Extraction des tokens et du coût si présents dans la réponse du microservice
                $tokens = $json['usage']['total_tokens'] ?? null;
                $cost = $json['usage']['cost'] ?? null;
                if ($tokens !== null && $cost !== null) {
                    \App\Models\OpenAIUsage::create([
                        'date' => now()->toDateString(),
                        'tokens' => $tokens,
                        'cost' => $cost,
                    ]);
                }

                // Conversion Markdown -> HTML
                $converter = new CommonMarkConverter([
                    'html_input' => 'escape',
                    'allow_unsafe_links' => false,
                ]);
                $html = $converter->convert($generatedText)->getContent();
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

        // Enregistrement automatique de l'usage OpenAI lors de la génération de document
        // À placer après chaque génération IA réussie (exemple pour DocumentController)
        //
        // Exemple d'utilisation :
        // OpenAIUsage::create([
        //     'date' => now()->toDateString(),
        //     'tokens' => $tokens, // nombre de tokens utilisés pour cette génération
        //     'cost' => $cost,     // coût estimé pour cette génération
        // ]);
        //
        // Il faut extraire $tokens et $cost de la réponse du microservice ou de l'API OpenAI
        // et les passer ici pour chaque génération IA.

        // Stocker le texte généré dans le champ content (JSON)
        $validated['content'] = json_encode([
            'fields' => $contentArray,
            'generated_text' => $generatedText,
            'html' => $html,
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
    // public function storeFromTemplate(Request $request)
    // {
    //     $data = $request->all(); // Doit contenir type_template et tous les champs nécessaires

    //     // Validation minimale (ajuste selon tes besoins réels)
    //     $validated = \Validator::make($data, [
    //         'type_template' => 'required|string',
    //         'title' => 'required|string|max:255',
    //         // Ajoute ici d'autres règles si tu veux valider les champs dynamiques
    //     ])->validate();

    //     \Log::info('Données reçues du frontend (React)', ['data' => $data]);

    //     // Appel au microservice Python
    //     try {
    //         $response = \Illuminate\Support\Facades\Http::withHeaders(['Content-Type' => 'application/json'])
    //             ->post('http://localhost:5000/generate-template', $data);
    //         $json = $response->json();
    //         \Log::info('Réponse microservice /generate-template', ['response' => $json]);
    //         if ($response->successful() && isset($json['generated_text'])) {
    //             $generatedText = $json['generated_text'];
    //         } else {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Erreur lors de la génération du texte.',
    //                 'details' => $json,
    //             ], 500);
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Le microservice IA est indisponible.',
    //             'error' => $e->getMessage(),
    //         ], 503);
    //     }

    //     // Stocker le texte généré dans le champ content (JSON)
    //     $contentArray = $data;
    //     unset($contentArray['title']); // On ne stocke pas le titre dans le content
    //     $validated['content'] = json_encode([
    //         'fields' => $contentArray,
    //         'generated_text' => $generatedText,
    //     ]);

    //     // Lier à l'utilisateur si besoin
    //     if ($request->user()) {
    //         $validated['user_id'] = $request->user()->id;
    //     } else {
    //         $validated['user_id'] = null;
    //     }

    //     $document = \App\Models\Document::create($validated);

    //     return response()->json([
    //         'message' => 'Document créé avec succès (template) !',
    //         'document' => $document,
    //     ], 201);
    // }

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

// Nettoyage des conflits git :
// Vérifie qu'il n'y a aucune balise <<<<<<<, =======, >>>>>>> dans ce fichier.
// Si tu en trouves, supprime-les et fusionne le code manuellement.
//
// Exemple de correction :
// <<<<<<< HEAD
// return response()->json(['message' => 'Document saved locally.']);
// =======
// return response()->json(['success' => true, 'message' => 'Document enregistré avec succès.']);
// >>>>>>> ca870f1
// devient :
// return response()->json(['success' => true, 'message' => 'Document enregistré avec succès.']);
