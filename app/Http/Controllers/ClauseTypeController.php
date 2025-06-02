<?php

namespace App\Http\Controllers;

use App\Models\ClauseType;
use Illuminate\Http\Request;

class ClauseTypeController extends Controller
{
    /**
     * Liste tous les types de clauses.
     */
    public function index()
    {
        $types = ClauseType::all();
        return response()->json($types);
    }

    /**
     * Crée un nouveau type de clause.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $type = ClauseType::create($validated);

        return response()->json([
            'message' => 'Type de clause créé avec succès.',
            'type' => $type
        ], 201);
    }

    /**
     * Affiche un type de clause spécifique.
     */
    public function show($id)
    {
        $type = ClauseType::findOrFail($id);
        return response()->json($type);
    }

    /**
     * Met à jour un type de clause.
     */
    public function update(Request $request, $id)
    {
        $type = ClauseType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $type->update($validated);

        return response()->json([
            'message' => 'Type de clause mis à jour avec succès.',
            'type' => $type
        ]);
    }

    /**
     * Supprime un type de clause.
     */
    public function destroy($id)
    {
        $type = ClauseType::findOrFail($id);
        $type->delete();

        return response()->json([
            'message' => 'Type de clause supprimé avec succès.'
        ]);
    }
}