<?php

namespace Database\Factories;

use App\Models\Film;
use Illuminate\Database\Eloquent\Factories\Factory;

class FilmFactory extends Factory
{
    protected $model = Film::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence(),
            'poster_image' => $this->faker->imageUrl(),
            'preview_image' => $this->faker->imageUrl(),
            'background_image' => $this->faker->imageUrl(),
            'background_color' => $this->faker->hexColor(),
            'video_link' => 'https://some-link/video.mp4',
            'preview_video_link' => 'https://some-link/preview-video.mp4',
            'description' => $this->faker->paragraph(),
            'director' => $this->faker->name(),
            'released' => $this->faker->year(),
            'run_time' => $this->faker->numberBetween(60, 240),
            'rating' => $this->faker->randomFloat(1, 1, 10),
            'scores_count' => $this->faker->numberBetween(0, 10000),
            'imdb_id' => 'tt' . $this->faker->numberBetween(0000001, 9999999),
            'status' => $this->faker->randomElement(['pending', 'moderate', 'ready']),
        ];
    }
}
