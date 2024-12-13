<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Récupérer tous les utilisateurs
        $users = User::all();
        return response()->json($users);
    }

    public function show($id)
    {
        // Vérifier si l'utilisateur existe
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        return response()->json($user);
    }
}
