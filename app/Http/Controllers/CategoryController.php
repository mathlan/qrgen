<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        // Filtres optionnels
        if ($request->has('name')) {
            $query->where('name', 'LIKE', '%' . $request->input('name') . '%');
        }

        // Si un ID de restaurant est spécifié
        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->input('restaurant_id'));
        }

        // Pagination
        return $query->paginate($request->input('per_page', 10));
    }

    public function show(Category $category)
    {
        return $category;
    }

    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|string',
            'restaurant_id' => 'required|exists:restaurants,id'
        ]);

        // Vérifier la propriété du restaurant ou le rôle admin
        $restaurant = \App\Models\Restaurant::findOrFail($validatedData['restaurant_id']);

        if ($user->role !== 'admin' && $restaurant->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $category = Category::create($validatedData);

        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Vérifier la propriété ou le rôle admin
        if ($user->role !== 'admin' && $category->restaurant->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'photo' => 'nullable|string',
        ]);

        $category->update($validatedData);

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Vérifier la propriété ou le rôle admin
        if ($user->role !== 'admin' && $category->restaurant->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
