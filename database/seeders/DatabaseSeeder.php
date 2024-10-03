<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'ali',
            'email' => 'ali@eg.com',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'name' => 'ali2',
            'email' => 'ali2@eg.com',
            'password' => bcrypt('password'),
        ]);
    }
}
