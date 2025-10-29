<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BloquerCompteEpargne implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Bloquer les comptes épargne actifs dont la date de début de blocage est échue
        $comptesABloquer = Compte::where('type_compte', 'epargne')
            ->where('statut', 'actif')
            ->whereNotNull('date_debut_blocage')
            ->where('date_debut_blocage', '<=', now())
            ->whereNull('date_fin_blocage')
            ->get();

        foreach ($comptesABloquer as $compte) {
            $compte->update([
                'statut' => 'bloque',
                'date_fin_blocage' => now()->addDays(30), // Blocage de 30 jours par défaut
            ]);

            Log::info('Compte épargne bloqué automatiquement', [
                'compte_id' => $compte->id,
                'numero_compte' => $compte->numero_compte,
                'date_debut' => $compte->date_debut_blocage,
                'date_fin' => $compte->date_fin_blocage,
            ]);
        }
    }
}
