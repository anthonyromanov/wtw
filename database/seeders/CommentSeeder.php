<?php

namespace Database\Seeders;

use App\Models\Comment;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
/**
 * @psalm-api
 */
    public function run(): void
    {
        Comment::factory()->count(10)->create();
    }
}
