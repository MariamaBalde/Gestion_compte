<?php

namespace App\Events;

use App\Models\Client;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientCreated
{
    use Dispatchable, SerializesModels;

    public Client $client;
    public string $generatedPassword;
    public string $generatedCode;

    /**
     * Create a new event instance.
     */
    public function __construct(Client $client, string $generatedPassword, string $generatedCode)
    {
        $this->client = $client;
        $this->generatedPassword = $generatedPassword;
        $this->generatedCode = $generatedCode;
    }
}
