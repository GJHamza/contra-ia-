<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Document;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Dashboard : stats
    public function dashboard()
    {
        return response()->json([
            'totalContrats' => Document::count(),
            'totalUsers' => User::count(),
            'lastContrats' => Document::orderBy('created_at', 'desc')->take(5)->get(),
        ]);
    }

    // Liste des utilisateurs
    public function users()
    {
        return User::all();
    }

    // Création utilisateur
    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'is_admin' => 'boolean',
        ]);
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);
        return response()->json($user, 201);
    }

    // Modification utilisateur
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'password' => 'sometimes|string|min:6',
            'is_admin' => 'boolean',
        ]);
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        $user->update($data);
        return response()->json($user);
    }

    // Suppression utilisateur
    public function deleteUser($id)
    {
        User::destroy($id);
        return response()->json(['success' => true]);
    }

    // Liste des documents
    public function documents()
    {
        return Document::orderBy('created_at', 'desc')->get();
    }

    // Affichage d'un document
    public function showDocument($id)
    {
        return Document::findOrFail($id);
    }

    // Suppression d'un document
    public function deleteDocument($id)
    {
        Document::destroy($id);
        return response()->json(['success' => true]);
    }
}
