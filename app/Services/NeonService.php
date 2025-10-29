<?php

namespace App\Services;

use App\Models\Compte;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NeonService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.neon.base_url');
        $this->apiKey = config('services.neon.api_key');
    }

    /**
     * Récupérer un compte archivé depuis Neon
     */
    public function getArchivedAccount(string $compteId): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/api/v1/archived-accounts/{$compteId}");

            if ($response->successful()) {
                return $response->json()['data'];
            }

            Log::warning('Échec de récupération du compte archivé depuis Neon', [
                'compte_id' => $compteId,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du compte archivé', [
                'compte_id' => $compteId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Archiver un compte dans Neon
     */
    public function archiveAccount(Compte $compte): bool
    {
        try {
            $data = [
                'id' => $compte->id,
                'numero_compte' => $compte->numero_compte,
                'client' => $compte->client->toArray(),
                'type_compte' => $compte->type_compte,
                'solde' => $compte->solde_reel,
                'devise' => $compte->devise,
                'date_creation' => $compte->created_at,
                'date_archivage' => now(),
                'motif_archivage' => 'Archivage automatique après blocage prolongé',
                'date_debut_blocage' => $compte->date_debut_blocage,
                'date_fin_blocage' => $compte->date_fin_blocage,
                'motif_blocage' => $compte->motif_blocage,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/api/v1/archived-accounts", $data);

            if ($response->successful()) {
                Log::info('Compte archivé dans Neon avec succès', [
                    'compte_id' => $compte->id,
                    'numero_compte' => $compte->numero_compte,
                ]);

                return true;
            }

            Log::error('Échec de l\'archivage du compte dans Neon', [
                'compte_id' => $compte->id,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'archivage du compte dans Neon', [
                'compte_id' => $compte->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Vérifier si Neon est disponible
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Service Neon indisponible', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
