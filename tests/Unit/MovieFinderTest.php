<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Services\MovieFinder\RemoteRepositoryInterface;
use App\Services\MovieFinder\MovieFinder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class MovieFinderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тестирование метода getMovie() класса MovieService.
     */
    public function testMovieServiceData(): void
    {
        $imdbId = 'tt3896198';

        $newMovie = Film::factory()->make([
            'imdb_id' => $imdbId,
        ])->toArray();

        $mockMovieRepository = Mockery::mock(RemoteRepositoryInterface::class);
        $mockMovieRepository->shouldReceive('find')
            ->with($imdbId)
            ->once()
            ->andReturn($newMovie);      
 
        $movieFinder = new MovieFinder($mockMovieRepository);

        $result = $movieFinder->getMovie($imdbId);

        $this->assertEquals($newMovie, $result);
    }
}
