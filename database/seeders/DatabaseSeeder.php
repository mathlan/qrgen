<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Schedule;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Création des utilisateurs personnalisés
        $mathieu = User::factory()->create([
            'name' => 'Mathieu',
            'email' => 'mathieulannel@gmail.com',
            'role' => "admin",
            'password' => bcrypt('motdepasse'),
        ]);

        $nathan = User::factory()->create([
            'name' => 'Nathan',
            'email' => 'nathan.macaigne@hotmail.com',
            'role' => 'admin',
            'password' => bcrypt('motdepasse'),
        ]);

        // Création des 5 autres utilisateurs
        $otherUsers = User::factory(5)->create();

        // Création des catégories avec la factory
//        $categories = Category::factory(5)->create();

        // Création des ingrédients
        $ingredients = Ingredient::factory(15)->create();

        // Fonction pour créer les données associées à un utilisateur
        $createUserData = function($user) use ($ingredients) {
            // Créer 1-2 restaurants pour l'utilisateur
            $restaurants = Restaurant::factory(rand(1, 2))->create([
                'user_id' => $user->id
            ]);

            // Pour chaque restaurant
            $restaurants->each(function ($restaurant) use ($user, $restaurants, $ingredients) {

                // Créer les cat. du resto
                $categories = Category::factory(rand(3, 6))->create([
                    'restaurant_id' => $restaurant->id
                ]);

                // Créer les horaires
                $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

                $restaurants->each(function ($restaurant) use ($days) {
                    foreach ($days as $day) {
                        Schedule::factory()->create([
                            'restaurant_id' => $restaurant->id,
                            'day' => $day,
                            'opening_time' => fake()->dateTimeBetween('11:00:00', '12:00:00')->format('H:i:s'), // Entre 11h et 12h
                            'closing_time' => fake()->dateTimeBetween('22:00:00', '23:30:00')->format('H:i:s'), // Entre 22h et 23h30
                        ]);
                    }
                });

                // Créer les recettes pour chaque catégorie
                $categories->each(function ($category) use ($restaurant, $ingredients) {
                    Recipe::factory(rand(3, 5))->create([
                        'restaurant_id' => $restaurant->id,
                        'category_id' => $category->id
                    ])->each(function ($recipe) use ($ingredients) {
                        // Attacher des ingrédients aux recettes
                        $recipe->ingredients()->attach(
                            $ingredients->random(rand(3, 6))->pluck('id')->toArray()
                        );
                    });
                });
            });
        };

        // Créer les données pour chaque utilisateur
        $createUserData($mathieu);
        $createUserData($nathan);
        $otherUsers->each(function($user) use ($createUserData) {
            $createUserData($user);
        });
    }
}
