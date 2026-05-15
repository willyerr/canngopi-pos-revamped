<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public static function authenticateAdmin($email, $password): bool
    {
        $credentials = [
            'email' => $email,
            'password' => $password
        ];

        return Auth::attempt(['email' => $email, 'password' => $password, 'role' => 'Admin']);
    }
}
