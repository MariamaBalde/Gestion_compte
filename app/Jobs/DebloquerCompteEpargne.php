<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DebloquerCompteEpargne implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Débloquer les comptes épargne bloqués dont la date de fin de blocage est échue
        $comptesADebloquer = Compte::where('type_compte', 'epargne')
            ->where('statut', 'bloque')
            ->whereNotNull('date_fin_blocage')
            ->where('date_fin_blocage', '<=', now())
            ->get();

        foreach ($comptesADebloquer as $compte) {
            $compte->update([
                'statut' => 'actif',
                'date_debut_blocage' => null,
                'date_fin_blocage' => null,
                'motif_blocage' => null,
            ]);

            Log::info('Compte épargne débloqué automatiquement', [
                'compte_id' => $compte->id,
                'numero_compte' => $compte->numero_compte,
                'date_fin' => $compte->date_fin_blocage,
            ]);
        }
    }
}
