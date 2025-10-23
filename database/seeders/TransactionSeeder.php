<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Compte;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Option 1 : pour comptes existants, ajouter transactions
        Compte::all()->each(function ($compte) {
            // créer entre 1 et 5 transactions pour chaque compte
            Transaction::factory()->count(rand(1,5))->create([
                'compte_id' => $compte->id,
            ]);
        });

        // Option 2 : créer des comptes + transactions
        // Transaction::factory()->count(20)->create();
    }
}
