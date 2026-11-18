<?php

namespace Tests\Browser\Fakhri;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Concerns\CreatesTestUsers;
use Tests\Browser\Concerns\MakesUploadFiles;
use Tests\DuskTestCase;

/**
 * PBI-08 — Mengedit Profil, Ubah Password & Hapus Foto Admin (TC.AdminProfile.010–018).
 */
#[Group('fakhri')]
class AdminEditProfileTest extends DuskTestCase
{
    use CleansAvatarUploads, CreatesTestUsers, DatabaseMigrations, MakesUploadFiles;

    private const NEW_PASSWORD = 'PasswordBaru123';

    /**
     * Case ID  : TC.AdminProfile.010
     * Test Case: Admin mengubah data profil yang sudah tersimpan sebelumnya.
     */
    public function test_tc_admin_profile_010_edit_profil(): void
    {
        $admin = $this->makeAdmin(['name' => 'Fakhri Abdullah'], [
            'phone' => '081234567890',
            'institution' => 'Komunitas Lama',
            'bio' => 'Bio lama admin.',
        ]);
        $avatar = $this->giveAvatar($admin);

        $this->browse(function (Browser $browser) use ($admin, $avatar) {
            // Langkah 1: form terisi data lama.
            $browser->loginAs($admin)
                ->visit('/admin/profil')
                ->assertInputValue('@name-input', 'Fakhri Abdullah')
                ->assertInputValue('@phone-input', '081234567890')
                ->assertInputValue('@institution-input', 'Komunitas Lama')
                ->assertInputValue('@bio-input', 'Bio lama admin.')
                ->assertAttributeContains('#avatarPreview', 'src', $avatar)
                ->shot('Fakhri/TC.AdminProfile.010-step1');

            // Langkah 2: ubah nama & No. HP.
            $browser->type('@name-input', 'Fakhri Abdullah Updated')
                ->type('@phone-input', '089876543210')
                ->clickAndWaitForReload('@profile-submit')
                ->assertSee('Profil berhasil disimpan')
                ->assertInputValue('@name-input', 'Fakhri Abdullah Updated')
                ->assertSeeIn('.panel-sidebar', 'Fakhri Abdullah Updated')
                ->shot('Fakhri/TC.AdminProfile.010-step2')
                ->visit('/')
                ->assertSeeIn('.navbar-public', 'Fakhri Abdullah Updated')
                ->shot('Fakhri/TC.AdminProfile.010-step2-navbar');
        });

        $this->assertSame('Fakhri Abdullah Updated', $admin->fresh()->name);
        $this->assertSame('089876543210', UserProfile::find($admin->id)->phone);
    }

    /**
     * Case ID  : TC.AdminProfile.011
     * Test Case: Admin menyimpan perubahan dengan No. HP tidak valid setelah mengubah field lain.
     */
    public function test_tc_admin_profile_011_old_input_setelah_validasi_gagal(): void
    {
        $admin = $this->makeAdmin([], [
            'phone' => '081234567890',
            'institution' => 'Komunitas Lama',
            'bio' => 'Bio lama admin.',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/profil')
                ->disableHtmlValidation()
                ->type('@institution-input', 'Komunitas Baru Diketik')
                ->type('@bio-input', 'Bio baru yang belum tersimpan.')
                ->type('@phone-input', '08abc')
                ->clickAndWaitForReload('@profile-submit')
                ->assertDontSee('Profil berhasil disimpan')
                ->assertSee('No. HP hanya boleh berisi angka (boleh diawali +) dan terdiri dari 10–15 digit.')
                ->assertInputValue('@institution-input', 'Komunitas Baru Diketik')
                ->assertInputValue('@bio-input', 'Bio baru yang belum tersimpan.')
                ->shot('Fakhri/TC.AdminProfile.011-step1');
        });

        $profile = UserProfile::find($admin->id);
        $this->assertSame('Komunitas Lama', $profile->institution);
        $this->assertSame('Bio lama admin.', $profile->bio);
    }

    /**
     * Case ID  : TC.AdminProfile.012
     * Test Case: Admin mengganti foto profil lama dengan foto baru.
     */
    public function test_tc_admin_profile_012_ganti_foto_profil(): void
    {
        $admin = $this->makeAdmin();
        $oldAvatar = $this->giveAvatar($admin);

        $this->browse(function (Browser $browser) use ($admin, $oldAvatar) {
            $browser->loginAs($admin)
                ->visit('/admin/profil')
                ->assertAttributeContains('#avatarPreview', 'src', $oldAvatar)
                ->attach('@avatar-input', $this->imageFile('jpg', 100))
                ->clickAndWaitForReload('@profile-submit')
                ->assertSee('Profil berhasil disimpan');

            $newAvatar = UserProfile::find($admin->id)->avatar_path;

            $this->assertNotNull($newAvatar);
            $this->assertNotSame($oldAvatar, $newAvatar);
            $this->assertTrue(Storage::disk('public')->exists($newAvatar), 'Foto baru harus tersimpan.');
            $this->assertFalse(Storage::disk('public')->exists($oldAvatar), 'Foto lama harus terhapus.');

            $browser->assertAttributeContains('#avatarPreview', 'src', $newAvatar)
                ->assertAttributeContains('.panel-sidebar img.avatar', 'src', $newAvatar)
                ->shot('Fakhri/TC.AdminProfile.012-step1')
                ->visit('/')
                ->assertAttributeContains('.navbar-public img.avatar', 'src', $newAvatar)
                ->shot('Fakhri/TC.AdminProfile.012-step1-navbar');
        });
    }

    /**
     * Case ID  : TC.AdminProfile.013
     * Test Case: Admin mengubah password melalui card 'Ubah Password' dengan password lama yang benar.
     */
    public function test_tc_admin_profile_013_ubah_password(): void
    {
        $admin = $this->makeAdmin(['email' => 'admin.password@abislulus.test']);

        $this->browse(function (Browser $browser) use ($admin) {
            // Langkah 1: isi field password (tersamar) lalu tampilkan lewat ikon mata.
            $browser->loginAs($admin)
                ->visit('/admin/profil')
                ->type('@current_password-input', self::TEST_PASSWORD)
                ->type('@password-input', self::NEW_PASSWORD)
                ->type('@password_confirmation-input', self::NEW_PASSWORD)
                ->assertAttribute('@current_password-input', 'type', 'password')
                ->assertAttribute('@password-input', 'type', 'password')
                ->assertAttribute('@password_confirmation-input', 'type', 'password')
                ->click('@toggle-password')
                ->assertAttribute('@password-input', 'type', 'text')
                ->assertInputValue('@password-input', self::NEW_PASSWORD)
                ->shot('Fakhri/TC.AdminProfile.013-step1');

            // Langkah 2: simpan password.
            $browser->clickAndWaitForReload('@password-submit')
                ->assertSee('Password berhasil diubah')
                ->shot('Fakhri/TC.AdminProfile.013-step2');

            // Langkah 3: logout lalu login ulang dengan password baru.
            $browser->clickAndWaitForReload('.panel-sidebar form[action$="/logout"] button')
                ->assertGuest()
                ->visit('/login')
                ->type('email', 'admin.password@abislulus.test')
                ->type('password', self::NEW_PASSWORD)
                ->clickAndWaitForReload('form[action$="/login"] button[type="submit"]')
                ->assertPathIs('/admin/dashboard')
                ->assertAuthenticatedAs($admin)
                ->shot('Fakhri/TC.AdminProfile.013-step3');
        });

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $admin->fresh()->password));
    }

    /**
     * Case ID  : TC.AdminProfile.014
     * Test Case: Admin memasukkan password lama yang salah saat mengubah password.
     */
    public function test_tc_admin_profile_014_password_lama_salah(): void
    {
        $admin = $this->makeAdmin();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/profil')
                ->type('@current_password-input', 'passwordSalah99')
                ->type('@password-input', self::NEW_PASSWORD)
                ->type('@password_confirmation-input', self::NEW_PASSWORD)
                ->clickAndWaitForReload('@password-submit')
                ->assertDontSee('Password berhasil diubah')
                ->assertSeeIn('@password-card', 'Password lama tidak sesuai.')
                ->assertDontSeeIn('@profile-form', 'Password lama tidak sesuai.')
                ->shot('Fakhri/TC.AdminProfile.014-step1');
        });

        $this->assertPasswordUnchanged($admin);
    }

    /**
     * Case ID  : TC.AdminProfile.015
     * Test Case: Admin memasukkan password baru kurang dari 8 karakter, konfirmasi tidak cocok,
     *            atau sama dengan password lama.
     */
    public function test_tc_admin_profile_015_validasi_password_baru(): void
    {
        $admin = $this->makeAdmin();

        $cases = [
            'step1-kurang-8' => ['abc123', 'abc123', 'Password baru minimal 8 karakter.'],
            'step2-konfirmasi-beda' => [self::NEW_PASSWORD, 'PasswordLain456', 'Konfirmasi password baru tidak cocok.'],
            'step3-sama-password-lama' => [self::TEST_PASSWORD, self::TEST_PASSWORD, 'Password baru harus berbeda dari password lama.'],
        ];

        $this->browse(function (Browser $browser) use ($admin, $cases) {
            $browser->loginAs($admin);

            foreach ($cases as $label => [$password, $confirmation, $message]) {
                $browser->visit('/admin/profil')
                    ->disableHtmlValidation()
                    ->type('@current_password-input', self::TEST_PASSWORD)
                    ->type('@password-input', $password)
                    ->type('@password_confirmation-input', $confirmation)
                    ->clickAndWaitForReload('@password-submit')
                    ->assertDontSee('Password berhasil diubah')
                    ->assertSeeIn('@password-card', $message)
                    ->shot("Fakhri/TC.AdminProfile.015-{$label}");
            }
        });

        $this->assertPasswordUnchanged($admin);
    }

    /**
     * Case ID  : TC.AdminProfile.016
     * Test Case: Admin menekan tombol Batal pada modal konfirmasi hapus foto.
     */
    public function test_tc_admin_profile_016_batal_hapus_foto(): void
    {
        $admin = $this->makeAdmin();
        $avatar = $this->giveAvatar($admin);

        $this->browse(function (Browser $browser) use ($admin, $avatar) {
            $browser->loginAs($admin)->visit('/admin/profil');

            foreach (['batal' => '@delete-avatar-cancel', 'tutup-x' => '@delete-avatar-close'] as $label => $closeButton) {
                // Langkah 1: buka modal konfirmasi.
                $browser->click('@delete-avatar-button')
                    ->waitFor('#deleteAvatarModal.show')
                    ->pause(500) // tunggu animasi modal selesai
                    ->assertSeeIn('@delete-avatar-modal', 'Hapus foto profil?')
                    ->assertSeeIn('@delete-avatar-modal', 'Foto profilmu akan dihapus dan diganti dengan avatar default (inisial nama).')
                    ->assertSeeIn('@delete-avatar-cancel', 'Batal')
                    ->assertSeeIn('@delete-avatar-confirm', 'Hapus Foto')
                    ->shot("Fakhri/TC.AdminProfile.016-step1-{$label}");

                // Langkah 2: tutup modal.
                $browser->click($closeButton)
                    ->waitUntilMissing('#deleteAvatarModal.show')
                    ->waitUntilMissing('.modal-backdrop')
                    ->assertPathIs('/admin/profil')
                    ->assertAttributeContains('#avatarPreview', 'src', $avatar)
                    ->assertVisible('@delete-avatar-button')
                    ->shot("Fakhri/TC.AdminProfile.016-step2-{$label}");
            }
        });

        $this->assertSame($avatar, UserProfile::find($admin->id)->avatar_path);
        $this->assertTrue(Storage::disk('public')->exists($avatar));
    }

    /**
     * Case ID  : TC.AdminProfile.017
     * Test Case: Admin menghapus foto profil melalui modal konfirmasi.
     */
    public function test_tc_admin_profile_017_hapus_foto(): void
    {
        $admin = $this->makeAdmin(['name' => 'Zahra Admin']);
        $avatar = $this->giveAvatar($admin);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/profil')
                ->click('@delete-avatar-button')
                ->waitFor('#deleteAvatarModal.show')
                    ->pause(500) // tunggu animasi modal selesai
                ->clickAndWaitForReload('@delete-avatar-confirm')
                ->assertPathIs('/admin/profil')
                ->assertSee('Foto profil berhasil dihapus')
                ->assertPresent('span#avatarPreview')
                ->assertSeeIn('span#avatarPreview', 'Z')
                ->assertMissing('@delete-avatar-button')
                ->assertMissing('.panel-sidebar img.avatar')
                ->shot('Fakhri/TC.AdminProfile.017-step1');
        });

        $this->assertNull(UserProfile::find($admin->id)->avatar_path);
        $this->assertFalse(Storage::disk('public')->exists($avatar), 'File foto harus terhapus dari storage.');
    }

    /**
     * Case ID  : TC.AdminProfile.018
     * Test Case: Admin mencoba mengubah profil admin lain dengan menyisipkan ID pengguna lain pada request.
     */
    public function test_tc_admin_profile_018_hanya_ubah_profil_sendiri(): void
    {
        $adminA = $this->makeAdmin(['name' => 'Admin A'], ['phone' => '081111111111', 'institution' => 'Komunitas A']);
        $adminB = $this->makeAdmin(['name' => 'Admin B'], ['phone' => '082222222222', 'institution' => 'Komunitas B', 'bio' => 'Bio B.']);

        $this->browse(function (Browser $browser) use ($adminA, $adminB) {
            $browser->loginAs($adminA)->visit('/admin/profil');

            $browser->script(
                "var input = document.createElement('input');"
                ."input.type = 'hidden'; input.name = 'user_id'; input.value = '{$adminB->id}';"
                ."document.querySelector('[dusk=\"profile-form\"]').appendChild(input);"
            );

            $browser->assertPresent('[dusk="profile-form"] input[name="user_id"][value="'.$adminB->id.'"]')
                ->type('@name-input', 'Nama Disisipkan')
                ->type('@phone-input', '083333333333')
                ->type('@institution-input', 'Komunitas Disisipkan')
                ->clickAndWaitForReload('@profile-submit')
                ->assertSee('Profil berhasil disimpan')
                ->assertSeeIn('.panel-sidebar', 'Nama Disisipkan')
                ->shot('Fakhri/TC.AdminProfile.018-step1');
        });

        $this->assertSame('Nama Disisipkan', $adminA->fresh()->name);
        $this->assertSame('083333333333', UserProfile::find($adminA->id)->phone);

        $profileB = UserProfile::find($adminB->id);
        $this->assertSame('Admin B', $adminB->fresh()->name);
        $this->assertSame('082222222222', $profileB->phone);
        $this->assertSame('Komunitas B', $profileB->institution);
        $this->assertSame('Bio B.', $profileB->bio);
    }

    private function assertPasswordUnchanged(User $user): void
    {
        $password = $user->fresh()->password;

        $this->assertTrue(Hash::check(self::TEST_PASSWORD, $password), 'Password lama harus tetap berlaku.');
        $this->assertFalse(Hash::check(self::NEW_PASSWORD, $password));
    }
}
