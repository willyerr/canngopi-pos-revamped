<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->integer('price');
            $table->enum('category', ['Signature', 'Breakfast', 'Dessert', 'Snack', 'Pizza', 'Burger & Sandwich', 'Soup', 'Pasta', 'Coffee', 'Non Coffee', 'Mocktail', 'Donbury']);
            $table->text('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
