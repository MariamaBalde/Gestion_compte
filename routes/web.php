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
        'documentation' => url('/api/documentation'),
        'endpoints' => [
            'comptes' => url('/api/v1/comptes'),
        ]
    ]);
});

Route::get('/api/documentation', function () {
    $documentation = 'default';
    $urlToDocs = route('l5-swagger.'.$documentation.'.docs');
    $configUrl = config('l5-swagger.defaults.additional_config_url');
    $validatorUrl = config('l5-swagger.defaults.validator_url');
    $operationsSorter = config('l5-swagger.defaults.operations_sort');
    $useAbsolutePath = config('l5-swagger.documentations.'.$documentation.'.paths.use_absolute_path', false);

    return view('l5-swagger::index', compact('documentation', 'urlToDocs', 'configUrl', 'validatorUrl', 'operationsSorter', 'useAbsolutePath'));
});

