<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Transaction;
use App\Models\Compte;
use App\Models\Client;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function une_transaction_peut_etre_creee_et_liee_a_un_compte()
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create(['client_id' => $client->id, 'solde' => 100000]);

        $transaction = Transaction::create([
            'compte_id' => $compte->id,
            'type' => 'debit',
            'montant' => 10000,
            'devise' => 'FCFA',
            'description' => 'Retrait guichet',
        ]);

        $this->assertNotNull($transaction->id);
        $this->assertMatchesRegularExpression('/^T\d{8}\d{6}$/', $transaction->reference);
        $this->assertEquals($compte->id, $transaction->compte->id);
    }

    /** @test */
    public function factory_cree_transactions()
    {
        $transaction = Transaction::factory()->create();
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);
    }
}
