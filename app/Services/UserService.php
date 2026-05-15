<?php

namespace App\Services;

use Exception;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;

class UserService
{
    private $fields = [
        'fullname' => ['required', 'string'],
        'email' => ['required', 'email'],
        'password' => ['required', 'string', 'min:6'],
        'role' => ['required', 'string', 'in:Admin,Cashier,Accounting,Kitchen,IT']
    ];

    public function list()
    {
        return User::query();
    }

    public function show(int $id): User
    {
        return User::findOrFail($id);
    }

    public function store(array $user): User
    {
        $validator = Validator::make($user, $this->fields);
        if($validator->fails()) 
            throw new \InvalidArgumentException(implode(', ', $validator->errors()->all()));
        
        return User::create([
            'fullname' => $user['fullname'],
            'email' => $user['email'],
            'password' => Hash::make($user['password']),
            'role' => $user['role']
        ]);
    }

    public function edit(int $id, array $user): bool
    {
        $validator = Validator::make($user, Arr::except($this->fields, ['password']));
        if($validator->fails()) 
            throw new \InvalidArgumentException(implode(', ', $validator->errors()->all()));
        
        return User::where('id', $id)->update([
            'fullname' => $user['fullname'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);
    }

    public function delete(int $id): bool
    {
        return User::destroy($id);
    }
}
