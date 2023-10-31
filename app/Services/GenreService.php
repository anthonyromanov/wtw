<?php

namespace App\Services;

use App\Models\Film;
use App\Models\Genre;

class GenreService
{
    /**
     * Синхронизирует жанры фильма.
     *
     * @param Film $film Фильм, для которого необходимо синхронизировать жанры.
     * @param array $genresNames Массив названий жанров.
     * @return void
     */
    public function syncGenres(Film $film, array $genresNames): void
    {
        $film->genres()->detach();
        foreach ($genresNames as $genreName) {
            $genre = Genre::firstOrCreate(['name' => $genreName]);
            $film->genres()->attach($genre);
        }
    }
}
