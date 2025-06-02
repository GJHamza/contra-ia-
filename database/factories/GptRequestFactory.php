<?php

namespace Database\Factories;

use App\Models\GptRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GptRequestFactory extends Factory
{
    protected $model = GptRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'requested_at' => now(),
        ];
    }
}
