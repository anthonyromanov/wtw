<?php

namespace App\Services\MovieFinder;

class MovieFinder
{
    private RemoteRepositoryInterface $repository;

    /**
     * Конструктор класса MovieService.
     *
     * @param  RemoteRepositoryInterface  $repository Задаёт репозиторий для работы с фильмами.
     */
    public function __construct(RemoteRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Осуществляет получение информации о фильме по его IMDB ID через репозиторий.
     *
     * @param  string  $imdbId IMDB ID фильма.
     * @return array|null Возвращает массив с информацией о фильме или null, если фильм не найден.
     */
    public function getMovie(string $imdbId): ?array
    {
        return $this->repository->find($imdbId);
    }
}
