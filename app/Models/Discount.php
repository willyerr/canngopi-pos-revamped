<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    /** @use HasFactory<\Database\Factories\DiscountFactory> */
    use HasFactory;

    protected $guarded = [];

    // (TAMBAHAN DISARANKAN) 
    // Mengubah tipe data secara otomatis saat ditarik dari database
    protected $casts = [
        'offer_value' => 'float', 
        'minimum_purchase' => 'float',
    ];
}