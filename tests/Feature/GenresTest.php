<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GenresTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тестирование метода index, который возвращает список жанров.
     */
    public function testIndex(): void
    {
        Genre::factory()->count(3)->create();

        $response = $this->getJson('/api/genres');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                ],
            ],
        ]);
    }

    /**
     * Тестирование метода update при отсутствии авторизации.
     */
    public function testUpdateUnauthorized(): void
    {
        $genre = Genre::factory()->create();
        $newName = 'Новый жанр';

        $response = $this->patchJson("/api/genres/{$genre->id}", [
            'name' => $newName,
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Запрос требует аутентификации.',
        ]);
    }

    /**
     * Тестирование метода update при авторизации без достаточных прав.
     */
    public function testUpdateAuthorized(): void
    {
        $genre = Genre::factory()->create();
        $user = User::factory()->create();
        $newName = 'Новый жанр';

        $response = $this->actingAs($user)
            ->patchJson("/api/genres/{$genre->id}", [
                'name' => $newName,
            ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertJson([
            'message' => 'Недостаточно прав.',
        ]);
    }

    /**
     * Тестирование метода update при авторизации с правами модератора.
     */
    public function testUpdateModerator(): void
    {
        $genre = Genre::factory()->create();
        $user = User::factory()->moderator()->create();
        $newName = 'Новый жанр';

        $response = $this->actingAs($user)
            ->patchJson("/api/genres/{$genre->id}", [
                'name' => $newName,
            ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                'id' => $genre->id,
                'name' => $newName,
            ],
        ]);

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => $newName,
        ]);
    }

    /**
     * Тестирование валидации при обновлении жанра.
     */
    public function testUpdateValidationError(): void
    {
        $genre = Genre::factory()->create();
        $user = User::factory()->moderator()->create();
        $newName = '';

        $response = $this->actingAs($user)
            ->patchJson("/api/genres/{$genre->id}", [
                'name' => $newName,
            ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson([
            'message' => 'Переданные данные не корректны.',
            'errors' => [
                'name' => [
                    'Поле Название обязательно для заполнения.',
                ],
            ],
        ]);
    }
}
