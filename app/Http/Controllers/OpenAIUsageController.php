<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\OpenAIUsage;

class OpenAIUsageController extends Controller
{
    public function getUsage(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());

        $usages = OpenAIUsage::whereBetween('date', [$start, $end])->get();

        $totalTokens = $usages->sum('tokens');
        $totalCost = $usages->sum('cost');

        $daily = $usages->groupBy('date')->map(function ($items, $date) {
            return [
                'date' => $date,
                'tokens' => $items->sum('tokens'),
                'cost' => $items->sum('cost'),
            ];
        })->values();

        return response()->json([
            'tokens' => $totalTokens,
            'cost' => $totalCost,
            'daily' => $daily,
        ]);
    }
}
