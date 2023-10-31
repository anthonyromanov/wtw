<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'),
            'avatar' => $this->faker->imageUrl(63, 63, 'people'),
            'role' => User::ROLE_USER,
        ];
    }

    public function moderator()
    {
        return $this->state([
            'role' => User::ROLE_MODERATOR,
        ]);
    }
}
