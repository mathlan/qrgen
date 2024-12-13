<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        // Récupération des informations d'authentification
        $credentials = $request->only('email', 'password');

        // Tentative de génération du token JWT avec les identifiants fournis
        if (!$token = JWTAuth::attempt($credentials)) {
            // Si l'authentification échoue, renvoyer une réponse d'erreur
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Récupération de l'utilisateur authentifié
        $user = auth()->user();

        // Retourne une réponse avec le token JWT et l'utilisateur
        return response()->json([
            'token' => $token,
            'user' => $user, // Optionnel, si vous souhaitez inclure des détails utilisateur
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        // Invalider le token JWT actuel
        JWTAuth::invalidate(JWTAuth::getToken());

        // Retourner une réponse de succès (200 OK)
        return response()->json(['message' => 'Successfully logged out'], Response::HTTP_OK);
    }
}
