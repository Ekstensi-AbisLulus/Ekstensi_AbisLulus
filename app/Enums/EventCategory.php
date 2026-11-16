<?php

namespace App\Enums;

enum EventCategory: string
{
    use HasValues;

    case Hardskill = 'hardskill';
    case Softskill = 'softskill';

    public function label(): string
    {
        return match ($this) {
            self::Hardskill => 'Hard Skill',
            self::Softskill => 'Soft Skill',
        };
    }
}
