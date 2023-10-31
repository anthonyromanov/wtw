<?php

namespace Database\Factories;

use App\Models\Star;
use Illuminate\Database\Eloquent\Factories\Factory;

class StarFactory extends Factory
{
    protected $model = Star::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
        ];
    }
}
