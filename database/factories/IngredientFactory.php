<?php

namespace Database\Factories;

use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ingredient>
 */
class IngredientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Ingredient::class;

    public function definition()
    {
        $ingredients = [
            'Farine', 'Œufs', 'Lait', 'Beurre', 'Sucre', 'Sel', 'Poivre',
            'Poulet', 'Boeuf', 'Poisson', 'Riz', 'Pommes de terre',
            'Tomates', 'Oignons', 'Ail', 'Carottes', 'Champignons'
        ];

        return [
            'name' => fake()->unique()->randomElement($ingredients),
            'allergen' => fake()->boolean(20), // 20% chance d'être un allergène
        ];
    }
}
