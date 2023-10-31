<?php

namespace App\Services\MovieFinder;

use App\Services\MovieFinder\Movie;
use GuzzleHttp\Client;

class RemoteRepository implements RemoteRepositoryInterface
{

    private Client $client;
    private string $apiKey;
    private string $baseUrl;

    /**
     * Конструктор класса MovieRemotebRepository.
     *
     * @param Client $client Клиент HTTP для отправки запросов.
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->apiKey = config('custom.omdb.api_key');
        $this->baseUrl = config('custom.omdb.base_url');
    }

    /**
    * Находит фильм по его IMDB ID.
    *
    * @param string $imdbId IMDB ID фильма.
    * @return array|null Данные фильма в виде массива или null, если фильм не найден.
    */
   public function getMovie(string $movieId): ?array
   {
           
       $response = $this->client->request('GET', $this->baseUrl, [
           'query' => [
               'apikey' => $this->apiKey,
               'i' => $movieId,
           ],
       ]);

       if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
           return null;
       }

       $payload = json_decode($response->getBody()->getContents(), true);

       $movie = new Movie(
           $payload['Title'] ?? null,
           $payload['Plot'] ?? null,
           $payload['Director'] ?? null,
           (int) ($payload['Year'] ?? 0),
           (int) ($payload['Runtime'] ?? 0),
           $payload['imdbID'] ?? null,
           array_map('trim', explode(',', $payload['Actors'] ?? '')),
           array_map('trim', explode(',', $payload['Genre'] ?? ''))
       );

       $movie->poster_image = $$payload['Poster'] ?? null;
       $movie->rating = (float) ($payload['imdbRating'] ?? 0);
       $movie->scores_count = (int) str_replace(',', '', $payload['imdbVotes'] ?? '0');

       $data = $movie->toArray();

       return $data;
   }
}
