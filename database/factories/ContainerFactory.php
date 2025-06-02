<?php

namespace Database\Factories;

use App\Models\Container;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContainerFactory extends Factory
{
    protected $model = Container::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(8),
        ];
    }
}
