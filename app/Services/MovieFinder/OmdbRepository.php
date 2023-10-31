<?php

namespace App\Services\MovieFinder;

use Carbon\Carbon;
use App\Services\MovieFinder;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class OmdbRepository implements RemoteRepositoryInterface
{
    private Client $client;
    private string $apiKey;
    private string $baseUrl;
    private int $cachingTime;

    /**
     * Конструктор класса MovieOmdbRepository.
     *
     * @param Client $client Клиент HTTP для отправки запросов.
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->apiKey = config('custom.omdb.api_key');
        $this->baseUrl = config('custom.omdb.base_url');
        $this->cachingTime = (int)config('custom.omdb.caching_time');
    }

    /**
     * Находит фильм по его IMDB ID.
     *
     * @param string $imdbId IMDB ID фильма.
     * @return array|null Данные фильма в виде массива или null, если фильм не найден.
     */
    public function getMovie(string $imdbId): ?array
    {
        $cacheKey = 'movie_'.$imdbId;

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = $this->client->request('GET', $this->baseUrl, [
            'query' => [
                'apikey' => $this->apiKey,
                'i' => $imdbId,
            ],
        ]);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            return null;
        }

        $movieData = json_decode($response->getBody()->getContents(), true);

        $filmData = new Movie(
            $movieData['Title'] ?? null,
            $movieData['Plot'] ?? null,
            $movieData['Director'] ?? null,
            (int) ($movieData['Year'] ?? 0),
            (int) ($movieData['Runtime'] ?? 0),
            $movieData['imdbID'] ?? null,
            array_map('trim', explode(',', $movieData['Actors'] ?? '')),
            array_map('trim', explode(',', $movieData['Genre'] ?? ''))
        );

        $filmData->poster_image = $movieData['Poster'] ?? null;
        $filmData->rating = (float) ($movieData['imdbRating'] ?? 0);
        $filmData->scores_count = (int) str_replace(',', '', $movieData['imdbVotes'] ?? '0');

        $data = $filmData->toArray();
        $cachingTimeCarbon = Carbon::now()->addSeconds($this->cachingTime);
        Cache::put($cacheKey, $data, $cachingTimeCarbon);

        return $data;
    }
}
