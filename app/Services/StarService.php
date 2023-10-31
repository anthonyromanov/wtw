<?php

namespace App\Services;

use App\Models\Star;
use App\Models\Film;

class StarService
{
    /**
     * Синхронизирует актеров фильма.
     *
     * @param Film $film Фильм, для которого необходимо синхронизировать актеров.
     * @param array $starsNames Массив имен актеров.
     * @return void
     */
    public function syncStars(Film $film, array $starsNames): void
    {
        $film->stars()->detach();
        foreach ($starsNames as $starsName) {
            $star = Star::firstOrCreate(['name' => $starName]);
            $film->stars()->attach($star);
        }
    }
}