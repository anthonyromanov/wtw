<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            GenreSeeder::class,
            StarSeeder::class,
            FilmSeeder::class,
            CommentSeeder::class,
            PromoSeeder::class,
            StarFilmSeeder::class,
            FilmGenreSeeder::class,
            UserFavoriteSeeder::class,
        ]);
    }
}