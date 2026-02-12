<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Compte;

class CompteSeeder extends Seeder
{

    public function run(): void
    {

        Compte::factory()->count(4)->create();
    }
}
