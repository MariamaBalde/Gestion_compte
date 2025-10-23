<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompteIndexRequest;
use Illuminate\Http\Request;
use App\Http\Resources\CompteResource;
use App\Models\Compte;
use App\Traits\ApiResponse;


class CompteController extends Controller
{

    /**
 * @OA\Get(
 *     path="/comptes",
 *     summary="Lister les comptes",
 *     tags={"Comptes"},
 *     @OA\Parameter(name="page", in="query", description="Numéro de page", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="limit", in="query", description="Nombre d’éléments", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="type", in="query", description="Filtrer par type", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="statut", in="query", description="Filtrer par statut", required=false, @OA\Schema(type="string")),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des comptes récupérée avec succès"
 *     )
 * )
 */
    use ApiResponse;

    public function index(CompteIndexRequest $request)
    {
        $query = Compte::query();

        // appliquer scope non-archivé
        $query->nonArchive();

        // Filters
        if ($request->filled('type')) {
            $query->where('type_compte', $request->type);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('numero')) {
            $query->numero($request->numero);
        }

        // recherche simple : titulaire (via relation) ou numero
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_compte', 'ilike', "%{$search}%")
                  ->orWhereHas('client', function ($qc) use ($search) {
                      $qc->where('nom', 'ilike', "%{$search}%")
                         ->orWhere('prenom', 'ilike', "%{$search}%")
                         ->orWhere('telephone', 'ilike', "%{$search}%");
                  });
            });
        }

        // filter by client telephone (scopeClientByPhone)
        if ($request->filled('telephone')) {
            $query->clientByPhone($request->telephone);
        }

        // Sorting
        $sort = $request->get('sort', 'dateCreation');
        $order = $request->get('order', 'desc');

        // mapping sort keys to columns
        $sortMap = [
            'dateCreation' => 'created_at',
            'solde' => 'solde',
            'titulaire' => 'numero_compte', // ou client.nom si tu veux trier par nom
        ];

        $orderBy = $sortMap[$sort] ?? 'created_at';
        $query->orderBy($orderBy, $order);

        // Pagination
        $limit = (int) $request->get('limit', 10);
        $page = (int) $request->get('page', 1);

        $paginator = $query->with('client')->paginate($limit, ['*'], 'page', $page);

        // transformer la data
        $data = CompteResource::collection($paginator->items());

        // build pagination meta & links
        $pagination = [
            'currentPage' => $paginator->currentPage(),
            'totalPages' => $paginator->lastPage(),
            'totalItems' => $paginator->total(),
            'itemsPerPage' => $paginator->perPage(),
            'hasNext' => $paginator->hasMorePages(),
            'hasPrevious' => $paginator->currentPage() > 1,
        ];

        $basePath = url('/api/v1/comptes');

        $links = [
            'self' => $basePath . '?' . http_build_query(array_merge($request->except('page'), ['page' => $paginator->currentPage()])),
            'next' => $paginator->currentPage() < $paginator->lastPage() ? $basePath . '?' . http_build_query(array_merge($request->except('page'), ['page' => $paginator->currentPage() + 1])) : null,
            'first' => $basePath . '?' . http_build_query(array_merge($request->except('page'), ['page' => 1])),
            'last' => $basePath . '?' . http_build_query(array_merge($request->except('page'), ['page' => $paginator->lastPage()])),
        ];

        return $this->paginatedResponse($data, $pagination, $links);
    }
}




