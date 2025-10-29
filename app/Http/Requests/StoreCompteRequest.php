<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreCompteRequest",
 *     title="Store Compte Request",
 *     description="Requête de création d'un compte bancaire",
 *     required={"client","type_compte","devise","statut"},
 *     @OA\Property(
 *         property="client",
 *         type="object",
 *         description="Informations du client",
 *         required={"titulaire","nci","email","telephone","adresse"},
 *         @OA\Property(property="titulaire", type="string", maxLength=255, example="Amadou Diallo"),
 *         @OA\Property(property="nci", type="string", maxLength=20, example="1234567890123456"),
 *         @OA\Property(property="email", type="string", format="email", example="amadou.diallo@email.com"),
 *         @OA\Property(property="telephone", type="string", example="+221771234567"),
 *         @OA\Property(property="adresse", type="string", example="Dakar, Plateau")
 *     ),
 *     @OA\Property(
 *         property="type_compte",
 *         type="string",
 *         enum={"cheque","epargne","courant"},
 *         example="epargne",
 *         description="Type de compte bancaire"
 *     ),
 *     @OA\Property(
 *         property="devise",
 *         type="string",
 *         example="FCFA",
 *         description="Devise du compte"
 *     ),
 *     @OA\Property(
 *         property="statut",
 *         type="string",
 *         enum={"actif","inactif","suspendu","bloque","ferme"},
 *         example="actif",
 *         description="Statut initial du compte"
 *     )
 * )
 */
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
            'client.nci' => 'required|string',
            'client.email' => 'required|email|unique:client,email',
            'client.telephone' => 'required|string',
            'client.adresse' => 'required|string|max:500',
            'type_compte' => 'required|in:cheque,epargne,courant',
            'devise' => 'required|string|max:5',
            'statut' => 'required|in:actif,inactif,suspendu',
            'soldeInitial' => 'sometimes|numeric|min:10000',
        ];
    }

    public function messages(): array
    {
        return [
            'client.required' => 'Les informations du client sont obligatoires.',
            'client.titulaire.required' => 'Le titulaire est obligatoire.',
            'client.nci.required' => 'Le numéro de carte d\'identité est obligatoire.',
            'client.nci.regex' => 'Le numéro de carte d\'identité doit être au format sénégalais (13 chiffres + 2 lettres + 2 chiffres).',
            'client.nci.unique' => 'Ce numéro de carte d\'identité est déjà utilisé.',
            'client.email.required' => 'L\'email est obligatoire.',
            'client.email.email' => 'L\'email doit être valide.',
            'client.email.unique' => 'Cet email est déjà utilisé.',
            'client.telephone.required' => 'Le téléphone est obligatoire.',
            'client.telephone.regex' => 'Le numéro de téléphone doit être au format sénégalais (+221 ou 221 suivi de 7 chiffres commençant par 7, 6 ou 8).',
            'client.telephone.unique' => 'Ce téléphone est déjà utilisé.',
            'client.adresse.required' => 'L\'adresse est obligatoire.',
            'type_compte.required' => 'Le type de compte est obligatoire.',
            'type_compte.in' => 'Le type de compte doit être cheque, epargne ou courant.',
            'devise.required' => 'La devise est obligatoire.',
            'devise.max' => 'La devise ne peut pas dépasser 5 caractères.',
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in' => 'Le statut doit être actif, inactif ou suspendu.',
            'soldeInitial.min' => 'Le solde initial doit être d\'au moins 10 000 FCFA.',
        ];
    }
}
