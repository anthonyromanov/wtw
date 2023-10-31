<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Film;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CommentsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Возвращает типичную структуру комментария в формате JSON.
     *
     * @return array
     */
    private function getTypicalCommentStructure(): array
    {
        return [
            'data' => [
                'id',
                'comment_id',
                'film_id',
                'text',
                'rating',
                'user_id',
                'author_name',
                'created_at',
            ],
        ];
    }

    /**
     * Тестирование метода index.
     */
    public function testIndex(): void
    {
        $film = Film::factory()->create();
        $commentCount = 10;

        Comment::factory()->count($commentCount)->create([
            'film_id' => $film->id,
        ]);

        $response = $this->getJson("/api/films/{$film->id}/comments");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'comment_id',
                    'film_id',
                    'text',
                    'rating',
                    'user_id',
                    'author_name',
                    'is_external',
                    'created_at',
                ],
            ],
        ]);

        $testComments = $response->json('data');
        $dbComments = Comment::where('film_id', $film->id)
            ->orderByDesc('created_at')
            ->get()
            ->toArray();

        $this->assertEquals($dbComments, $testComments);
    }

    /**
     * Тестирование автора внешнего комментария.
     */
    public function testExternalCommentAuthorName(): void
    {
        $film = Film::factory()->create();

        Comment::factory()->create([
            'film_id' => $film->id,
            'is_external' => true,
        ]);

        $response = $this->getJson("/api/films/{$film->id}/comments");
        $comments = $response->json('data');

        $response->assertStatus(Response::HTTP_OK);
        $this->assertCount(1, $comments);
        $this->assertEquals(Comment::ANONYMOUS_NAME, $comments[0]['author_name']);
    }

    /**
     * Тестирование метода store при отсутствии авторизации.
     */
    public function testStoreUnauthorized(): void
    {
        $film = Film::factory()->create();

        $data = [
            'text' => 'Тестовый комментарий. На Кинопоиске статьи о кино полная шляпа.',
            'rating' => 8,
        ];

        $response = $this->postJson("/api/films/{$film->id}/comments", $data);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Запрос требует аутентификации.',
        ]);
    }

    /**
     * Тестирование метода store при авторизации.
     */
    public function testStoreAuthorized(): void
    {
        $user = User::factory()->create();
        $film = Film::factory()->create();

        $data = [
            'text' => 'Тестовый комментарий. На Фильм.Ру статьи о кино хуже, чем на Кинопоиске.',
            'rating' => 8,
        ];

        $response = $this->actingAs($user)->postJson("/api/films/{$film->id}/comments", $data);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure($this->getTypicalCommentStructure());
        $response->assertJsonPath('data.comment_id', null);

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'film_id' => $film->id,
            'text' => $data['text'],
            'rating' => $data['rating'],
        ]);
    }

    /**
     * Тестирование метода store с некорректными данными.
     */
    public function testStoreValidationError(): void
    {
        $user = User::factory()->create();
        $film = Film::factory()->create();

        $data = [
            'text' => 'Краткость - сестра таланта',
            'rating' => 11,
        ];

        $response = $this->actingAs($user)->postJson("/api/films/{$film->id}/comments", $data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson([
            'message' => 'Переданные данные не корректны.',
        ]);
        $response->assertJsonValidationErrors([
            'text',
            'rating',
        ]);
    }

    /**
     * Тестирование метода store для ответа на комментарий.
     */
    public function testStoreReplyToComment(): void
    {
        $user = User::factory()->create();
        $film = Film::factory()->create();
        $parentComment = Comment::factory()->create(['film_id' => $film->id]);

        $data = [
            'text' => 'Американцы считают в сексуальной революции в США виноваты русские. Путин тогда только родился, но зато Набоков уже написал Лолиту.',
            'rating' => 8,
            'comment_id' => $parentComment->id,
        ];

        $response = $this->actingAs($user)->postJson("/api/films/{$film->id}/comments", $data);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure($this->getTypicalCommentStructure());
        $response->assertJsonPath('data.comment_id', $parentComment->id);

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'film_id' => $film->id,
            'text' => $data['text'],
            'rating' => $data['rating'],
            'comment_id' => $data['comment_id'],
        ]);
    }

    /**
     * Тестирование метода update при отсутствии авторизации.
     */
    public function testUpdateUnauthorized(): void
    {
        $comment = Comment::factory()->create();

        $data = [
            'text' => 'Крайний фильм про Индиана Джонса очень неплохой такой. Мне понравился.',
            'rating' => 9,
        ];

        $response = $this->patchJson("/api/comments/{$comment->id}", $data);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Запрос требует аутентификации.',
        ]);
    }

    /**
     * Тестирование метода update с запретом доступа.
     */
    public function testUpdateForbidden(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $data = [
            'text' => 'Крайний фильм про Индиана Джонса очень неплохой такой. Мне понравился. Но фейс-контроль не был пройден',
            'rating' => 9,
        ];

        $response = $this->actingAs($user)->patchJson("/api/comments/{$comment->id}", $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertJson([
            'message' => 'Недостаточно прав.',
        ]);
    }

    /**
     * Тестирование метода update с валидными данными.
     */
    public function testUpdateSuccess(): void
    {
        $comment = Comment::factory()->create();

        $data = [
            'text' => 'Крайний фильм Стражи галактики очень неплохой такой. Мне понравился. Фейс-контроль был пройден',
            'rating' => 9,
        ];

        $response = $this->actingAs($comment->user)->patchJson("/api/comments/{$comment->id}", $data);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure($this->getTypicalCommentStructure());
        $response->assertJsonPath('data.text', $data['text']);
        $response->assertJsonPath('data.rating', $data['rating']);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'text' => $data['text'],
            'rating' => $data['rating'],
        ]);
    }

    /**
     * Тестирование метода update для модератора.
     */
    public function testUpdateByModeratorSuccess(): void
    {
        $user = User::factory()->moderator()->create();
        $comment = Comment::factory()->create();

        $data = [
            'text' => 'Крайний фильм Форсаж очень неплохой такой. Мне понравился. Но много вопросов.',
            'rating' => 9,
        ];

        $response = $this->actingAs($user)->patchJson("/api/comments/{$comment->id}", $data);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure($this->getTypicalCommentStructure());
        $response->assertJsonPath('data.text', $data['text']);
        $response->assertJsonPath('data.rating', $data['rating']);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'text' => $data['text'],
            'rating' => $data['rating'],
        ]);
    }

    /**
     * Тестирование метода update с невалидными данными.
     */
    public function testUpdateValidationError(): void
    {
        $comment = Comment::factory()->create();

        $data = [
            'text' => 'Креатив иссяк. Ваши данные невалидны и точка.',
            'rating' => 11,
        ];

        $response = $this->actingAs($comment->user)->patchJson("/api/comments/{$comment->id}", $data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson([
            'message' => 'Переданные данные не корректны.',
        ]);
        $response->assertJsonValidationErrors([
            'text',
            'rating',
        ]);
    }

    /**
     * Тестирование метода destroy без авторизации.
     */
    public function testDestroyUnauthorized(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Запрос требует аутентификации.',
        ]);
    }

    /**
     * Тестирование метода destroy с отсутствующими правами.
     */
    public function testDestroyForbidden(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertJson([
            'message' => 'Недостаточно прав.',
        ]);
    }

    /**
     * Тестирование метода destroy с валидными данными.
     */
    public function testDestroySuccess(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->actingAs($comment->user)->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }

    /**
     * Тестирование метода destroy для модератора.
     */
    public function testDestroyByModeratorSuccess(): void
    {
        $user = User::factory()->moderator()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }

    /**
     * Тестирование метода destroy с дочерними комментариями.
     */
    public function testDestroyWithChildrenSuccess(): void
    {
        $childrenCount = 3;

        $user = User::factory()->moderator()->create();
        $comment = Comment::factory()->create();

        $childrenComments = Comment::factory()->count($childrenCount)->create([
            'film_id' => $comment->film_id,
            'comment_id' => $comment->id,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);

        foreach ($childrenComments as $childComment) {
            $this->assertDatabaseMissing('comments', [
                'id' => $childComment->id,
            ]);
        }
    }
}