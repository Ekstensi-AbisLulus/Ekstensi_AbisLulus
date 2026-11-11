<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * No. HP / WhatsApp: hanya angka (boleh diawali "+"), 10–15 digit (PRD §7 F2/F3).
 * Dipakai bersama oleh profil user (PBI-04/05) dan profil admin (PBI-07/08).
 */
class PhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^\+?[0-9]{10,15}$/', $value)) {
            $fail('No. HP hanya boleh berisi angka (boleh diawali +) dan terdiri dari 10–15 digit.');
        }
    }
}
