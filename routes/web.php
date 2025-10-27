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

Route::get('/api/docs', function () {
    return view('swagger');
});

Route::get('/api/documentation', function () {
    return view('swagger');
})->name('l5-swagger.default.api');

Route::get('/api-docs.json', function () {
    return response()->file(storage_path('api-docs/api-docs.json'));
});

