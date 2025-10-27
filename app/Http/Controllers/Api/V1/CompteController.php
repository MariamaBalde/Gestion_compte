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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class CompteController extends Controller
{

    /**
  * @OA\Get(
  *     path="/comptes",
  *     summary="Lister les comptes",
  *     tags={"Comptes"},
  *     @OA\Parameter(name="page", in="query", description="Numéro de page", required=false, @OA\Schema(type="integer")),
  *     @OA\Parameter(name="limit", in="query", description="Nombre d'éléments", required=false, @OA\Schema(type="integer")),
  *     @OA\Parameter(name="type", in="query", description="Filtrer par type", required=false, @OA\Schema(type="string")),
  *     @OA\Parameter(name="statut", in="query", description="Filtrer par statut", required=false, @OA\Schema(type="string")),
  *     @OA\Parameter(name="search", in="query", description="Recherche par titulaire ou numéro", required=false, @OA\Schema(type="string")),
  *     @OA\Parameter(name="sort", in="query", description="Tri (dateCreation, solde, titulaire)", required=false, @OA\Schema(type="string")),
  *     @OA\Parameter(name="order", in="query", description="Ordre (asc, desc)", required=false, @OA\Schema(type="string")),
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

    /**
     * @OA\Post(
     *     path="/comptes",
     *     summary="Créer un nouveau compte",
     *     tags={"Comptes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"client","type_compte","devise","statut"},
     *             @OA\Property(property="client", type="object",
     *                 @OA\Property(property="titulaire", type="string", example="John Doe"),
     *                 @OA\Property(property="nci", type="string", example="123456789"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *                 @OA\Property(property="telephone", type="string", example="+221771234567"),
     *                 @OA\Property(property="adresse", type="string", example="Dakar, Sénégal")
     *             ),
     *             @OA\Property(property="type_compte", type="string", enum={"cheque","epargne","courant"}, example="cheque"),
     *             @OA\Property(property="devise", type="string", example="XOF"),
     *             @OA\Property(property="statut", type="string", enum={"actif","inactif","suspendu"}, example="actif")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès"),
     *             @OA\Property(property="data", type="object", description="Données du compte créé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur de validation"),
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




