<?php

namespace Tests\Feature;

use App\Jobs\CreateFilmJob;
use App\Models\Film;
use App\Services\StarService;
use App\Services\FilmService;
use App\Services\GenreService;
use App\Services\MovieFinder\RemoteRepositoryInterface;
use App\Services\MovieFinder\MovieFinder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class FilmJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тестирование выполнения задачи CreateFilmJob.
     */
    public function testCreateFilmJob(): void
    {
        $imdbId = 'tt3896198';

        $data = [
            'imdb_id' => $imdbId,
            'status' => Film::STATUS_PENDING,
        ];

        $newMovie = Film::factory()->make([
            'imdb_id' => $imdbId,
        ])->toArray();

        $mockMovieRepository = Mockery::mock(RemoteRepositoryInterface::class);
        $mockMovieRepository->shouldReceive('find')
            ->with($imdbId)
            ->once()
            ->andReturn($newMovie);
        $movieFinder = new MovieFinder($mockMovieRepository);

        $starService = new StarService();
        $genreService = new GenreService();
        $filmService = new FilmService($starService, $genreService);

        Film::factory()->create($data);
        $job = new CreateFilmJob($data);
        $job->handle($movieFinder, $filmService);

        $this->assertDatabaseHas('films', [
            'imdb_id' => $imdbId,
            'status' => Film::STATUS_MODERATE,
        ]);
    }
}
