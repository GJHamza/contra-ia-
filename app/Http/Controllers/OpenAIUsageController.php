<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class OpenAIUsageController extends Controller
{
    public function getUsage()
    {
        $apiKey = env('OPENAI_API_KEY');

        $start = now()->startOfMonth()->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $response = Http::withToken($apiKey)->get('https://api.openai.com/v1/dashboard/billing/usage', [
            'start_date' => $start,
            'end_date' => $end,
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Échec de la récupération des données'], $response->status());
    }
}
