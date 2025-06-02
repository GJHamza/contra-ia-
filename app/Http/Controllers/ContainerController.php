<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\Document;
use App\Models\Clause;
use Illuminate\Http\Request;

class ContainerController extends Controller
{
    /**
     * Liste tous les containers avec leurs documents et clauses associés.
     */
    public function index()
    {
        $containers = Container::with(['documents', 'clauses'])->get();
        return response()->json($containers, 200);
    }

    /**
     * Affiche un container spécifique.
     */
    public function show($id)
    {
        $container = Container::with(['documents', 'clauses'])->find($id);

        if (!$container) {
            return response()->json(['message' => 'Container introuvable.'], 404);
        }

        return response()->json($container, 200);
    }

    /**
     * Crée un nouveau container.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $container = Container::create($validated);

        return response()->json($container, 201);
    }

    /**
     * Met à jour un container existant.
     */
    public function update(Request $request, $id)
    {
        $container = Container::find($id);

        if (!$container) {
            return response()->json(['message' => 'Container introuvable.'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $container->update($validated);

        return response()->json($container, 200);
    }

    /**
     * Supprime un container avec ses liens.
     */
    public function destroy($id)
    {
        $container = Container::find($id);

        if (!$container) {
            return response()->json(['message' => 'Container introuvable.'], 404);
        }

        $container->delete();

        return response()->json(['message' => 'Container supprimé avec succès.'], 200);
    }

    /**
     * Supprime un container sans affecter les documents ou clauses liés.
     */
    public function destroyContainerId($containerId)
    {
        $container = Container::find($containerId);

        if (!$container) {
            return response()->json(['message' => 'Container introuvable.'], 404);
        }

        $container->delete();

        return response()->json(['message' => 'Container supprimé sans affecter les relations.'], 200);
    }

    /**
     * Associe des documents à un container.
     */
    public function assignDocuments(Request $request, $id)
    {
        $container = Container::find($id);

        if (!$container) {
            return response()->json(['message' => 'Container introuvable.'], 404);
        }

        $validated = $request->validate([
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:documents,id',
        ]);

        foreach ($validated['document_ids'] as $docId) {
            $document = Document::find($docId);
            $document->container_id = $container->id;
            $document->save();
        }

        return response()->json(['message' => 'Documents associés avec succès.'], 200);
    }

    /**
     * Associe des clauses à un container.
     */
    public function assignClauses(Request $request, $id)
    {
        $container = Container::find($id);

        if (!$container) {
            return response()->json(['message' => 'Container introuvable.'], 404);
        }

        $validated = $request->validate([
            'clause_ids' => 'required|array',
            'clause_ids.*' => 'exists:clauses,id',
        ]);

        foreach ($validated['clause_ids'] as $clauseId) {
            $clause = Clause::find($clauseId);
            $clause->container_id = $container->id;
            $clause->save();
        }

        return response()->json(['message' => 'Clauses associées avec succès.'], 200);
    }

    /**
     * Retire un document d’un container.
     */
    public function removeDocument($containerId, $documentId)
    {
        $container = Container::find($containerId);
        $document = Document::find($documentId);

        if (!$container || !$document || $document->container_id !== $container->id) {
            return response()->json(['message' => 'Container ou document introuvable ou non lié.'], 404);
        }

        $document->container_id = null;
        $document->save();

        return response()->json(['message' => 'Document retiré du container.'], 200);
    }

    /**
     * Retire une clause d’un container.
     */
    public function removeClause($containerId, $clauseId)
    {
        $container = Container::find($containerId);
        $clause = Clause::find($clauseId);

        if (!$container || !$clause || $clause->container_id !== $container->id) {
            return response()->json(['message' => 'Container ou clause introuvable ou non liée.'], 404);
        }

        $clause->container_id = null;
        $clause->save();

        return response()->json(['message' => 'Clause retirée du container.'], 200);
    }
}
