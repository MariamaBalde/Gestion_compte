<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Transaction;
use App\Models\Compte;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['depot', 'retrait']);
        $montant = $this->faker->randomFloat(2, 100, 200000);

        return [
            'compte_id' => Compte::factory(),
            'type_transaction' => $type,
            'montant' => $montant,
            'devise' => 'FCFA',
            // 'reference' laissé vide pour être généré par le modèle
            'solde_apres' => null, // optionnel, on peut calculer plus tard
            'statut_transaction' => 'effectue',
        ];
    }
}
