<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CompteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Lister tous les comptes (Admin) ou comptes du client (Client)
// GET /mariama/v1/comptes?page=1&limit=10&type=epargne&statut=actif&search=...&sort=dateCreation&order=desc
Route::middleware('rating:10')->group(function(){
        Route::prefix(config('balde.mariama'))->group(function() {
        Route::get('comptes', [CompteController::class, 'index']);
        });
});



