<?php

namespace App\Enums;

enum ArticleStatus: string
{
    use HasValues;

    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Published => 'Dipublikasikan',
        };
    }
}
