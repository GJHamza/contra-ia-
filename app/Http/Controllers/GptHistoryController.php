<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GptHistory;

class GptHistoryController extends Controller
{
    /**
     * Récupère l'historique des textes générés par l'utilisateur connecté
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $history = GptHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($history);
    }

    /**
     * Enregistre une nouvelle interaction dans l'historique
     */
    public function store(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'response' => 'required|string',
        ]);

        $history = GptHistory::create([
            'user_id' => $request->user()->id,
            'prompt' => $request->input('prompt'),
            'response' => $request->input('response'),
        ]);

        return response()->json([
            'message' => 'Historique enregistré avec succès.',
            'data' => $history
        ], 201);
    }

    /**
     * Supprimer une entrée d'historique
     */
    public function destroy(Request $request, $id)
    {
        $history = GptHistory::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$history) {
            return response()->json(['error' => 'Entrée non trouvée ou non autorisée.'], 404);
        }

        $history->delete();

        return response()->json(['message' => 'Entrée supprimée avec succès.']);
    }
}
