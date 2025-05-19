<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecipeFactory extends Factory
{
    protected $model = Recipe::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'cooking_time' => $this->faker->numberBetween(10, 120),
            'category' => 'main_course',
            'image_url' => null,
            'video_url' => null,
            'user_id' => User::factory(),
        ];
    }
}