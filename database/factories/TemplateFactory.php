<?php

namespace Database\Factories;

use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;

class TemplateFactory extends Factory
{
    protected $model = Template::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(8),
            'content' => $this->faker->paragraph(4),
            'language' => $this->faker->randomElement(['fr', 'ar']),
            'category' => $this->faker->word(),
        ];
    }
}
