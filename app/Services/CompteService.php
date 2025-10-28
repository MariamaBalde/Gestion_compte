<?php

namespace App\Services;

use App\Models\Compte;
use App\Http\Requests\CompteIndexRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CompteService
{
    /**
     * Récupérer les comptes actifs avec filtres et pagination
     */
    public function getActiveComptes(CompteIndexRequest $request): LengthAwarePaginator
    {
        return $this->buildQuery($request, false)->paginate(
            (int) $request->get('limit', 10),
            ['*'],
            'page',
            (int) $request->get('page', 1)
        );
    }

    /**
     * Récupérer les comptes archivés (épargne uniquement) avec filtres et pagination
     */
    public function getArchivedComptes(CompteIndexRequest $request): LengthAwarePaginator
    {
        return $this->buildQuery($request, true)->paginate(
            (int) $request->get('limit', 10),
            ['*'],
            'page',
            (int) $request->get('page', 1)
        );
    }

    /**
     * Construire la requête avec tous les filtres
     */
    private function buildQuery(CompteIndexRequest $request, bool $archived = false): \Illuminate\Database\Eloquent\Builder
    {
        $query = Compte::query()->with('client');

        if ($archived) {
            // Comptes archivés (soft deleted) uniquement épargne
            $query->onlyTrashed()->where('type_compte', 'epargne');
        } else {
            // Comptes actifs (non archivés)
            $query->nonArchive();
        }

        // Appliquer les filtres
        $this->applyFilters($query, $request);

        // Appliquer le tri
        $this->applySorting($query, $request);

        return $query;
    }

    /**
     * Appliquer les filtres à la requête
     */
    private function applyFilters(\Illuminate\Database\Eloquent\Builder $query, CompteIndexRequest $request): void
    {
        if ($request->filled('type')) {
            $query->where('type_compte', $request->type);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('numero')) {
            $query->numero($request->numero);
        }

        // Recherche par titulaire, numéro ou téléphone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_compte', 'ilike', "%{$search}%")
                  ->orWhereHas('client', function ($qc) use ($search) {
                      $qc->where('titulaire', 'ilike', "%{$search}%")
                         ->orWhere('telephone', 'ilike', "%{$search}%");
                  });
            });
        }

        // Filtre par téléphone client
        if ($request->filled('telephone')) {
            $query->clientByPhone($request->telephone);
        }
    }

    /**
     * Appliquer le tri à la requête
     */
    private function applySorting(\Illuminate\Database\Eloquent\Builder $query, CompteIndexRequest $request): void
    {
        $sort = $request->get('sort', 'dateCreation');
        $order = $request->get('order', 'desc');

        $sortMap = [
            'dateCreation' => 'created_at',
            'solde' => 'solde',
            'titulaire' => 'numero_compte',
        ];

        $orderBy = $sortMap[$sort] ?? 'created_at';
        $query->orderBy($orderBy, $order);
    }

    /**
     * Construire les métadonnées de pagination
     */
    public function buildPaginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'currentPage' => $paginator->currentPage(),
            'totalPages' => $paginator->lastPage(),
            'totalItems' => $paginator->total(),
            'itemsPerPage' => $paginator->perPage(),
            'hasNext' => $paginator->hasMorePages(),
            'hasPrevious' => $paginator->currentPage() > 1,
        ];
    }

    /**
     * Construire les liens de pagination
     */
    public function buildPaginationLinks(LengthAwarePaginator $paginator, CompteIndexRequest $request, string $basePath): array
    {
        return [
            'self' => $basePath . '?' . http_build_query(array_merge($request->except('page'), ['page' => $paginator->currentPage()])),
            'next' => $paginator->currentPage() < $paginator->lastPage() ? $basePath . '?' . http_build_query(array_merge($request->except('page'), ['page' => $paginator->currentPage() + 1])) : null,
            'first' => $basePath . '?' . http_build_query(array_merge($request->except('page'), ['page' => 1])),
            'last' => $basePath . '?' . http_build_query(array_merge($request->except('page'), ['page' => $paginator->lastPage()])),
        ];
    }
}