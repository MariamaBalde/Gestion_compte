<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="CompteIndexRequest",
 *     title="Compte Index Request",
 *     description="Paramètres de requête pour la liste des comptes",
 *     @OA\Property(property="page", type="integer", minimum=1, default=1, description="Numéro de page"),
 *     @OA\Property(property="limit", type="integer", minimum=1, maximum=100, default=10, description="Nombre d'éléments par page"),
 *     @OA\Property(property="type", type="string", enum={"epargne","cheque","courant"}, description="Filtrer par type de compte"),
 *     @OA\Property(property="statut", type="string", enum={"actif","inactif","suspendu","bloque","ferme"}, description="Filtrer par statut"),
 *     @OA\Property(property="search", type="string", maxLength=255, description="Recherche par titulaire, numéro de compte ou téléphone"),
 *     @OA\Property(property="sort", type="string", enum={"dateCreation","solde","titulaire"}, default="dateCreation", description="Champ de tri"),
 *     @OA\Property(property="order", type="string", enum={"asc","desc"}, default="desc", description="Ordre de tri"),
 *     @OA\Property(property="numero", type="string", description="Filtrer par numéro de compte"),
 *     @OA\Property(property="telephone", type="string", description="Filtrer par téléphone du client")
 * )
 */
class CompteIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authentification à ajouter plus tard via passport
    }

    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer|min:1|max:100',
            'type' => ['sometimes', Rule::in(['epargne','cheque','courant'])],
            'statut' => ['sometimes', Rule::in(['actif','inactif','suspendu','bloque','ferme'])],
            'search' => 'sometimes|string|max:255',
            'sort' => 'sometimes|string|in:dateCreation,solde,titulaire',
            'order' => 'sometimes|string|in:asc,desc',
            'numero' => 'sometimes|string',
            'telephone' => 'sometimes|string',
        ];
    }
}
