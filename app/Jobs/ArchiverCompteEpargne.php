<?php

namespace App\Jobs;

use App\Models\Compte;
use App\Services\NeonService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ArchiverCompteEpargne implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $neonService = app(NeonService::class);

        // Archiver les comptes épargne bloqués dont la date de début de blocage est échue (plus de 30 jours)
        $comptesAArchiver = Compte::where('type_compte', 'epargne')
            ->where('statut', 'bloque')
            ->whereNotNull('date_debut_blocage')
            ->where('date_debut_blocage', '<=', now()->subDays(30))
            ->get();

        foreach ($comptesAArchiver as $compte) {
            // Archiver dans Neon d'abord
            if ($neonService->isAvailable() && $neonService->archiveAccount($compte)) {
                // Soft delete pour archiver localement
                $compte->delete();

                Log::info('Compte épargne archivé automatiquement', [
                    'compte_id' => $compte->id,
                    'numero_compte' => $compte->numero_compte,
                    'date_debut_blocage' => $compte->date_debut_blocage,
                    'archived_in_neon' => true,
                ]);
            } else {
                Log::warning('Échec de l\'archivage du compte épargne dans Neon', [
                    'compte_id' => $compte->id,
                    'numero_compte' => $compte->numero_compte,
                ]);
            }
        }
    }
}
