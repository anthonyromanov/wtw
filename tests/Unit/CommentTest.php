<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тестирование получения имени автора комментария.
     */
    public function testRegisterAuthorName(): void
    {
        $user = User::factory()->create(['name' => 'Райан Рейнольдс']);
        $comment = Comment::factory()->for($user, 'user')->create(['is_external' => false]);

        $authorName = $comment->author_name;

        $this->assertEquals('Райан Рейнольдс', $authorName);
    }

    /**
     * Тестирование получения имени анонимного автора комментария.
     */
    public function testAnonymousAuthorName(): void
    {
        $comment = Comment::factory()->create(['is_external' => true]);

        $authorName = $comment->author_name;

        $this->assertEquals(Comment::ANONYMOUS_NAME, $authorName);
    }
}
