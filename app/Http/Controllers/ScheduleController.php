<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ScheduleController extends Controller
{
    /**
     * Display a listing of schedules.
     */
    public function index(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = null;
        }

        // Si l'utilisateur est admin, il voit tous les horaires
        if ($user && $user->role === 'admin') {
            return Schedule::with('restaurant')->get();
        }

        // Pour un utilisateur connecté, on voit seulement ses horaires
        if ($user) {
            $restaurantIds = Restaurant::where('user_id', $user->id)->pluck('id');
            return Schedule::whereIn('restaurant_id', $restaurantIds)
                ->with('restaurant')
                ->get();
        }

        // Pour les utilisateurs non connectés, on voit tous les horaires
        return Schedule::with('restaurant')->get();
    }

    /**
     * Display the specified schedule.
     */
    public function show(Schedule $schedule)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = null;
        }

        // Admin peut voir tous les horaires
        if ($user && $user->role === 'admin') {
            return $schedule->load('restaurant');
        }

        // Un utilisateur ne peut voir que les horaires de ses restaurants
        if ($user) {
            $userRestaurantIds = Restaurant::where('user_id', $user->id)->pluck('id');

            if ($userRestaurantIds->contains($schedule->restaurant_id)) {
                return $schedule->load('restaurant');
            }

            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Les utilisateurs non connectés voient tous les horaires
        return $schedule->load('restaurant');
    }

    /**
     * Store a new schedule.
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
            'day' => 'required|in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi,Dimanche',
            'opening_time' => 'required|date_format:H:i:s',
            'closing_time' => 'required|date_format:H:i:s|after:opening_time',
        ]);

        // Vérification pour les utilisateurs non-admin
        if ($user->role !== 'admin') {
            // Vérifier que l'utilisateur est le propriétaire du restaurant
            $restaurant = Restaurant::findOrFail($validatedData['restaurant_id']);

            if ($restaurant->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $schedule = Schedule::create($validatedData);

        return response()->json($schedule->load('restaurant'), 201);
    }

    /**
     * Update an existing schedule.
     */
    public function update(Request $request, Schedule $schedule)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validation des données
        $validatedData = $request->validate([
            'restaurant_id' => 'sometimes|exists:restaurants,id',
            'day' => 'sometimes|in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi,Dimanche',
            'opening_time' => 'sometimes|date_format:H:i:s',
            'closing_time' => 'sometimes|date_format:H:i:s|after:opening_time',
        ]);

        // Vérification pour les utilisateurs non-admin
        if ($user->role !== 'admin') {
            $restaurant = Restaurant::findOrFail($schedule->restaurant_id);

            if ($restaurant->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $schedule->update($validatedData);

        return response()->json($schedule->load('restaurant'));
    }

    /**
     * Delete a schedule.
     */
    public function destroy(Schedule $schedule)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Vérification pour les utilisateurs non-admin
        if ($user->role !== 'admin') {
            $restaurant = Restaurant::findOrFail($schedule->restaurant_id);

            if ($restaurant->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $schedule->delete();

        return response()->json(['message' => 'Schedule deleted successfully']);
    }
}
