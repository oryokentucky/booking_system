<?php

namespace App\Rules;

class UserRules
{
    public static function createRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'status' => 'nullable|string',
        ];
    }
    
    public static function updateRules(int $id): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'status' => 'nullable|string',
        ];
    }
}
