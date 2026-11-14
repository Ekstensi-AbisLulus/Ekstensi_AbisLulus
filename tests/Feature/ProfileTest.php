<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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

    public function test_profile_form_is_filled_with_saved_profile_values(): void
    {
        $user = User::factory()->create(['name' => 'Nama Tersimpan']);
        $user->profile()->create([
            'phone' => '081234567890',
            'education_level' => 'Mahasiswa',
            'institution' => 'Kampus Contoh',
            'address' => 'Bandung',
            'bio' => 'Bio tersimpan',
        ]);

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('value="Nama Tersimpan"', false)
            ->assertSee('value="081234567890"', false)
            ->assertSee('value="Kampus Contoh"', false)
            ->assertSee('value="Bandung"', false)
            ->assertSee('Bio tersimpan');
    }

    public function test_updated_name_is_shown_in_navbar(): void
    {
        $user = User::factory()->create(['name' => 'Nama Lama']);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Nama Terbaru',
            'phone' => '081234567890',
            'education_level' => 'Mahasiswa',
        ])->assertRedirect(route('profile.edit'));

        $this->get(route('profile.edit'))->assertOk()->assertSee('Nama Terbaru');
    }

    public function test_user_can_delete_avatar_file_and_clear_avatar_path(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        Storage::disk('public')->put('avatars/profile.jpg', 'foto');
        $user->profile()->create([
            'phone' => '081234567890',
            'education_level' => 'Mahasiswa',
            'avatar_path' => 'avatars/profile.jpg',
        ]);

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('data-bs-toggle="modal"', false)
            ->assertSee('data-bs-target="#deleteAvatarModal"', false)
            ->assertSee('data-bs-dismiss="modal"', false)
            ->assertSee(route('profile.avatar.destroy'));

        $this->delete(route('profile.avatar.destroy'))->assertRedirect(route('profile.edit'));

        Storage::disk('public')->assertMissing('avatars/profile.jpg');
        $this->assertDatabaseHas('user_profiles', ['user_id' => $user->id, 'avatar_path' => null]);
    }

    public function test_cancel_delete_avatar_modal_leaves_photo_unchanged(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        Storage::disk('public')->put('avatars/profile.jpg', 'foto');
        $user->profile()->create([
            'phone' => '081234567890',
            'education_level' => 'Mahasiswa',
            'avatar_path' => 'avatars/profile.jpg',
        ]);

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Batal')
            ->assertSee('data-bs-dismiss="modal"', false);

        Storage::disk('public')->assertExists('avatars/profile.jpg');
        $this->assertDatabaseHas('user_profiles', ['user_id' => $user->id, 'avatar_path' => 'avatars/profile.jpg']);
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

    public function test_user_can_change_password_with_current_password(): void
    {
        $user = User::factory()->create(['password' => 'password-lama']);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'phone' => '081234567890',
            'education_level' => 'Mahasiswa',
            'current_password' => 'password-lama',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();

        $this->assertTrue(password_verify('password-baru', $user->fresh()->password));
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => 'password-lama']);

        $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.update'), [
            'name' => $user->name,
            'phone' => '081234567890',
            'education_level' => 'Mahasiswa',
            'current_password' => 'password-salah',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertRedirect(route('profile.edit'))->assertSessionHasErrors('current_password');

        $this->assertTrue(password_verify('password-lama', $user->fresh()->password));
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
