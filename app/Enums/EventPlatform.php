<?php

namespace App\Enums;

enum EventPlatform: string
{
    use HasValues;

    case Zoom = 'zoom';
    case GoogleMeet = 'google_meet';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Zoom => 'Zoom',
            self::GoogleMeet => 'Google Meet',
            self::Other => 'Lainnya',
        };
    }
}
