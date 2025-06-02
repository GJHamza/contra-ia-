<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function index()
    {
        return response()->json(Document::where('user_id', Auth::id())->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $document = Document::create([
            'title'   => $request->title,
            'content' => $request->content,
            'user_id' => Auth::id(),
            'status'  => 'en_attente',
        ]);

        return response()->json($document, 201);
    }

    public function show(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        return response()->json($document);
    }

    public function update(Request $request, Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $document->update($request->only(['title', 'content', 'status']));

        return response()->json($document);
    }

    public function destroy(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $document->delete();

        return response()->json(['message' => 'Document supprimé']);
    }
}
