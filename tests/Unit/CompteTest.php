<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Compte;
use App\Models\Client;

class CompteTest extends TestCase
{
    use RefreshDatabase;

    public function test_lister_comptes()
    {
        $client = Client::factory()->create(['telephone' => '770000000']);
        Compte::factory()->count(15)->create(['client_id' => $client->id, 'type_compte' => 'epargne', 'statut' => 'actif']);

        $response = $this->getJson('/api/v1/comptes?limit=5');

        $response->assertStatus(200)
                 ->assertJsonPath('pagination.itemsPerPage', 5)
                 ->assertJsonPath('pagination.totalItems', 15)
                 ->assertJson('success', true);
    }
}
