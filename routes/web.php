<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response()->json([
        'message' => 'API de gestion de comptes - Bienvenue',
        'version' => '1.0.0',
        'status' => 'operational',
        'documentation' => url('/api/docs'),
        'endpoints' => [
            'comptes' => url('/api/v1/comptes'),
        ]
    ]);
});

Route::get('/api/docs', function () {
    return view('swagger');
});

Route::get('/api-docs.json', function () {
    return response()->json([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'API de gestion de comptes',
            'version' => '1.0.0',
            'description' => 'Documentation de l\'API bancaire'
        ],
        'servers' => [
            [
                'url' => 'https://gestion-compte-1izl.onrender.com/api/v1',
                'description' => 'Serveur de production'
            ]
        ],
        'paths' => [
            '/comptes' => [
                'get' => [
                    'summary' => 'Lister les comptes',
                    'parameters' => [
                        [
                            'name' => 'page',
                            'in' => 'query',
                            'schema' => ['type' => 'integer'],
                            'description' => 'Numéro de page'
                        ],
                        [
                            'name' => 'limit',
                            'in' => 'query',
                            'schema' => ['type' => 'integer'],
                            'description' => 'Nombre d\'éléments par page'
                        ],
                        [
                            'name' => 'type',
                            'in' => 'query',
                            'schema' => ['type' => 'string'],
                            'description' => 'Filtrer par type de compte'
                        ],
                        [
                            'name' => 'statut',
                            'in' => 'query',
                            'schema' => ['type' => 'string'],
                            'description' => 'Filtrer par statut'
                        ],
                        [
                            'name' => 'search',
                            'in' => 'query',
                            'schema' => ['type' => 'string'],
                            'description' => 'Recherche par numéro ou titulaire'
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Liste des comptes récupérée avec succès'
                        ]
                    ]
                ]
            ]
        ]
    ]);
});

