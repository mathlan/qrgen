<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        // Authentification des données
        $request->authenticate();

        // Régénération de la session pour des raisons de sécurité
        $request->session()->regenerate();

        // Récupération de l'utilisateur authentifié
        $user = Auth::user();

        // Génération d'un token personnel pour cet utilisateur
        $token = $user->createToken('API Token')->plainTextToken;

        // Retourne une réponse avec le token
        return response()->json([
            'token' => $token,
            'user' => $user, // Optionnel, si vous souhaitez inclure des détails utilisateur
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
