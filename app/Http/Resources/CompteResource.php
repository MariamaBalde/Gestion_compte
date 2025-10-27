<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class CompteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numeroCompte' => $this->numero_compte,
            'titulaire' => optional($this->client)->titulaire ?? null,
            'type' => $this->type_compte,
            'solde' => (float) $this->solde,
            'devise' => $this->devise,
            'dateCreation' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'statut' => $this->statut,
            'metadata' => [
                'derniereModification' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
                'version' => 1,
            ],
        ];
    }
}
