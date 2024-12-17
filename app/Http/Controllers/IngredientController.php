<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class IngredientController extends Controller
{
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

    public function show(Ingredient $ingredient)
    {
        return $ingredient;
    }

    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Vérification du rôle admin
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:ingredients,name',
            'allergen' => 'sometimes|boolean'
        ]);

        $ingredient = Ingredient::create($validatedData);

        return response()->json($ingredient, 201);
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Vérification du rôle admin
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255|unique:ingredients,name,' . $ingredient->id,
            'allergen' => 'sometimes|boolean'
        ]);

        $ingredient->update($validatedData);

        return response()->json($ingredient);
    }

    public function destroy(Ingredient $ingredient)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Vérification du rôle admin
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $ingredient->delete();

        return response()->json(['message' => 'Ingredient deleted successfully']);
    }
}
