<?php

namespace App\Http\Controllers;

use App\Http\Responses\Base;
use App\Http\Responses\Fail;
use App\Http\Responses\Success;
use App\Models\Film;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FavoriteController extends Controller
{
    /**
     * Получение списка избранных фильмов.
     *
     * @return Base
     */
    public function index(): Base
    {
        /** @var User $user */
        $user = Auth::user();
        $favoriteFilms = $user->favoriteFilms()->orderBy('created_at', 'desc')->get();

        return new Success($favoriteFilms);
    }

    /**
     * Добавление фильма в избранное.
     *
     * @return Base
     */
    public function store(Film $film): Base
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasFavorite($film->id)) {
            return new Fail('Фильм уже в избранном', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->favoriteFilms()->attach($film);

        return new Success();
    }

    /**
     * Удаление фильма из избранного.
     *
     * @return Base
     */
    public function destroy(Film $film): Base
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasFavorite($film->id)) {
            return new Fail('Фильм не найден в избранном', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->favoriteFilms()->detach($film);

        return new Success();
    }
}
