<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Recipe::class;

    public function definition()
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 5, 50),
//            'ingredients' => fake()->words(5),
            'ingredients' => json_encode(
                \App\Models\Ingredient::inRandomOrder()->limit(rand(1, 5))->pluck('id')->toArray()
            ),
            'photo' => fake()->imageUrl(640, 480, 'food'),
        ];
    }
}
