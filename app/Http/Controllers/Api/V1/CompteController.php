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


class CompteController extends Controller
{
    use ApiResponse;

    protected CompteService $compteService;

    public function __construct(CompteService $compteService)
    {
        $this->compteService = $compteService;
    }

    /**
     * Lister tous les comptes (Admin) ou comptes du client (Client)
     *
     * @OA\Get(
     *     path="/api/v1/comptes",
     *     summary="Lister tous les comptes actifs",
     *     description="Permet à l'admin de récupérer tous les comptes ou au client de récupérer ses propres comptes. Liste uniquement les comptes non archivés de type cheque, épargne ou courant.",
     *     operationId="getComptes",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page (défaut: 1)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page (défaut: 10, max: 100)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=10)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type de compte",
     *         required=false,
     *         @OA\Schema(type="string", enum={"cheque", "epargne", "courant"})
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut du compte",
     *         required=false,
     *         @OA\Schema(type="string", enum={"actif", "inactif", "suspendu", "bloque", "ferme"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par titulaire, numéro de compte ou téléphone",
     *         required=false,
     *         @OA\Schema(type="string", maxLength=255)
     *     ),
     *     @OA\Parameter(
     *         name="telephone",
     *         in="query",
     *         description="Filtrer par téléphone du client",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Champ de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"dateCreation", "solde", "titulaire"}, default="dateCreation")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CompteResource")),
     *             @OA\Property(property="pagination", ref="#/components/schemas/PaginationMeta"),
     *             @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function index(CompteIndexRequest $request)
    {
        $paginator = $this->compteService->getActiveComptes($request);

        // transformer la data
        $data = CompteResource::collection($paginator->items());

        // build pagination meta & links
        $pagination = $this->compteService->buildPaginationMeta($paginator);
        $links = $this->compteService->buildPaginationLinks($paginator, $request, url('/api/v1/comptes'));

        return $this->paginatedResponse($data, $pagination, $links);
    }

    /**
     * Lister les comptes archivés (épargne uniquement depuis le cloud)
     *
     * @OA\Get(
     *     path="/api/v1/comptes/archived",
     *     summary="Lister les comptes épargne archivés",
     *     description="Permet de récupérer la liste des comptes épargne archivés. Ces données sont consultées depuis le cloud.",
     *     operationId="getArchivedComptes",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page (défaut: 1)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page (défaut: 10, max: 100)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=10)
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut du compte",
     *         required=false,
     *         @OA\Schema(type="string", enum={"actif", "inactif", "suspendu", "bloque", "ferme"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par titulaire, numéro de compte ou téléphone",
     *         required=false,
     *         @OA\Schema(type="string", maxLength=255)
     *     ),
     *     @OA\Parameter(
     *         name="telephone",
     *         in="query",
     *         description="Filtrer par téléphone du client",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Champ de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"dateCreation", "solde", "titulaire"}, default="dateCreation")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes archivés récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CompteResource")),
     *             @OA\Property(property="pagination", ref="#/components/schemas/PaginationMeta"),
     *             @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function archived(CompteIndexRequest $request)
    {
        $paginator = $this->compteService->getArchivedComptes($request);

        // transformer la data
        $data = CompteResource::collection($paginator->items());

        // build pagination meta & links
        $pagination = $this->compteService->buildPaginationMeta($paginator);
        $links = $this->compteService->buildPaginationLinks($paginator, $request, url('/api/v1/comptes/archived'));

        return $this->paginatedResponse($data, $pagination, $links);
    }

    /**
     * Récupérer un compte spécifique
     *
     * @OA\Get(
     *     path="/api/v1/comptes/{compteId}",
     *     summary="Récupérer un compte spécifique",
     *     description="Permet à l'admin de récupérer n'importe quel compte ou au client de récupérer un de ses comptes par ID. Recherche d'abord en local (comptes cheque/epargne actifs), puis en serverless si non trouvé.",
     *     operationId="getCompte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         required=true,
     *         description="ID du compte à récupérer",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", ref="#/components/schemas/CompteResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas"),
     *                 @OA\Property(property="details", type="object",
     *                     @OA\Property(property="compteId", type="string", example="550e8400-e29b-41d4-a716-446655440000")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function show(Compte $compte)
    {
        // Vérifier que le compte n'est pas archivé (soft deleted)
        if ($compte->trashed()) {
            throw new CompteNotFoundException();
        }

        return $this->successResponse(
            new CompteResource($compte->load('client')),
            'Success'
        );
    }
    /**
     * Créer un nouveau compte bancaire
     *
     * @OA\Post(
     *     path="/api/v1/comptes",
     *     summary="Créer un nouveau compte bancaire",
     *     description="Permet de créer un nouveau compte bancaire. Si le client n'existe pas, il sera créé automatiquement avec un utilisateur pour l'authentification.",
     *     operationId="createCompte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données du compte à créer",
     *         @OA\JsonContent(
     *             required={"client","type_compte","devise","statut"},
     *             @OA\Property(
     *                 property="client",
     *                 type="object",
     *                 description="Informations du client",
     *                 required={"titulaire","nci","email","telephone","adresse"},
     *                 @OA\Property(property="titulaire", type="string", maxLength=255, example="Amadou Diallo"),
     *                 @OA\Property(property="nci", type="string", maxLength=20, example="1234567890123456"),
     *                 @OA\Property(property="email", type="string", format="email", example="amadou.diallo@email.com"),
     *                 @OA\Property(property="telephone", type="string", example="+221771234567"),
     *                 @OA\Property(property="adresse", type="string", example="Dakar, Plateau")
     *             ),
     *             @OA\Property(
     *                 property="type_compte",
     *                 type="string",
     *                 enum={"cheque","epargne","courant"},
     *                 example="epargne",
     *                 description="Type de compte bancaire"
     *             ),
     *             @OA\Property(
     *                 property="devise",
     *                 type="string",
     *                 example="FCFA",
     *                 description="Devise du compte"
     *             ),
     *             @OA\Property(
     *                 property="statut",
     *                 type="string",
     *                 enum={"actif","inactif","suspendu","bloque","ferme"},
     *                 example="actif",
     *                 description="Statut initial du compte"
     *             )
     *         )
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
     *         response=400,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur de validation"),
     *             @OA\Property(property="errors", type="object", description="Détails des erreurs de validation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation des données",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(StoreCompteRequest $request)
    {
        // Vérifier si le client existe déjà par email ou téléphone
        $clientData = $request->input('client');
        $client = Client::where('email', $clientData['email'])
                       ->orWhere('telephone', $clientData['telephone'])
                       ->first();

        if (!$client) {
            // Générer un mot de passe et un code
            $generatedPassword = Str::random(8);
            $generatedCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

            // Créer le client avec les données fournies
            $client = Client::create([
                'titulaire' => $clientData['titulaire'],
                'nci' => $clientData['nci'],
                'email' => $clientData['email'],
                'telephone' => $clientData['telephone'],
                'adresse' => $clientData['adresse'],
            ]);

            // Créer un utilisateur pour l'authentification
            $user = User::create([
                'name' => $clientData['titulaire'],
                'email' => $clientData['email'],
                'password' => Hash::make($generatedPassword),
                'role' => 'client',
            ]);

            // Stocker le code temporairement (dans une table dédiée ou cache)
            // Pour cet exemple, on l'ajoute comme attribut temporaire
            $client->temp_code = $generatedCode;
            $client->temp_password = $generatedPassword;
        }

        // Créer le compte
        $compte = Compte::create([
            'client_id' => $client->id,
            'numero_compte' => null, // Sera généré automatiquement dans le boot
            'type_compte' => $request->type_compte,
            'solde' => 0, // Solde initial à 0
            'devise' => $request->devise,
            'statut' => $request->statut,
        ]);

        // Si c'est un nouveau client, envoyer email et SMS
        if (isset($client->temp_password)) {
            // Envoyer email avec le mot de passe
            try {
                Mail::raw("Votre compte a été créé avec succès. Votre mot de passe temporaire est : {$client->temp_password}", function ($message) use ($client) {
                    $message->to($client->email)
                            ->subject('Création de votre compte bancaire');
                });
            } catch (\Exception $e) {
                // Log l'erreur mais ne pas interrompre la création du compte
                Log::error('Erreur envoi email: ' . $e->getMessage());
            }

            // TODO: Envoyer SMS avec le code
            // Pour l'instant, on simule l'envoi
            // Vous pouvez intégrer un service SMS comme Twilio, Africa's Talking, etc.
        }

        return $this->successResponse(
            new CompteResource($compte->load('client')),
            'Compte créé avec succès',
            201
        );
    }
}




