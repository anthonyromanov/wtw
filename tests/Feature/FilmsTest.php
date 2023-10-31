<?php
namespace Tests\Feature;

use App\Jobs\CreateFilmJob;
use App\Models\Film;
use App\Models\Genre;
use App\Models\User;
use App\Services\MovieService\MovieService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class FilmsTest extends TestCase
{
    use RefreshDatabase;

    private function getTypicalFilmStructure(): array
    {
        return [
            'id',
            'name',
            'poster_image',
            'preview_image',
            'background_image',
            'background_color',
            'video_link',
            'preview_video_link',
            'description',
            'director',
            'released',
            'run_time',
            'rating',
            'scores_count',
            'imdb_id',
            'status',
            'starring',
            'genre',
        ];
    }

    public function testIndexAllFilms()
    {
        Film::factory()->count(10)->create(['status' => Film::STATUS_READY]);

        $response = $this->get('/api/films');
        $response->assertStatus(Response::HTTP_OK);
        $responseData = json_decode($response->getContent(), true);
        $response->assertJsonCount(10, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'preview_image',
                ],
            ],
            'current_page',
            'first_page_url',
            'next_page_url',
            'prev_page_url',
            'per_page',
            'total',
        ]);
    }

    public function testIndexFilteredByGenre()
    {
        $genre = Genre::factory()->create();
        Film::factory()->count(5)->create(['status' => Film::STATUS_READY])->each(function ($film) use ($genre) {
            $film->genres()->attach($genre);
        });
        $response = $this->get('/api/films?genre=' . $genre->name);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(5, 'data');
    }

    public function testIndexFilteredByStatusForUser()
    {
        $user = User::factory()->create();

        Film::factory()->count(3)->create(['status' => Film::STATUS_PENDING]);
        Film::factory()->count(3)->create(['status' => Film::STATUS_MODERATE]);

        $response = $this->actingAs($user)->get('/api/films?status=' . Film::STATUS_PENDING);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertJson([
            'message' => 'Недостаточно прав для просмотра фильмов в статусе ' . Film::STATUS_PENDING,
        ]);
        $response = $this->actingAs($user)->get('/api/films?status=' . Film::STATUS_MODERATE);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertJson([
            'message' => 'Недостаточно прав для просмотра фильмов в статусе ' . Film::STATUS_MODERATE,
        ]);
    }

    public function testIndexFilteredByStatusForModerator()
    {
        $moderator = User::factory()->moderator()->create();
        Film::factory()->count(3)->create(['status' => Film::STATUS_PENDING]);
        Film::factory()->count(3)->create(['status' => Film::STATUS_MODERATE]);

        $response = $this->actingAs($moderator)->get('/api/films?status=' . Film::STATUS_PENDING);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(3, 'data');
        $response = $this->actingAs($moderator)->get('/api/films?status=' . Film::STATUS_MODERATE);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(3, 'data');
    }

    public function testIndexFilmsOrderedByReleased()
    {
        Film::factory()->count(5)->create(['status' => Film::STATUS_READY]);

        $response = $this->get('/api/films?order_by=' . Film::ORDER_BY_RELEASED . '&order_to=desc');
        $response->assertStatus(Response::HTTP_OK);
        $responseData = json_decode($response->getContent(), true)['data'];

        for ($i = 1; $i < count($responseData); $i++) {
            $this->assertTrue($responseData[$i - 1][Film::ORDER_BY_RELEASED] >= $responseData[$i][Film::ORDER_BY_RELEASED]);
        }
    }

    public function testIndexFilmsOrderedByRating()
    {
        Film::factory()->count(5)->create(['status' => Film::STATUS_READY]);
        $response = $this->get('/api/films?order_by=' . Film::ORDER_BY_RATING . '&order_to=desc');
        $response->assertStatus(Response::HTTP_OK);
        $responseData = json_decode($response->getContent(), true)['data'];

        for ($i = 1; $i < count($responseData); $i++) {
            $this->assertTrue($responseData[$i - 1][Film::ORDER_BY_RATING] >= $responseData[$i][Film::ORDER_BY_RATING]);
        }
    }

    public function testStoreUnauthorized()
    {
        $data = [
            'imdb_id' => 'tt3896198',
        ];

        $response = $this->postJson("/api/films", $data);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Запрос требует аутентификации.',
        ]);
    }

    public function testStoreAuthorized()
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);

        $data = [
            'imdb_id' => 'tt3896198',
        ];

        $response = $this->actingAs($user)->postJson("/api/films", $data);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertJson([
            'message' => 'Недостаточно прав.',
        ]);
    }

    public function testStoreModerator()
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MODERATOR,
        ]);

        $data = [
            'imdb_id' => 'tt3896198',
        ];
        
        $response = $this->actingAs($user)->postJson("/api/films", $data);
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'data' => $this->getTypicalFilmStructure(),
        ]);
    }

    public function testStoreFilmAlreadyExists()
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MODERATOR,
        ]);

        $film = Film::factory()->create([
            'imdb_id' => 'tt3896198',
        ]);

        $data = [
            'imdb_id' => 'tt3896198',
        ];

        $response = $this->actingAs($user)->postJson("/api/films", $data);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson([
            'message' => 'Переданные данные не корректны.',
        ]);

        $response->assertJsonValidationErrors([
            'imdb_id',
        ]);
    }

    public function testUpdateValidationError()
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MODERATOR,
        ]);

        $data = [
            'imdb_id' => 'Невалидный imdb_id',
        ];

        $response = $this->actingAs($user)->postJson("/api/films", $data);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson([
            'message' => 'Переданные данные не корректны.',
        ]);

        $response->assertJsonValidationErrors([
            'imdb_id',
        ]);
    }

    public function testShowFilm()
    {
        $film = Film::factory()->create(['status' => Film::STATUS_READY]);

        $response = $this->get("/api/films/{$film->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => $this->getTypicalFilmStructure(),
        ]);
    }

    public function testShowFavoriteForAuthorized()
    {
        $user = User::factory()->create();
        $film = Film::factory()->create(['status' => Film::STATUS_READY]);

        $user->favoriteFilms()->attach($film);

        $response = $this->actingAs($user)->get("/api/films/{$film->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => array_merge($this->getTypicalFilmStructure(), ['is_favorite']),
        ]);
        $response->assertJsonPath('data.is_favorite', true);
    }

    public function testShowFavoriteForGuest()
    {
        $film = Film::factory()->create(['status' => Film::STATUS_READY]);

        $response = $this->get("/api/films/{$film->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => $this->getTypicalFilmStructure(),
        ]);
        $response->assertJsonMissing(['data' => ['is_favorite']]);
    }   
}

