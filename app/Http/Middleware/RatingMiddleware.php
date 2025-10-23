<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RatingMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response->getStatusCode() == 429) { // Rate limit exceeded
            Log::info('Rate limit atteint pour l\'utilisateur', [
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
        }

        return $response;
    }
}
