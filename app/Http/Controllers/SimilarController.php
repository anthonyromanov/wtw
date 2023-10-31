<?php

namespace App\Http\Controllers;

use App\Http\Responses\Base;
use App\Http\Responses\Success;
use App\Models\Film;
use Symfony\Component\HttpFoundation\Response;

class SimilarController extends Controller
{
    /**
     * Получение списка фильмов, похожих на данный.
     *
     * @param Film $film.
     * @return Base
     */
    public function index(Film $film): Base
    {
        $status = Film::STATUS_READY;
        $similarFilmsCount = 4;
        $filmGenres = $film->genres->pluck('id');

        $similarFilms = Film::where('status', $status)
            ->where('id', '!=', $film->id)
            ->whereHas('genres', function ($query) use ($filmGenres) {
                $query->whereIn('genres.id', $filmGenres);
            })
            ->withCount(['genres as genres_count' => function ($query) use ($filmGenres) {
                $query->whereIn('genres.id', $filmGenres);
            }])
            ->orderByDesc('genres_count')
            ->limit($similarFilmsCount)
            ->get();

        if ($similarFilms->isEmpty()) {
            return new Success(null, Response::HTTP_NO_CONTENT);
        }

        return new Success($similarFilms);
    }
}
