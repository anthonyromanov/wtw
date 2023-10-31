<?php

namespace App\Services\MovieFinder;

use App\Models\Comment;
use Carbon\Carbon;
use App\Services\MovieFinder\Movie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AcademyRepository implements RemoteRepositoryInterface
{
    private string $baseUrl;
    private int $cachingTime;

    public function __construct()
    {
        $this->baseUrl = config('custom.academy.base_url');
        $this->cachingTime = (int)config('custom.academy.caching_time');
    }

    /**
     * Находит фильм по его IMDB ID.
     *
     * @param string $movieId IMDB ID фильма.
     * @return array|null Данные фильма в виде массива или null, если фильм не найден.
     */
    public function find(string $imdbId): ?array
    {
        $cacheKey = 'movie_'.$imdbId;

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = Http::get($this->baseUrl . "films/" . $imdbId);

        if (!$response->successful()) {
            return null;
        }

        $movieData = $response->json();

        $filmData = new Movie(
            $movieData['name'] ?? null,
            $movieData['desc'] ?? null,
            $movieData['director'] ?? null,
            (int) ($movieData['released'] ?? 0),
            (int) ($movieData['run_time'] ?? 0),
            $movieData['imdb_id'] ?? null,
            $movieData['actors'] ?? null,
            $movieData['genres'] ?? null
        );

        $filmData->poster_image = $movieData['poster'] ?? null;
        $filmData->preview_image = $movieData['icon'] ?? null;
        $filmData->background_image = $movieData['background'] ?? null;
        $filmData->video_link = $movieData['video'] ?? null;
        $filmData->preview_video_link = $movieData['preview'] ?? null;

        $data = $filmData->toArray();
        $cachingTimeCarbon = Carbon::now()->addSeconds($this->cachingTime);
        Cache::put($cacheKey, $data, $cachingTimeCarbon);

        return $data;
    }

    /**
     * Получение новых комментариев для фильмов.
     *
     * @return array|null Новые комментарии в виде массива или null, если новых комментариев нет.
     */
    public function getNewComments(): ?array
    {
        $lastCommentDate = Comment::getLastExternalCommentDate();

        $params = [];
        if ($lastCommentDate) {
            $params['after'] = $lastCommentDate;
        }

        $response = Http::get($this->baseUrl . "comments/", $params);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}
