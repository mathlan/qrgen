<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Login
     * @group Auth
     */
    public function store(LoginRequest $request)
    {
        // Récupération des informations d'authentification
        $credentials = $request->only('email', 'password');

        try {
            // Tentative de génération du token JWT avec les identifiants fournis
            if (!$token = JWTAuth::attempt($credentials)) {
                // Si l'authentification échoue, renvoyer une réponse d'erreur
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (JWTException $e) {
            // En cas d'erreur lors de la création du token
            return response()->json(['error' => 'Could not create token'], 500);
        }

        // Récupération de l'utilisateur authentifié
        $user = JWTAuth::user();

        // Retourne une réponse avec le token JWT et l'utilisateur
        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Logout
     * @group Auth
     */
    public function destroy(Request $request)
    {
        try {
            // Invalider le token JWT actuel
            JWTAuth::invalidate(JWTAuth::getToken());

            // Retourner une réponse de succès (200 OK)
            return response()->json(['message' => 'Successfully logged out'], Response::HTTP_OK);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to logout'], 500);
        }
    }
}
