<?php

namespace App\Helpers;

class UtilityHelper
{
    public static function formatLabel($label) {
        $label = str_replace('_', ' ', $label);
        $label = ucwords($label);
    
        return $label;
    }

    public static function formatCurrency($amount) 
    {
        return 'Rp ' . number_format($amount, fmod($amount, 1) === 0.0 ? 0 : 2, ',', '.');
    }
}
