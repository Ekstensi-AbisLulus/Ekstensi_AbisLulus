<?php

namespace Database\Seeders;

use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * PRD §6.3 — 1 super admin, 2 admin, 5 user; semua dengan profil.
 * Semua akun berpassword "password".
 */
class RoleUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->createUser('Super Admin AbisLulus', 'superadmin@abislulus.test', UserRole::SuperAdmin, [
            'phone' => '081200000001',
            'institution' => 'Tim Ekstensi AbisLulus',
            'bio' => 'Pengelola platform AbisLulus.',
        ]);

        $admins = [
            ['Rina Pratiwi', 'admin1@abislulus.test', 'Komunitas Digital Talent Bandung'],
            ['Andi Wijaya', 'admin2@abislulus.test', 'Karier Muda Indonesia'],
        ];

        foreach ($admins as $i => [$name, $email, $institution]) {
            $this->createUser($name, $email, UserRole::Admin, [
                'phone' => '08120000001'.($i + 1),
                'institution' => $institution,
                'bio' => 'Mentor dan pengelola konten di '.$institution.'.',
            ]);
        }

        $users = [
            ['Siti Nurhaliza', Gender::Female, EducationLevel::FreshGraduate, 'SMKN 1 Bandung', 2026],
            ['Budi Santoso', Gender::Male, EducationLevel::HighSchool, 'SMAN 3 Bandung', 2026],
            ['Dewi Lestari', Gender::Female, EducationLevel::CollegeStudent, 'Telkom University', 2027],
            ['Rizky Ramadhan', Gender::Male, EducationLevel::FreshGraduate, 'SMKN 4 Bandung', 2025],
            ['Putri Ayu', Gender::Female, EducationLevel::FreshGraduate, 'SMAN 1 Cimahi', 2025],
        ];

        foreach ($users as $i => [$name, $gender, $level, $institution, $year]) {
            $this->createUser($name, 'user'.($i + 1).'@abislulus.test', UserRole::User, [
                'phone' => '08570000000'.($i + 1),
                'gender' => $gender,
                'education_level' => $level,
                'institution' => $institution,
                'graduation_year' => $year,
                'birth_date' => now()->subYears(18 + $i)->format('Y-m-d'),
                'address' => 'Bandung, Jawa Barat',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    private function createUser(string $name, string $email, UserRole $role, array $profile): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'role' => $role,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();
        $user->profile()->create($profile);

        return $user;
    }
}
