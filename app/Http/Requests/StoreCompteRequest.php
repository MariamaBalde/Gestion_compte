<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Autorise la requête, sinon tu peux mettre une logique d’auth si nécessaire
    }

    public function rules(): array
    {
        return [
            'client' => 'required|array',
            'client.titulaire' => 'required|string|max:255',
            'client.nci' => 'required|string|unique:client,nci',
            'client.email' => 'required|email|unique:client,email',
            'client.telephone' => 'required|string|unique:client,telephone',
            'client.adresse' => 'required|string|max:500',
            'type_compte' => 'required|in:cheque,epargne,courant',
            'devise' => 'required|string|max:5',
            'statut' => 'required|in:actif,inactif,suspendu',
        ];
    }

    public function messages(): array
    {
        return [
            'client.required' => 'Les informations du client sont obligatoires.',
            'client.titulaire.required' => 'Le titulaire est obligatoire.',
            'client.nci.required' => 'Le numéro de carte d\'identité est obligatoire.',
            'client.nci.unique' => 'Ce numéro de carte d\'identité est déjà utilisé.',
            'client.email.required' => 'L\'email est obligatoire.',
            'client.email.email' => 'L\'email doit être valide.',
            'client.email.unique' => 'Cet email est déjà utilisé.',
            'client.telephone.required' => 'Le téléphone est obligatoire.',
            'client.telephone.unique' => 'Ce téléphone est déjà utilisé.',
            'client.adresse.required' => 'L\'adresse est obligatoire.',
            'type_compte.required' => 'Le type de compte est obligatoire.',
            'type_compte.in' => 'Le type de compte doit être cheque, epargne ou courant.',
            'devise.required' => 'La devise est obligatoire.',
            'devise.max' => 'La devise ne peut pas dépasser 5 caractères.',
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in' => 'Le statut doit être actif, inactif ou suspendu.',
        ];
    }
}
