<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ContainerController;
use App\Http\Controllers\ClauseController;
use App\Http\Controllers\ClauseTypeController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\GPTPDFController;
use App\Http\Controllers\GptHistoryController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\HuggingFaceController;
use App\Http\Controllers\IAController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ✅ Authentification (accès public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 🔐 Routes protégées (requièrent un token Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // 👤 Utilisateur connecté
    Route::get('/user', fn(Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);

    // 📄 Documents
    Route::apiResource('documents', DocumentController::class)->except(['create', 'edit']);
    Route::get('/documents/{id}/download', [DocumentController::class, 'download']);
    Route::get('/documents/{id}/pdf', [PDFController::class, 'generate']);

    // 📦 Containers
    Route::apiResource('containers', ContainerController::class)->except(['create', 'edit']);
    Route::post('/containers/{id}/assign-documents', [ContainerController::class, 'assignDocuments']);
    Route::post('/containers/{id}/assign-clauses', [ContainerController::class, 'assignClauses']);
    Route::delete('/containers/{containerId}/remove-document/{documentId}', [ContainerController::class, 'removeDocument']);
    Route::delete('/containers/{containerId}/remove-clause/{clauseId}', [ContainerController::class, 'removeClause']);

    // 📚 Types de clauses
    Route::apiResource('clause-types', ClauseTypeController::class)->except(['create', 'edit']);

    // 📑 Clauses
    Route::apiResource('clauses', ClauseController::class)->except(['create', 'edit']);

    // 🧠 Templates & génération AI
    Route::apiResource('templates', TemplateController::class)->except(['create', 'edit']);
    Route::post('/templates/generate', [TemplateController::class, 'generateWithAI']);
    
    // 🤖 Génération via Hugging Face
    Route::post('/huggingface/generate', [GPTPDFController::class, 'generateText']);
    Route::post('/generer-texte', [IAController::class, 'genererTexte']);

});

// Routes admin protégées par le middleware is_admin
Route::middleware(['auth:sanctum', 'is_admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/users', [AdminController::class, 'users']);
    Route::post('/users', [AdminController::class, 'storeUser']);
    Route::put('/users/{id}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
    Route::get('/documents', [AdminController::class, 'documents']);
    Route::get('/documents/{id}', [AdminController::class, 'showDocument']);
    Route::delete('/documents/{id}', [AdminController::class, 'deleteDocument']);
});