<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class EmailVerificationNotificationController extends Controller
{
    public function sendVerificationLink(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Vérifier si l'email est déjà vérifié
            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email déjà vérifié',
                    'status' => 'already_verified'
                ], 200);
            }

            // Générer un lien de vérification signé
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $user->id,
                    'hash' => sha1($user->email)
                ]
            );

            $verificationUrl = str_replace(url('/'), url('/api'), $verificationUrl);


            //! Envoyer la notification !!! A déquoter pour la mise en prod
//            $user->sendEmailVerificationNotification($verificationUrl);

            // Log de l'envoi
            Log::info('Lien de vérification envoyé', [
                'user_id' => $user->id,
                'email' => $user->email,
                'user_email_hash' => sha1($user->email),
                'signature_valid' => request()->hasValidSignature(),
                'user_email' => $user->email
            ]);

            //! A retirer en PROD
            return response()->json([
                'message' => 'Lien de vérification généré',
                'verification_url' => $verificationUrl,
                'status' => 'verification_link_generated'
            ], 200);

            //! A remettre en PROD
//!            return response()->json([
//!                'message' => 'Lien de vérification envoyé',
//!                'status' => 'verification_link_sent'
//!            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi du lien de vérification', [
                'message' => $e->getMessage(),
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'message' => 'Erreur lors de l\'envoi du lien de vérification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
