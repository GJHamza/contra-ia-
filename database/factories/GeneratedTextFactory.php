<?php

namespace Database\Factories;

use App\Models\GeneratedText;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GeneratedTextFactory extends Factory
{
    protected $model = GeneratedText::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'prompt' => $this->faker->sentence(6),
            'response' => $this->faker->paragraph(3),
        ];
    }
}
