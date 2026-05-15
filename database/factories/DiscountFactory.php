<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Discount>
 */
class DiscountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    
    public function definition(): array
    {
        $discounts = [
            'Discount Mahasiswa',
            'Discount Summarecon Staff',
            'Discount Alumni'
        ];

        return [
            'name' => fake()->randomElement($discounts),
            'description' => fake()->sentence(),
            'terms_and_condition' => fake()->sentence(),
            'offer_value' => fake()->numberBetween(1, 50),
        ];
    }
}
