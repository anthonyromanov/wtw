<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Film;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'film_id' => Film::factory(),
            'comment_id' => null,
            'text' => $this->faker->paragraph(),
            'rating' => $this->faker->numberBetween(1, 10),
            'is_external' => false,
        ];
    }
}
