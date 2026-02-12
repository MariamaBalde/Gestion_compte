<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Compte;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

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

    public function test_creer_compte_nouveau_client()
    {
        Mail::fake();

        $clientData = [
            'titulaire' => 'John Doe',
            'nci' => '123456789',
            'email' => 'john.doe@example.com',
            'telephone' => '+221771234567',
            'adresse' => 'Dakar, Sénégal'
        ];

        $compteData = [
            'client' => $clientData,
            'type_compte' => 'cheque',
            'devise' => 'XOF',
            'statut' => 'actif'
        ];

        $response = $this->postJson('/api/v1/comptes', $compteData);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Compte créé avec succès'
                 ]);

        // Vérifier que le client a été créé
        $this->assertDatabaseHas('client', [
            'email' => 'john.doe@example.com',
            'telephone' => '+221771234567'
        ]);

        // Vérifier que l'utilisateur a été créé
        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com'
        ]);

        // Vérifier que le compte a été créé
        $this->assertDatabaseHas('compte', [
            'type_compte' => 'cheque',
            'devise' => 'XOF',
            'statut' => 'actif',
            'solde' => 0
        ]);

        // Vérifier que l'email a été envoyé
        Mail::assertSent(function ($mail) use ($clientData) {
            return $mail->hasTo($clientData['email']) &&
                   $mail->subject('Création de votre compte bancaire');
        });
    }

    public function test_creer_compte_client_existant()
    {
        Mail::fake();

        // Créer un client existant
        $existingClient = Client::factory()->create([
            'email' => 'existing@example.com',
            'telephone' => '+221778765432'
        ]);

        $clientData = [
            'titulaire' => 'Existing Client',
            'nci' => '987654321',
            'email' => 'existing@example.com', // Même email
            'telephone' => '+221778765432', // Même téléphone
            'adresse' => 'Dakar, Sénégal'
        ];

        $compteData = [
            'client' => $clientData,
            'type_compte' => 'epargne',
            'devise' => 'XOF',
            'statut' => 'actif'
        ];

        $response = $this->postJson('/api/v1/comptes', $compteData);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Compte créé avec succès'
                 ]);

        // Vérifier qu'aucun nouveau client n'a été créé
        $this->assertDatabaseCount('client', 1);

        // Vérifier que le compte a été créé pour le client existant
        $this->assertDatabaseHas('compte', [
            'client_id' => $existingClient->id,
            'type_compte' => 'epargne'
        ]);

        // Vérifier qu'aucun email n'a été envoyé (car client existant)
        Mail::assertNotSent(function ($mail) {
            return $mail->subject('Création de votre compte bancaire');
        });
    }

    public function test_validation_compte_creation()
    {
        $invalidData = [
            'client' => [
                'titulaire' => '', // Vide
                'email' => 'invalid-email', // Email invalide
            ],
            'type_compte' => 'invalid_type', // Type invalide
        ];

        $response = $this->postJson('/api/v1/comptes', $invalidData);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Erreur de validation'
                 ]);
    }
}
