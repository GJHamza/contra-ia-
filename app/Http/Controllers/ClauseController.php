<?php

namespace App\Http\Controllers;

use App\Models\Clause;
use Illuminate\Http\Request;

class ClauseController extends Controller
{
    /**
     * Liste toutes les clauses.
     */
    public function index()
    {
        $clauses = Clause::all();
        return response()->json($clauses);
    }

    /**
     * Crée une nouvelle clause.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'container_id' => 'required|exists:containers,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $clause = Clause::create($validated);

        return response()->json([
            'message' => 'Clause créée avec succès.',
            'clause' => $clause
        ], 201);
    }

    /**
     * Affiche une clause spécifique.
     */
    public function show($id)
    {
        $clause = Clause::findOrFail($id);

        return response()->json($clause);
    }

    /**
     * Met à jour une clause existante.
     */
    public function update(Request $request, $id)
    {
        $clause = Clause::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'clause_type_id' => 'sometimes|required|exists:clause_types,id',
        ]);

        $clause->update($validated);

        return response()->json([
            'message' => 'Clause mise à jour avec succès.',
            'clause' => $clause
        ]);
    }

    /**
     * Supprime une clause.
     */
    public function destroy($id)
    {
        $clause = Clause::findOrFail($id);
        $clause->delete();

        return response()->json([
            'message' => 'Clause supprimée avec succès.'
        ]);
    }
}
