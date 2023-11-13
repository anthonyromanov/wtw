<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
/**
 * @psalm-api
 */
    public function run(): void
    {
        Genre::factory()->count(20)->create();
    }
}
