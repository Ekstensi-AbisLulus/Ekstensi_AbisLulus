<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_user_can_save_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Nama Lengkap',
            'phone' => '+6281234567890',
            'education_level' => 'Mahasiswa',
            'birth_date' => '2000-01-02',
            'gender' => 'female',
            'address' => 'Bandung',
            'institution' => 'Universitas',
            'graduation_year' => '2025',
            'bio' => 'Tentang saya',
            'linkedin_url' => 'https://linkedin.com/in/pengguna',
        ])->assertRedirect(route('profile.edit'))->assertSessionHas('success', 'Profil berhasil disimpan');

        $this->assertSame('Nama Lengkap', $user->fresh()->name);
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'phone' => '+6281234567890',
            'education_level' => 'Mahasiswa',
            'gender' => 'female',
            'address' => 'Bandung',
        ]);
    }

    public function test_invalid_phone_fails_validation_and_preserves_input(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.update'), [
            'name' => 'Nama',
            'phone' => 'nomor salah',
            'education_level' => 'Mahasiswa',
        ])->assertRedirect(route('profile.edit'))->assertSessionHasErrors('phone')->assertSessionHasInput('phone', 'nomor salah');

        $this->assertDatabaseMissing('user_profiles', ['user_id' => $user->id]);
    }

    public function test_admin_roles_cannot_access_user_profile(): void
    {
        foreach ([User::factory()->admin()->create(), User::factory()->superAdmin()->create()] as $staff) {
            $this->actingAs($staff)->get(route('profile.edit'))->assertForbidden();
        }
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }
}
