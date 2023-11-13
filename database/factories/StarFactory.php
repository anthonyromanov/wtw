<?php

namespace Database\Factories;

use App\Models\Star;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @psalm-api
 * @template-extends Factory<Star>
 */
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
