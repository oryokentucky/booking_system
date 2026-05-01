<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        // Add additional fillable fields here
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
        ];
    }
}
