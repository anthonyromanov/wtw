<?php

namespace Database\Seeders;

use App\Models\Star;
use App\Models\Film;
use Illuminate\Database\Seeder;

class StarFilmSeeder extends Seeder
{
    public function run(): void
    {
        $films = Film::all();
        $actors = Star::all();

        $films->each(function (Film $film) use ($stars) {
            $randomStars = $stars->random(rand(1, 10));
            $film->stars()->attach($randomStars);
        });
    }
}
