<?php

namespace Database\Seeders;

use App\Models\Film;
use Illuminate\Database\Seeder;

class FilmSeeder extends Seeder
{
/**
 * @psalm-api
 */
    public function run(): void
    {
        Film::factory()->count(10)->create();
    }
}
