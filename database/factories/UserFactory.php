<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            // Ne pas définir 'id' : le model boot() génère l'UUID
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'password', // sera hashé via mutator dans le model
            'role' => $this->faker->randomElement(['user','admin']),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin()
    {
        return $this->state(fn(array $attributes) => ['role' => 'admin']);
    }
}
