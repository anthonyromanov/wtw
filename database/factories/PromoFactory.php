<?php

namespace Database\Factories;

use App\Models\Film;
use App\Models\Promo;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromoFactory extends Factory
{
    protected $model = Promo::class;

    public function definition()
    {
        return [
            'film_id' => Film::factory(),
        ];
    }
}
