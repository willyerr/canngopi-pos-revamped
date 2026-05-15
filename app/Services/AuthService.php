<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function getUserRole()
    {
        return Auth::check() ? Auth::user()->role : null;
    }

    public function login(array $credentials): bool
    {   
        $validator = Validator::make($credentials, [
            'email' => ['required', 'email'],
            'password' => ['required', 'string']
        ]);

        if($validator->fails())
            throw new ValidationException(implode(', ', $validator->errors()->all()));

        $authenticated = Auth::attempt($credentials);
        return $authenticated;
    }
}
