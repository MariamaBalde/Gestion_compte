<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
