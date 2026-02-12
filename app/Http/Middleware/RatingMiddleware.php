<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class RatingMiddleware
{
    /**
     * Si l'utilisateur non-auth : on peut limiter par IP.
     * Ici on stocke compteur dans le cache.
     */
    public function handle(Request $request, Closure $next, $limit = 10)
    {
        $key = 'rating:' . ($request->user()?->id ?? $request->ip());

        $count = Cache::get($key, 0);
        $count++;
        Cache::put($key, $count, now()->addHour()); // expire après 1 heure

        if ($count >= (int)$limit) {
            // enregistrer dans un log ou base de données
            Log::info("Rating limit atteint pour: {$key}", ['count' => $count]);

            // tu peux émettre un événement, sauvegarder en BDD, etc.
        }

        return $next($request);
    }
}

