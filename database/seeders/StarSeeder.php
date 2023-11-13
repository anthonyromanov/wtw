<?php

namespace Database\Seeders;

use App\Models\Star;
use Illuminate\Database\Seeder;

class StarSeeder extends Seeder
{
/**
 * @psalm-api
 */
    public function run(): void
    {
        Star::factory()->count(20)->create();
    }
}
