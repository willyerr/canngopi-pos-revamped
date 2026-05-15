<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $menus = [
            'Nasi Goreng Spesial',
            'Mie Ayam Bakso',
            'Es Teh Manis',
            'Ayam Bakar Madu',
            'Kopi Susu Gula Aren',
            'Soto Ayam Lamongan',
            'Pecel Lele',
            'Indomie Rebus Telur',
            'Jus Alpukat',
            'Es Jeruk Nipis'
        ];

        return [
            'name' => fake()->randomElement($menus),
            'price' => fake()->numberBetween(10000, 50000),
            'category' => fake()->randomElement(['Signature', 'Breakfast', 'Dessert', 'Snack', 'Pizza', 'Burger & Sandwich', 'Soup', 'Pasta', 'Coffee', 'Non Coffee', 'Mocktail', 'Donbury'])
        ];
    }
}
