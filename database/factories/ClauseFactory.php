<?php

namespace Database\Factories;

use App\Models\Clause;
use App\Models\Container;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClauseFactory extends Factory
{
    protected $model = Clause::class;

    public function definition(): array
    {
        return [
            'container_id' => Container::factory(),
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->paragraph(3),
        ];
    }
}
