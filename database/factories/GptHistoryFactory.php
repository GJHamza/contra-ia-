<?php

namespace Database\Factories;

use App\Models\GptHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GptHistoryFactory extends Factory
{
    protected $model = GptHistory::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'prompt' => $this->faker->sentence(6),
            'response' => $this->faker->paragraph(3),
        ];
    }
}
