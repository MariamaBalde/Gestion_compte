<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Un admin connu
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'secret123', // sera hashé
            'role' => 'admin',
        ]);

        // Quelques utilisateurs
        User::factory()->count(5)->create();
    }
}
