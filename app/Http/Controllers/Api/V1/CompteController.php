<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\CompteResource;
use App\Traits\ApiResponse;




class CompteController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Compte::with('client');

        // Filtres
        if ($request->has('type')) {
            $query->where('type_compte', $request->type);
        }
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('numero_compte', 'like', '%' . $request->search . '%')
                  ->orWhereHas('client', function ($clientQuery) use ($request) {
                      $clientQuery->where('titulaire', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Tri
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);

        // Pagination
        $perPage = min($request->get('limit', 10), 100);
        $comptes = $query->paginate($perPage);

        return $this->successResponse([
            'data' => CompteResource::collection($comptes),
            'pagination' => [
                'currentPage' => $comptes->currentPage(),
                'totalPages' => $comptes->lastPage(),
                'totalItems' => $comptes->total(),
                'itemsPerPage' => $comptes->perPage(),
                'hasNext' => $comptes->hasMorePages(),
                'hasPrevious' => $comptes->currentPage() > 1,
            ],
            'links' => [
                'self' => $comptes->url($comptes->currentPage()),
                'next' => $comptes->nextPageUrl(),
                'first' => $comptes->url(1),
                'last' => $comptes->url($comptes->lastPage()),
            ],
        ]);
    }
}
