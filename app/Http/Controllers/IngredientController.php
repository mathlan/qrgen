<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IngredientController extends Controller
{
    /**
     * Liste tous les ingrédients (accessible à tous)
     */
    public function index(Request $request)
    {
        $query = Ingredient::query();

        // Filtres optionnels
        if ($request->has('name')) {
            $query->where('name', 'LIKE', '%' . $request->input('name') . '%');
        }

        if ($request->has('allergen')) {
            $query->where('allergen', $request->input('allergen'));
        }

        // Pagination
        return $query->paginate($request->input('per_page', 10));
    }

    /**
     * Affiche un ingrédient spécifique (accessible à tous)
     */
    public function show(Ingredient $ingredient)
    {
        return $ingredient;
    }

    /**
     * Crée un nouvel ingrédient (réservé aux admins)
     */
    public function store(Request $request)
    {
        // Vérification du rôle admin
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:ingredients,name',
            'allergen' => 'sometimes|boolean'
        ]);

        $ingredient = Ingredient::create($validatedData);

        return response()->json($ingredient, 201);
    }

    /**
     * Met à jour un ingrédient (réservé aux admins)
     */
    public function update(Request $request, Ingredient $ingredient)
    {
        // Vérification du rôle admin
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255|unique:ingredients,name,' . $ingredient->id,
            'allergen' => 'sometimes|boolean'
        ]);

        $ingredient->update($validatedData);

        return response()->json($ingredient);
    }

    /**
     * Supprime un ingrédient (réservé aux admins)
     */
    public function destroy(Ingredient $ingredient)
    {
        // Vérification du rôle admin
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $ingredient->delete();

        return response()->json(['message' => 'Ingredient deleted successfully']);
    }
}
