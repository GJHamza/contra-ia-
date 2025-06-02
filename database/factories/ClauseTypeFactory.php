<?php

namespace Database\Factories;

use App\Models\ClauseType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClauseTypeFactory extends Factory
{
    protected $model = ClauseType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
        ];
    }
}
