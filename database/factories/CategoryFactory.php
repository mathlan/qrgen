<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Category::class;

    public function definition()
    {
        static $usedCategories = []; // Garde trace des catégories déjà utilisées

        $categories = [
            'Entrées', 'Plats principaux', 'Desserts', 'Boissons', 'Spécialités',
            'Entrées froides', 'Entrées chaudes', 'Plats végétariens', 'Plats du jour',
            'Desserts glacés', 'Vins', 'Cocktails', 'Bières', 'Smoothies', 'Apéritifs',
            'Salades', 'Tapas', 'Pizzas', 'Pâtes', 'Sushis'
        ];

        // Filtrer les catégories restantes
        $availableCategories = array_diff($categories, $usedCategories);

        // Si toutes les catégories ont été utilisées, réinitialiser
        if (empty($availableCategories)) {
            $usedCategories = [];
            $availableCategories = $categories;
        }

        $category = $this->faker->randomElement($availableCategories);
        $usedCategories[] = $category; // Marquer la catégorie comme utilisée

        return [
            'name' => $category,
            'photo' => $this->faker->imageUrl(640, 480, 'food'),
        ];
    }
}
