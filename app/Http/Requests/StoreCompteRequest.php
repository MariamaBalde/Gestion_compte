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
            'client_id' => 'required|exists:client,id',
            'type_compte' => 'required|in:cheque,epargne,courant',
            'solde' => 'required|numeric|min:0',
            'devise' => 'required|string|max:5',
            'statut' => 'required|in:actif,inactif,suspendu',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Le client est obligatoire.',
            'client_id.exists' => 'Le client sélectionné est invalide.',
            'type_compte.required' => 'Le type de compte est obligatoire.',
            'type_compte.in' => 'Le type de compte doit être cheque, epargne ou courant.',
            'solde.required' => 'Le solde est obligatoire.',
            'solde.numeric' => 'Le solde doit être un nombre.',
            'solde.min' => 'Le solde ne peut pas être négatif.',
            'devise.required' => 'La devise est obligatoire.',
            'devise.max' => 'La devise ne peut pas dépasser 5 caractères.',
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in' => 'Le statut doit être actif, inactif ou suspendu.',
        ];
    }
}
