<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IAControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        // Désactive l'auth obligatoire si middleware auth:api présent
        \Illuminate\Support\Facades\Route::middleware([])->group(function () {
            \Illuminate\Support\Facades\Route::post('/api/generer-texte', [\App\Http\Controllers\IAController::class, 'genererTexte']);
        });
    }

    /** @test */
    public function it_returns_error_if_prompt_is_missing()
    {
        $response = $this->postJson('/api/generer-texte', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['prompt']);
    }

    /** @test */
    public function it_returns_generated_text_on_success()
    {
        // Simule la réponse du microservice Flask
        \Illuminate\Support\Facades\Http::fake([
            'localhost:5000/generate' => [
                'generated_text' => 'Texte généré de test.'
            ]
        ]);

        $response = $this->postJson('/api/generer-texte', [
            'prompt' => 'Rédige un contrat de prestation.'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'generated_text' => 'Texte généré de test.'
            ]);
    }

    /** @test */
    public function it_returns_error_if_microservice_unavailable()
    {
        \Illuminate\Support\Facades\Http::fake([
            'localhost:5000/generate' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
            }
        ]);

        $response = $this->postJson('/api/generer-texte', [
            'prompt' => 'Test indisponibilité.'
        ]);

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'message' => 'Le microservice IA est indisponible.'
            ]);
    }
}
