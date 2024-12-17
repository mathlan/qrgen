<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class RestaurantController extends Controller
{
    /**
     * Display a listing of the restaurants.
     */
    public function index()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            // Si l'utilisateur est un admin, afficher tous les restaurants
            if ($user->role === 'admin') {
                return Restaurant::all();
            }

            // Sinon, afficher uniquement les restaurants de l'utilisateur connecté
            return $user->restaurants;
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            // Si pas de token ou token invalide, retournez tous les restaurants
            return Restaurant::all();
        }
    }

    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|string',
        ]);

        // Associer le restaurant à l'utilisateur connecté
        $restaurant = $user->restaurants()->create($validatedData);

        return response()->json($restaurant, 201);
    }

    public function show(Restaurant $restaurant)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Vérifier si l'utilisateur est le propriétaire ou un admin
        if ($user->role === 'admin' || $restaurant->user_id === $user->id) {
            return $restaurant;
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    public function update(Request $request, Restaurant $restaurant)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Vérifier si l'utilisateur est le propriétaire ou un admin
        if ($user->role !== 'admin' && $restaurant->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|string',
        ]);

        $restaurant->update($validatedData);

        return response()->json($restaurant);
    }

    public function destroy(Restaurant $restaurant)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Vérifier si l'utilisateur est le propriétaire ou un admin
        if ($user->role !== 'admin' && $restaurant->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $restaurant->delete();

        return response()->json(['message' => 'Restaurant deleted successfully']);
    }
}
