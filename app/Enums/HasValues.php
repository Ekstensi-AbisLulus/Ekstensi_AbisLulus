<?php

namespace App\Enums;

trait HasValues
{
    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<string, string> value => label (untuk <select>)
     */
    public static function options(): array
    {
        return array_combine(self::values(), array_map(fn (self $case) => $case->label(), self::cases()));
    }
}
