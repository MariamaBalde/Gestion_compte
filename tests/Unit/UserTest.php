<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function un_user_peut_etre_cree_avec_uuid_et_password_hash()
    {
        $user = User::factory()->create([
            'password' => 'plainpassword123',
        ]);

        $this->assertNotNull($user->id, 'UUID généré');
        $this->assertIsString($user->id);
        $this->assertDatabaseHas('users', ['email' => $user->email]);

        // Le mot de passe stocké ne doit pas être le plain
        $this->assertNotEquals('plainpassword123', $user->getAuthPassword());
        $this->assertTrue(password_verify('plainpassword123', $user->getAuthPassword()));
    }

    /** @test */
    public function les_emails_sont_uniques()
    {
        $user = User::factory()->create();
        $this->expectException(\Illuminate\Database\QueryException::class);
        // tenter de créer un autre user avec le même email provoquera une exception (unique)
        User::factory()->create(['email' => $user->email]);
    }
}
