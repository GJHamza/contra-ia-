<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Affiche tous les clients.
     */
    public function index()
    {
        $clients = Client::all();
        return response()->json($clients);
    }

    /**
     * Crée un nouveau client.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $client = Client::create($validated);

        return response()->json([
            'message' => 'Client créé avec succès.',
            'client' => $client
        ], 201);
    }

    /**
     * Affiche un client spécifique.
     */
    public function show($id)
    {
        $client = Client::findOrFail($id);
        return response()->json($client);
    }

    /**
     * Met à jour les données d’un client.
     */
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:clients,email,' . $client->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $client->update($validated);

        return response()->json([
            'message' => 'Client mis à jour avec succès.',
            'client' => $client
        ]);
    }

    /**
     * Supprime un client.
     */
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return response()->json([
            'message' => 'Client supprimé avec succès.'
        ]);
    }
}
