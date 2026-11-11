<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * PBI-08 Mengedit profil admin (PRD §7 F3).
 */
class EditProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    private function admin(array $profile = []): User
    {
        return User::factory()->admin()->withProfile(array_merge([
            'phone' => '081234567890',
            'institution' => 'Komunitas Lama',
            'bio' => 'Bio lama.',
        ], $profile))->create(['name' => 'Nama Lama', 'password' => 'password-lama']);
    }

    // --- Edit profil ---

    public function test_form_is_prefilled_with_existing_data(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/admin/profil')
            ->assertOk()
            ->assertSee('value="Nama Lama"', false)
            ->assertSee('value="081234567890"', false)
            ->assertSee('value="Komunitas Lama"', false)
            ->assertSee('Bio lama.');
    }

    public function test_old_input_takes_precedence_after_validation_error(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from('/admin/profil')
            ->followingRedirects()
            ->put('/admin/profil', [
                'name' => 'Nama Baru Belum Tersimpan',
                'phone' => 'abc',
                'institution' => 'Komunitas Baru',
            ])
            ->assertSee('value="Nama Baru Belum Tersimpan"', false)
            ->assertSee('value="Komunitas Baru"', false)
            ->assertDontSee('value="Nama Lama"', false);

        $this->assertSame('Nama Lama', $admin->fresh()->name);
    }

    public function test_edited_profile_is_saved_and_shown_in_sidebar(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from('/admin/profil')
            ->followingRedirects()
            ->put('/admin/profil', [
                'name' => 'Nama Terbaru',
                'phone' => '+6289876543210',
                'institution' => 'Komunitas Baru',
                'bio' => 'Bio baru.',
            ])
            ->assertOk()
            ->assertSee('Profil berhasil disimpan')
            // Sidebar panel + form profil langsung memakai nama baru.
            ->assertSeeInOrder(['Nama Terbaru', 'Dashboard', 'Profil Admin', 'Nama Terbaru'])
            ->assertDontSee('Nama Lama');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'name' => 'Nama Terbaru']);
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $admin->id,
            'phone' => '+6289876543210',
            'institution' => 'Komunitas Baru',
            'bio' => 'Bio baru.',
        ]);

        $this->actingAs($admin->fresh())->get('/')->assertSee('Nama Terbaru');
    }

    public function test_admin_only_edits_own_profile_even_if_other_id_is_sent(): void
    {
        $admin = $this->admin();
        $other = User::factory()->admin()->withProfile(['phone' => '081111111111'])->create(['name' => 'Admin Lain']);

        $this->actingAs($admin)
            ->put('/admin/profil', [
                'user_id' => $other->id,
                'id' => $other->id,
                'name' => 'Diubah',
                'phone' => '082222222222',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Admin Lain', $other->fresh()->name);
        $this->assertSame('081111111111', $other->profile()->first()->phone);
        $this->assertSame('Diubah', $admin->fresh()->name);
    }

    // --- Ubah password ---

    public function test_password_card_is_shown(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/profil')
            ->assertSee('Ubah Password')
            ->assertSee('name="current_password"', false)
            ->assertSee(route('admin.profile.password.update'), false);
    }

    public function test_admin_can_change_password(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from('/admin/profil')
            ->put('/admin/profil/password', [
                'current_password' => 'password-lama',
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertRedirect('/admin/profil')
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Password berhasil diubah');

        $this->assertTrue(Hash::check('password-baru', $admin->fresh()->password));

        // Bisa login dengan password baru.
        $this->post('/logout');
        $this->post('/login', ['email' => $admin->email, 'password' => 'password-baru'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from('/admin/profil')
            ->put('/admin/profil/password', [
                'current_password' => 'salah-total',
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertRedirect('/admin/profil')
            ->assertSessionHasErrorsIn('updatePassword', ['current_password' => 'Password lama tidak sesuai.'])
            ->assertSessionMissing('success');

        $this->assertTrue(Hash::check('password-lama', $admin->fresh()->password));
    }

    public function test_new_password_must_be_valid(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put('/admin/profil/password', [
                'current_password' => 'password-lama',
                'password' => 'pendek',
                'password_confirmation' => 'pendek',
            ])
            ->assertSessionHasErrorsIn('updatePassword', ['password' => 'Password baru minimal 8 karakter.']);

        $this->actingAs($admin)
            ->put('/admin/profil/password', [
                'current_password' => 'password-lama',
                'password' => 'password-baru',
                'password_confirmation' => 'beda-sekali',
            ])
            ->assertSessionHasErrorsIn('updatePassword', ['password' => 'Konfirmasi password baru tidak cocok.']);

        $this->actingAs($admin)
            ->put('/admin/profil/password', [
                'current_password' => 'password-lama',
                'password' => 'password-lama',
                'password_confirmation' => 'password-lama',
            ])
            ->assertSessionHasErrorsIn('updatePassword', ['password']);

        $this->assertTrue(Hash::check('password-lama', $admin->fresh()->password));
    }

    public function test_password_errors_are_shown_in_password_card(): void
    {
        $this->actingAs($this->admin())
            ->from('/admin/profil')
            ->followingRedirects()
            ->put('/admin/profil/password', ['current_password' => 'salah', 'password' => 'password-baru', 'password_confirmation' => 'password-baru'])
            ->assertSeeInOrder(['Ubah Password', 'Password lama tidak sesuai.']);
    }

    // --- Hapus foto ---

    public function test_delete_button_and_modal_only_shown_when_photo_exists(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/profil')
            ->assertDontSee('deleteAvatarModal');

        $this->actingAs($this->admin(['avatar_path' => 'avatars/foto.jpg']))
            ->get('/admin/profil')
            ->assertSee('Hapus Foto')
            ->assertSee('id="deleteAvatarModal"', false)
            ->assertSee('Batal')
            ->assertSee(route('admin.profile.avatar.destroy'), false);
    }

    public function test_delete_photo_removes_file_and_restores_default_avatar(): void
    {
        Storage::disk('public')->put('avatars/foto.jpg', 'isi-foto');
        $admin = $this->admin(['avatar_path' => 'avatars/foto.jpg']);

        $this->actingAs($admin)
            ->from('/admin/profil')
            ->delete('/admin/profil/foto')
            ->assertRedirect('/admin/profil')
            ->assertSessionHas('success', 'Foto profil berhasil dihapus');

        Storage::disk('public')->assertMissing('avatars/foto.jpg');
        $this->assertNull($admin->profile()->first()->avatar_path);

        $this->actingAs($admin->fresh())
            ->get('/admin/profil')
            ->assertDontSee('/storage/avatars/foto.jpg', false)
            ->assertDontSee('deleteAvatarModal');
    }

    public function test_delete_photo_without_photo_shows_error(): void
    {
        $this->actingAs($this->admin())
            ->from('/admin/profil')
            ->delete('/admin/profil/foto')
            ->assertRedirect('/admin/profil')
            ->assertSessionHas('error');
    }

    // --- Akses ---

    public function test_regular_user_and_guest_cannot_use_new_endpoints(): void
    {
        $user = User::factory()->withProfile()->create(['password' => 'password-lama']);

        $this->actingAs($user)->put('/admin/profil/password', [
            'current_password' => 'password-lama', 'password' => 'password-baru', 'password_confirmation' => 'password-baru',
        ])->assertForbidden();
        $this->actingAs($user)->delete('/admin/profil/foto')->assertForbidden();
        $this->assertTrue(Hash::check('password-lama', $user->fresh()->password));

        $this->post('/logout');
        $this->put('/admin/profil/password')->assertRedirect('/login');
        $this->delete('/admin/profil/foto')->assertRedirect('/login');
    }
}
