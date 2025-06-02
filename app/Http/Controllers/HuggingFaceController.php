<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HuggingFaceController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'model' => 'nullable|string', // tu peux passer le nom du modèle (ex : 'gpt2', 'bert-base-uncased', etc.)
        ]);

        $inputText = $request->input('text');
        $model = $request->input('model', 'gpt2'); // modèle par défaut

        $token = env('HUGGINGFACE_API_TOKEN');

        $response = Http::withToken($token)
            ->timeout(30)
            ->post("https://api-inference.huggingface.co/models/{$model}", [
                'inputs' => $inputText
            ]);

        if ($response->successful()) {
            return response()->json([
                'model' => $model,
                'input' => $inputText,
                'result' => $response->json()
            ]);
        } else {
            return response()->json([
                'error' => 'Erreur HuggingFace API',
                'details' => $response->json()
            ], $response->status());
        }
    }
}
