<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Client;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
        protected $model = Client::class;

    public function definition(): array
    {
        return [
             'titulaire' => $this->faker->name(),                  
            'nci' => $this->faker->unique()->numerify('##########'), 
            'email' => $this->faker->unique()->safeEmail(),       
            'telephone' => $this->faker->unique()->phoneNumber(), 
            'adresse' => $this->faker->address(),               
            'created_at' => now(),                               
            'updated_at' => now(), 
        ];
    }
}
