<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email déjà vérifié',
                    'status' => 'already_verified'
                ], 200);
            }

            // Générer un hash simple basé sur l'email
            $verificationHash = sha1($user->email);

            $verificationUrl = url("/api/verify-email/{$user->id}/{$verificationHash}");

            Log::info('Lien de vérification généré', [
                'user_id' => $user->id,
                'email' => $user->email,
                'verification_url' => $verificationUrl
            ]);

            return response()->json([
                'message' => 'Lien de vérification généré',
                'verification_url' => $verificationUrl,
                'status' => 'verification_link_generated'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du lien de vérification', [
                'message' => $e->getMessage(),
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'message' => 'Erreur lors de la génération du lien de vérification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
