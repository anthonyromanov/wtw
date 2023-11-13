<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{

/**
 * @psalm-api
 */
    public function run(): void
    {
        User::factory()->count(10)->create();
        /** @psalm-suppress UndefinedMagicMethod  */
        User::factory()->count(5)->moderator()->create();
    }
}
