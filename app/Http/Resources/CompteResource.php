<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CompteResource",
 *     title="Compte Resource",
 *     description="Représentation d'un compte bancaire",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="numeroCompte", type="string", example="C00001234"),
 *     @OA\Property(property="titulaire", type="string", example="Amadou Diallo"),
 *     @OA\Property(property="type", type="string", enum={"cheque", "epargne", "courant"}, example="epargne"),
 *     @OA\Property(property="solde", type="number", format="float", example=1500.50),
 *     @OA\Property(property="devise", type="string", example="FCFA"),
 *     @OA\Property(property="dateCreation", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
 *     @OA\Property(property="statut", type="string", enum={"actif", "inactif", "suspendu", "bloque", "ferme"}, example="actif"),
 *     @OA\Property(
 *         property="metadata",
 *         type="object",
 *         @OA\Property(property="derniereModification", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
 *         @OA\Property(property="version", type="integer", example=1)
 *     )
 * )
 */
class CompteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numeroCompte' => $this->numero_compte,
            'titulaire' => optional($this->client)->titulaire ?? null,
            'type' => $this->type_compte,
            'solde' => (float) $this->solde_reel,
            'devise' => $this->devise,
            'dateCreation' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'statut' => $this->statut,
            'dateDebutBlocage' => $this->date_debut_blocage ? $this->date_debut_blocage->toIso8601String() : null,
            'dateFinBlocage' => $this->date_fin_blocage ? $this->date_fin_blocage->toIso8601String() : null,
            'motifBlocage' => $this->motif_blocage,
            'metadata' => [
                'derniereModification' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
                'version' => 1,
            ],
        ];
    }
}
