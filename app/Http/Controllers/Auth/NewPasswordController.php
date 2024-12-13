<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NewPasswordController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            // Validation détaillée
            $validatedData = $request->validate([
                'email' => 'required|email|exists:users,email',
                'token' => 'required|string',
                'password' => 'required|min:8|confirmed'
            ]);

            // Récupérer le token de réinitialisation
            $passwordReset = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            // Vérification du token
            if (!$passwordReset) {
                return response()->json([
                    'message' => 'Aucune demande de réinitialisation trouvée'
                ], 400);
            }

            // Vérification de l'expiration du token (par exemple, 1 heure)
            $tokenCreatedAt = Carbon::parse($passwordReset->created_at);
            if ($tokenCreatedAt->diffInHours(Carbon::now()) > 1) {
                return response()->json([
                    'message' => 'Le token a expiré'
                ], 400);
            }

            // Vérification de la correspondance du token
            if (!Hash::check($request->token, $passwordReset->token)) {
                return response()->json([
                    'message' => 'Token invalide'
                ], 400);
            }

            // Trouver l'utilisateur
            $user = User::where('email', $request->email)->first();

            // Mettre à jour le mot de passe
            $user->password = Hash::make($request->password);
            $user->save();

            // Supprimer le token utilisé
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            // Log de l'événement
            Log::info('Mot de passe réinitialisé', [
                'email' => $request->email
            ]);

            return response()->json([
                'message' => 'Mot de passe réinitialisé avec succès'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Gestion des erreurs de validation
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Gestion des erreurs génériques
            Log::error('Erreur de réinitialisation de mot de passe', [
                'message' => $e->getMessage(),
                'email' => $request->email
            ]);

            return response()->json([
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
