<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Film;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilmTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тестирование метода calculateRating() класса Film.
     */
    public function testFilmRating(): void
    {
        $usersCount = 3;

        $film = Film::factory()->create();
        $users = User::factory()->count($usersCount)->create();

        foreach ($users as $index => $user) {
            Comment::factory()->create([
                'film_id' => $film->id,
                'user_id' => $user->id,
            ]);
        }
        $film->calculateRating();

        $averageRating = $film->comments()->avg('rating');
        $averageRating = $averageRating ? round($averageRating, 1) : 0;

        $this->assertEquals($averageRating, $film->rating);
    }
}
