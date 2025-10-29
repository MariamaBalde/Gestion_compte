<?php

namespace App\Listeners;

use App\Events\ClientCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendClientNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ClientCreated $event): void
    {
        try {
            // Envoyer l'email avec le mot de passe
            Mail::raw(
                "Bienvenue {$event->client->titulaire}!\n\n" .
                "Votre compte a été créé avec succès.\n" .
                "Mot de passe temporaire: {$event->generatedPassword}\n" .
                "Code de vérification: {$event->generatedCode}\n\n" .
                "Veuillez changer votre mot de passe lors de votre première connexion.",
                function ($message) use ($event) {
                    $message->to($event->client->email)
                            ->subject('Création de votre compte bancaire');
                }
            );

            // TODO: Implémenter l'envoi de SMS avec le code
            // $this->sendSms($event->client->telephone, "Votre code de vérification: {$event->generatedCode}");

            Log::info('Notifications envoyées pour le client', [
                'client_id' => $event->client->id,
                'email' => $event->client->email,
                'telephone' => $event->client->telephone,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des notifications client', [
                'client_id' => $event->client->id,
                'error' => $e->getMessage(),
            ]);

            // Re-throw pour que le job soit marqué comme échoué
            throw $e;
        }
    }

    /**
     * Méthode pour envoyer un SMS (à implémenter selon le service SMS utilisé)
     */
    private function sendSms(string $telephone, string $message): void
    {
        // TODO: Intégrer un service SMS comme Twilio, Africa's Talking, etc.
        Log::info('SMS à envoyer', [
            'telephone' => $telephone,
            'message' => $message,
        ]);
    }
}
