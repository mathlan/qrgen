<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        Log::info('Request data', ['data' => $request->all()]);

        try {
            // Débogage : vérifiez le contenu exact de la requête
            if (!$request->has('email')) {
                Log::error('No email in request', ['request' => $request->all()]);
                return response()->json([
                    'message' => 'Email est requis',
                    'request_content' => $request->all()
                ], 422);
            }

            $validatedData = $request->validate([
                'email' => ['required', 'email', 'exists:users,email']
            ]);

            $user = User::where('email', $request->input('email'))->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            $token = Str::random(60);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'email' => $user->email,
                    'token' => Hash::make($token),
                    'created_at' => now()
                ]
            );

            return response()->json([
                'message' => 'Lien de réinitialisation généré',
                'token' => $token  // À des fins de test
            ], 200);

        } catch (ValidationException $e) {
            Log::error('Validation Error', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
                'request_content' => $request->all()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Erreur inattendue',
                'error' => $e->getMessage(),
                'request_content' => $request->all()
            ], 500);
        }
    }
}
