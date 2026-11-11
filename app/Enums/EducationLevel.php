<?php

namespace App\Enums;

enum EducationLevel: string
{
    use HasValues;

    case HighSchool = 'Pelajar SMA/SMK';
    case CollegeStudent = 'Mahasiswa';
    case FreshGraduate = 'Fresh Graduate';

    public function label(): string
    {
        return match ($this) {
            self::HighSchool => 'Pelajar SMA/SMK',
            self::CollegeStudent => 'Mahasiswa',
            self::FreshGraduate => 'Fresh Graduate',
        };
    }
}
