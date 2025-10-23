<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Compte;
use App\Models\Client;

class CompteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function un_compte_peut_etre_cree_avec_uuid_et_numero_unique()
    {
        $client = Client::factory()->create();

        $compte = Compte::create([
            'client_id' => $client->id,
            'type_compte' => 'cheque',
            'solde' => 60000,
            'devise' => 'FCFA',
            'statut' => 'actif',
        ]);

        $this->assertNotNull($compte->id, 'UUID généré');
        $this->assertMatchesRegularExpression('/^C\d{8}$/', $compte->numero_compte, 'Numéro de compte généré correctement');
        $this->assertEquals($client->id, $compte->client_id, 'Relation client correcte');
    }

    /** @test */
    public function la_relation_client_fonctionne()
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create(['client_id' => $client->id]);

        $this->assertEquals($client->id, $compte->client->id);
    }
}
