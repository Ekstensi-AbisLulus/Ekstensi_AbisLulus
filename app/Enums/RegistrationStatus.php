<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    use HasValues;

    case Registered = 'registered';
    case Cancelled = 'cancelled';
    case Attended = 'attended';

    public function label(): string
    {
        return match ($this) {
            self::Registered => 'Terdaftar',
            self::Cancelled => 'Dibatalkan',
            self::Attended => 'Hadir',
        };
    }
}
