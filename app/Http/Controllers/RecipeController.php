<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class RecipeController extends Controller
{
    /**
     * Display a listing of the user's recipes or all recipes if admin.
     */
    public function index(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = null;
        }

        // Si l'utilisateur est admin, il voit toutes les recettes
        if ($user && $user->role === 'admin') {
            return Recipe::with(['restaurant', 'category', 'ingredients'])->get();
        }

        // Pour un utilisateur connecté, on voit seulement ses recettes
        if ($user) {
            $restaurantIds = Restaurant::where('user_id', $user->id)->pluck('id');
            return Recipe::whereIn('restaurant_id', $restaurantIds)
                ->with(['restaurant', 'category', 'ingredients'])
                ->get();
        }

        // Pour les utilisateurs non connectés, on voit toutes les recettes
        return Recipe::with(['restaurant', 'category', 'ingredients'])->get();
    }

    /**
     * Display a specific recipe.
     */
    public function show(Recipe $recipe)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = null;
        }

        // Admin peut voir toutes les recettes
        if ($user && $user->role === 'admin') {
            return $recipe->load(['restaurant', 'category', 'ingredients']);
        }

        // Un utilisateur ne peut voir que ses propres recettes
        if ($user) {
            $userRestaurantIds = Restaurant::where('user_id', $user->id)->pluck('id');

            if ($userRestaurantIds->contains($recipe->restaurant_id)) {
                return $recipe->load(['restaurant', 'category', 'ingredients']);
            }
        }

        // Les utilisateurs non connectés voient toutes les recettes
        return $recipe->load(['restaurant', 'category', 'ingredients']);
    }

    /**
     * Store a new recipe.
     */
    public function store(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validation des données
        $validatedData = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'ingredients' => 'nullable|array',
            'photo' => 'nullable|string',
        ]);

        // Vérification pour les utilisateurs non-admin
        if ($user->role !== 'admin') {
            // Vérifier que l'utilisateur est le propriétaire du restaurant
            $restaurant = Restaurant::findOrFail($validatedData['restaurant_id']);

            if ($restaurant->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $recipe = Recipe::create($validatedData);

        // Attacher les ingrédients si fournis
        if (isset($validatedData['ingredients'])) {
            $recipe->ingredients()->sync($validatedData['ingredients']);
        }

        return response()->json($recipe->load(['restaurant', 'category', 'ingredients']), 201);
    }

    /**
     * Update an existing recipe.
     */
    public function update(Request $request, Recipe $recipe)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validation des données
        $validatedData = $request->validate([
            'restaurant_id' => 'sometimes|exists:restaurants,id',
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric',
            'ingredients' => 'nullable|array',
            'photo' => 'nullable|string',
        ]);

        // Vérification pour les utilisateurs non-admin
        if ($user->role !== 'admin') {
            $restaurant = Restaurant::findOrFail($recipe->restaurant_id);

            if ($restaurant->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $recipe->update($validatedData);

        // Mettre à jour les ingrédients si fournis
        if (isset($validatedData['ingredients'])) {
            $recipe->ingredients()->sync($validatedData['ingredients']);
        }

        return response()->json($recipe->load(['restaurant', 'category', 'ingredients']));
    }

    /**
     * Delete a recipe.
     */
    public function destroy(Recipe $recipe)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Vérification pour les utilisateurs non-admin
        if ($user->role !== 'admin') {
            $restaurant = Restaurant::findOrFail($recipe->restaurant_id);

            if ($restaurant->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $recipe->delete();

        return response()->json(['message' => 'Recipe deleted successfully']);
    }
}
