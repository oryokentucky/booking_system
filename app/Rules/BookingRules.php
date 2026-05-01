<?php

namespace App\Rules;

class BookingRules
{
    public static function createRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'status' => 'nullable|string',
        ];
    }
    
    public static function updateRules(int $id): array
    {
        return [
            'name' => 'required|string|max:255',
            'status' => 'nullable|string',
        ];
    }
}
