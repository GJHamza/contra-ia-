<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraph(6),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['en_attente', 'en_cours', 'termine']),
            'generated_at' => now(),
        ];
    }
}
