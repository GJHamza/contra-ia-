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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Lier le document à l'utilisateur connecté
        $validated['user_id'] = $request->user()->id;

        $document = Document::create($validated);

        return response()->json([
            'message' => 'Document créé avec succès.',
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
        $document = Document::findOrFail($id);

        $pdf = Pdf::loadView('pdf.document', ['document' => $document]);

        return $pdf->download("document_{$document->id}.pdf");
    }
}
