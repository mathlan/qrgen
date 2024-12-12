<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestaurantController extends Controller
{
    /**
     * Display a listing of the restaurants.
     */
    public function index()
    {
        // Si l'utilisateur est un admin, afficher tous les restaurants
        if (Auth::user()->role === 'admin') {
            return Restaurant::all();
        }

        // Sinon, afficher uniquement les restaurants de l'utilisateur connecté
        return Auth::user()->restaurants;
    }

    /**
     * Store a newly created restaurant.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|string',
        ]);

        // Associer le restaurant à l'utilisateur connecté
        $restaurant = Auth::user()->restaurants()->create($validatedData);

        return response()->json($restaurant, 201);
    }

    /**
     * Display the specified restaurant.
     */
    public function show(Restaurant $restaurant)
    {
        // Vérifier si l'utilisateur est le propriétaire ou un admin
        if (Auth::user()->role === 'admin' || $restaurant->user_id === Auth::id()) {
            return $restaurant;
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    /**
     * Update the specified restaurant.
     */
    public function update(Request $request, Restaurant $restaurant)
    {
        // Vérifier si l'utilisateur est le propriétaire ou un admin
        if (Auth::user()->role !== 'admin' && $restaurant->user_id !== Auth::id()) {
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

    /**
     * Remove the specified restaurant.
     */
    public function destroy(Restaurant $restaurant)
    {
        // Vérifier si l'utilisateur est le propriétaire ou un admin
        if (Auth::user()->role !== 'admin' && $restaurant->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $restaurant->delete();

        return response()->json(['message' => 'Restaurant deleted successfully']);
    }
}
