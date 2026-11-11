<?php

namespace App\Enums;

enum EventStatus: string
{
    use HasValues;

    case Draft = 'draft';
    case Published = 'published';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Published => 'Dipublikasikan',
            self::Cancelled => 'Dibatalkan',
            self::Completed => 'Selesai',
        };
    }
}
