<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Restaurant>
 */
class RestaurantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Restaurant::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company(),
            'description' => fake()->paragraph(),
            'photo' => fake()->imageUrl(640, 480, 'restaurant'),
        ];
    }
}
