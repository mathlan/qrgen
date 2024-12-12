<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Schedule::class;

    public function definition()
    {
        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

        return [
            'restaurant_id' => Restaurant::factory(),
            'day' => fake()->randomElement($days),
            'opening_time' => fake()->dateTimeBetween('11:00:00', '12:00:00')->format('H:i:s'), // Entre 11h et 12h
            'closing_time' => fake()->dateTimeBetween('22:00:00', '23:30:00')->format('H:i:s'), // Entre 22h et 23h30
        ];
    }
}
