<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authentification gérée par middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Pas de règles supplémentaires, la logique métier est dans le contrôleur
        ];
    }

    /**
     * Valider que le compte peut être supprimé
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $compte = $this->route('compte');

            // Vérifier que le compte est actif
            if ($compte->statut !== 'actif') {
                $validator->errors()->add('compte', 'Seul un compte actif peut être supprimé.');
            }

            // Vérifier que le solde est nul
            if ($compte->solde_reel > 0) {
                $validator->errors()->add('compte', 'Le compte doit avoir un solde nul pour être supprimé.');
            }

            // Vérifier qu'il n'y a pas de transactions récentes (dernière semaine)
            $recentTransactions = $compte->transactions()
                ->where('created_at', '>=', now()->subWeek())
                ->exists();

            if ($recentTransactions) {
                $validator->errors()->add('compte', 'Le compte a eu des transactions récentes et ne peut pas être supprimé.');
            }
        });
    }
}
