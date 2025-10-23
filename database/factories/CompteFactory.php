<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Client;
use App\Models\Compte;
use Illuminate\Support\Str;



/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compte>
 */
class CompteFactory extends Factory
{
        protected $model = Compte::class;

    public function definition(): array
    {
        $type_compte = ['cheque', 'epargne', 'courant'];
        return [
            'client_id' => Client::factory(),
            'type_compte' => $this->faker->randomElement($type_compte),
            'solde' => $this->faker->numberBetween(10000, 1000000),
            'devise' => 'FCFA',
            'statut' => 'actif',
        ];
    }



}
