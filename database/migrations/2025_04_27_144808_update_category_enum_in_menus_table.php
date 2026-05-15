<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE menus 
            MODIFY COLUMN category ENUM(
                'Signature', 'Breakfast', 'Dessert', 'Snack', 'Pizza', 'Burger & Sandwich', 'Soup', 'Pasta', 'Coffee', 'Non Coffee', 'Mocktail', 'Donbury', 'Others'
            ) NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE menus 
            MODIFY COLUMN category ENUM(
                'Signature', 'Breakfast', 'Dessert', 'Snack', 'Pizza', 'Burger & Sandwich', 'Soup', 'Pasta', 'Coffee', 'Non Coffee', 'Mocktail', 'Donbury'
            ) NOT NULL
        ");
    }
};
