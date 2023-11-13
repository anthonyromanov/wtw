<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilmRequest;
use App\Http\Requests\StoreFilmRequest;
use App\Http\Requests\UpdateFilmRequest;
use App\Http\Responses\Base;
use App\Http\Responses\Fail;
use App\Http\Responses\SuccessPagination;
use App\Http\Responses\Success;
use App\Jobs\CreateFilmJob;
use App\Models\Film;
use App\Services\StarService;
use App\Services\GenreService;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * @psalm-api
 */
class FilmController extends Controller
{
    /**
     * Получение списка фильмов.
     * @param FilmRequest $request
     *
     * @return Base
     */
    public function index(FilmRequest $request): Base
    {
        $pageCount = config('custom.films_per_page');
        $genre = $request->query('genre');
        $status = $request->query('status', Film::STATUS_READY);
        $order_by = $request->query('order_by', Film::ORDER_BY_RELEASED);
        $order_to = $request->query('order_to', Film::ORDER_TO_DESC);

        if (Gate::denies('view-films-with-status', $status)) {
            return new Fail("Недостаточно прав для просмотра фильмов в статусе $status", Response::HTTP_FORBIDDEN);
        }
        /** @psalm-suppress TooManyArguments */
        $films = Film::query()
            ->select('id', 'name', 'preview_image', 'released', 'rating')
            ->when($genre, function ($query, $genre) {
                return $query->whereRelation('genres', 'name', $genre);
            })
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy($order_by, $order_to)
            ->paginate($pageCount);

        return new SuccessPagination($films);
    }

    /**
     * Добавление фильма в базу данных.
     * @param StoreFilmRequest $request
     *
     * @return Base
     */
    public function store(StoreFilmRequest $request)
    {
        $imdbId = $request->input('imdb_id');

        $data = [
            'imdb_id' => $imdbId,
            'status' => Film::STATUS_PENDING,
        ];

        Film::create($data);
        CreateFilmJob::dispatch($data);

        return new Success($data, Response::HTTP_CREATED);
    }

    /**
     * Получение информации о фильме.
     * @param Film $film
     *
     * @return Base
     */
    public function show(Film $film): Base
    {
        return new Success($film);
    }

    /**
     * Редактирование фильма.
     * @param UpdateFilmRequest $request
     * @param Film $film
     *
     * @return Base
     */
    public function update(UpdateFilmRequest $request, Film $film): Base
    {
        $film->update($request->validated());

        if ($request->has('starring')) {
            app(StarService::class)->syncStars($film, $request->input('starring'));
        }

        if ($request->has('genre')) {
            app(GenreService::class)->syncGenres($film, $request->input('genre'));
        }

        return new Success($film);
    }
}
