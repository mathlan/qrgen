<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function __invoke($id, $hash, Request $request): JsonResponse
    {
        return $this->store($id, $hash, $request);
    }

    public function store($id, $hash, Request $request): JsonResponse
    {
        try {
            // Trouver l'utilisateur
            $user = User::findOrFail($id);

            // Vérifier le hash
            if (sha1($user->email) !== $hash) {
                Log::warning('Hash de vérification invalide', [
                    'user_id' => $id,
                    'stored_hash' => sha1($user->email),
                    'provided_hash' => $hash
                ]);

                return response()->json([
                    'message' => 'Lien de vérification invalide',
                    'status' => 'invalid_verification_link'
                ], 400);
            }

            // Vérifier si l'email est déjà vérifié
            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email déjà vérifié',
                    'status' => 'already_verified'
                ], 200);
            }

            // Marquer l'email comme vérifié
            $user->markEmailAsVerified();

            Log::info('Email vérifié avec succès', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'message' => 'Email vérifié avec succès',
                'status' => 'verified'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur de vérification d\'email', [
                'message' => $e->getMessage(),
                'user_id' => $id
            ]);

            return response()->json([
                'message' => 'Erreur lors de la vérification de l\'email',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
