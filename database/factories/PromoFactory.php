<?php

namespace Database\Factories;

use App\Models\Film;
use App\Models\Promo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @psalm-api
 * @template-extends Factory<Promo>
 */
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
