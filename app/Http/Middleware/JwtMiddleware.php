<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Tente de récupérer l'utilisateur authentifié via le token JWT
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            // Si le token n'est pas valide ou absent, retourne une erreur 401
            return response()->json(['error' => 'Token non valide ou absent'], 401);
        }

        // L'utilisateur est authentifié, on passe à la requête suivante
        return $next($request);
    }
}


