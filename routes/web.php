<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__.'/auth.php';

Route::get('/test-ai', function () {
    $response = Http::post('http://127.0.0.1:5000/generate-text', [
        'text' => 'Créer un contrat de location pour une voiture en français.',
    ]);

    return $response->json();
});