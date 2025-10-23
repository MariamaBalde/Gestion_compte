<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Client;



class ClientFactory extends Factory
{
    
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
