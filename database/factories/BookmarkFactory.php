<?php

namespace Database\Factories;

use App\Models\Bookmark;
use App\Models\User;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookmarkFactory extends Factory
{
    protected $model = Bookmark::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'recipe_id' => Recipe::factory(),
        ];
    }
}
