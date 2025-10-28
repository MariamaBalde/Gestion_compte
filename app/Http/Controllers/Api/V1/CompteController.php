<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompteIndexRequest;
use App\Http\Requests\StoreCompteRequest;
use Illuminate\Http\Request;
use App\Http\Resources\CompteResource;
use App\Models\Compte;
use App\Models\Client;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Services\CompteService;
use App\Exceptions\CompteNotFoundException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * @OA\Info(
 *     title="API RESTful Comptes Bancaires",
 *     description="Documentation complète de l'API RESTful pour la gestion des comptes bancaires",
 *     version="1.0.0",
 *     @OA\Contact(
 *         email="contact@banque.com"
 *     )
 * )
 * @OA\Server(
 *     url="https://gestion-compte-1izl.onrender.com",
 *     description="Serveur de production"
 * )
 *     description="Serveur de développement"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter your Bearer token in the format: Bearer {token}"
 * )
 *
 * @OA\Tag(
 *     name="Comptes",
 *     description="Gestion des comptes bancaires"
 * )
 */
class CompteController extends Controller
{
    use ApiResponse;

    protected CompteService $compteService;

    public function __construct(CompteService $compteService)
    {
        $this->compteService = $compteService;
    }

    /**
     * @OA\Get(
     *     path="/api/mariama/v1/comptes",
     *     summary="Lister tous les comptes",
     *     description="Récupère la liste des comptes actifs avec possibilité de filtrage et pagination",
     *     operationId="getComptes",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=10)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type de compte",
     *         required=false,
     *         @OA\Schema(type="string", enum={"epargne","cheque","courant"})
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut",
     *         required=false,
     *         @OA\Schema(type="string", enum={"actif","inactif","suspendu","bloque","ferme"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par titulaire, numéro de compte ou téléphone",
     *         required=false,
     *         @OA\Schema(type="string", maxLength=255)
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Champ de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"dateCreation","solde","titulaire"}, default="dateCreation")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc","desc"}, default="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Comptes récupérés avec succès"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CompteResource")),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
     *             @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non autorisé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Paramètres de requête invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Paramètres invalides"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function index(CompteIndexRequest $request)
    {
        $comptes = $this->compteService->getActiveComptes($request);
        $meta = $this->compteService->buildPaginationMeta($comptes);
        $links = $this->compteService->buildPaginationLinks($comptes, $request, $request->url());

        return $this->paginatedResponse(
            CompteResource::collection($comptes),
            $meta,
            $links,
            'Comptes récupérés avec succès'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/mariama/v1/comptes/archived",
     *     summary="Lister les comptes archivés",
     *     description="Récupère la liste des comptes épargne archivés avec possibilité de filtrage et pagination",
     *     operationId="getArchivedComptes",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=10)
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut",
     *         required=false,
     *         @OA\Schema(type="string", enum={"actif","inactif","suspendu","bloque","ferme"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par titulaire, numéro de compte ou téléphone",
     *         required=false,
     *         @OA\Schema(type="string", maxLength=255)
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Champ de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"dateCreation","solde","titulaire"}, default="dateCreation")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc","desc"}, default="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes archivés récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Comptes archivés récupérés avec succès"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CompteResource")),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
     *             @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non autorisé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Paramètres de requête invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Paramètres invalides"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function archived(CompteIndexRequest $request)
    {
        $comptes = $this->compteService->getArchivedComptes($request);
        $meta = $this->compteService->buildPaginationMeta($comptes);
        $links = $this->compteService->buildPaginationLinks($comptes, $request, $request->url());

        return $this->paginatedResponse(
            CompteResource::collection($comptes),
            $meta,
            $links,
            'Comptes archivés récupérés avec succès'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/mariama/v1/comptes/{compte}",
     *     summary="Récupérer un compte spécifique",
     *     description="Récupère les détails d'un compte spécifique par son ID",
     *     operationId="getCompte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="compte",
     *         in="path",
     *         description="ID du compte",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du compte récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte récupéré avec succès"),
     *             @OA\Property(property="data", ref="#/components/schemas/CompteResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non autorisé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
     *         )
     *     )
     * )
     */
    public function show(Compte $compte)
    {
        return $this->successResponse(
            new CompteResource($compte),
            'Compte récupéré avec succès'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/mariama/v1/comptes",
     *     summary="Créer un nouveau compte",
     *     description="Crée un nouveau compte bancaire avec les informations du client",
     *     operationId="createCompte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreCompteRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès"),
     *             @OA\Property(property="data", ref="#/components/schemas/CompteResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non autorisé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données de requête invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Données invalides"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(StoreCompteRequest $request)
    {
        try {
            $validated = $request->validated();

            // Créer le client
            $client = Client::create($validated['client']);

            // Créer le compte
            $compte = Compte::create([
                'client_id' => $client->id,
                'numero_compte' => $validated['numero_compte'] ?? null,
                'type_compte' => $validated['type_compte'],
                'solde' => 0,
                'devise' => $validated['devise'],
                'statut' => $validated['statut'],
            ]);

            return $this->successResponse(
                new CompteResource($compte->load('client')),
                'Compte créé avec succès',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la création du compte', 500);
        }
    }
}
