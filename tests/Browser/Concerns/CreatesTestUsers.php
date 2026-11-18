<?php

namespace Tests\Browser\Concerns;

use App\Models\User;
use Database\Factories\UserFactory;

/**
 * Pembuat akun uji untuk test Dusk.
 *
 * Semua akun memakai password "password" (lihat UserFactory).
 *
 * Parameter $profile:
 *  - []    (default) → profil terisi data acak dari UserProfileFactory.
 *  - [...] → profil acak dengan atribut yang di-override.
 *  - $this->emptyProfile() → baris user_profiles ada tetapi semua kolom kosong
 *            (kondisi "profil belum dilengkapi", sama seperti setelah registrasi).
 *  - null  → tanpa baris user_profiles sama sekali.
 */
trait CreatesTestUsers
{
    public const TEST_PASSWORD = 'password';

    protected function makeUser(array $attributes = [], ?array $profile = []): User
    {
        return $this->buildTestUser(User::factory(), $attributes, $profile);
    }

    protected function makeAdmin(array $attributes = [], ?array $profile = []): User
    {
        return $this->buildTestUser(User::factory()->admin(), $attributes, $profile);
    }

    protected function makeSuperAdmin(array $attributes = [], ?array $profile = []): User
    {
        return $this->buildTestUser(User::factory()->superAdmin(), $attributes, $profile);
    }

    /**
     * Atribut profil kosong (semua kolom opsional bernilai null).
     */
    protected function emptyProfile(): array
    {
        return [
            'phone' => null,
            'education_level' => null,
            'institution' => null,
            'graduation_year' => null,
            'bio' => null,
            'birth_date' => null,
            'gender' => null,
            'address' => null,
            'linkedin_url' => null,
            'avatar_path' => null,
        ];
    }

    private function buildTestUser(UserFactory $factory, array $attributes, ?array $profile): User
    {
        // Password selalu "password" agar konsisten untuk test login lewat form.
        unset($attributes['password']);

        if ($profile !== null) {
            $factory = $factory->withProfile($profile);
        }

        return $factory->create($attributes)->load('profile');
    }
}
