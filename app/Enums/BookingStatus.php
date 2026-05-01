<?php

namespace App\Enums;

enum BookingStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case DEACTIVATED = 'deactivated';

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::ACTIVE => 'success',
            self::DEACTIVATED => 'error',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::ACTIVE => 'Active',
            self::DEACTIVATED => 'Deactivated',
        };
    }
}
