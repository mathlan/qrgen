<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
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
            // Enhanced logging for debugging
            Log::info('Email Verification Attempt', [
                'id' => $id,
                'hash' => $hash,
                'full_url' => $request->fullUrl(),
                'all_query_params' => $request->query(),
                'has_valid_signature' => URL::hasValidSignature($request),
                'signature' => $request->query('signature'),
                'expires' => $request->query('expires')
            ]);

            $user = User::findOrFail($id);

            // Verify hash matches email hash
            if (sha1($user->email) !== $hash) {
                Log::warning('Hash mismatch', [
                    'stored_email_hash' => sha1($user->email),
                    'provided_hash' => $hash
                ]);
                return response()->json([
                    'message' => 'Lien de vérification invalide'
                ], 400);
            }

            // Signature validation
            if (!URL::hasValidSignature($request)) {
                return response()->json([
                    'message' => 'Lien de vérification invalide ou expiré'
                ], 400);
            }

            // Check if email already verified
            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email déjà vérifié',
                    'status' => 'already_verified'
                ], 200);
            }

            // Mark email as verified
            $user->markEmailAsVerified();

            Log::info('Email vérifié', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'message' => 'Email vérifié avec succès',
                'status' => 'verified'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur de vérification email', [
                'message' => $e->getMessage(),
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Erreur lors de la vérification de l\'email',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
