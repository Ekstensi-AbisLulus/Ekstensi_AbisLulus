<?php

namespace App\Enums;

enum UserRole: string
{
    use HasValues;

    case User = 'user';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';

    public function label(): string
    {
        return match ($this) {
            self::User => 'Pengguna',
            self::Admin => 'Admin',
            self::SuperAdmin => 'Super Admin',
        };
    }
}
