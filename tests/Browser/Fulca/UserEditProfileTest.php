<?php

namespace Tests\Browser\Fulca;

use App\Enums\EducationLevel;
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
 * PBI-05 — Mengedit Profil, Ubah Password & Hapus Foto Pengguna (TC.UserProfile.008–014).
 */
#[Group('fulca')]
class UserEditProfileTest extends DuskTestCase
{
    use CleansAvatarUploads, CreatesTestUsers, DatabaseMigrations, MakesUploadFiles;

    private const NEW_PASSWORD = 'PasswordBaru123';

    /**
     * Case ID  : TC.UserProfile.008
     * Test Case: Pengguna mengubah data profil yang sudah tersimpan sebelumnya.
     */
    public function test_tc_user_profile_008_edit_profil(): void
    {
        $user = $this->makeUser(['name' => 'Fulca Liya Rufaidah'], $this->completeProfile());

        $this->browse(function (Browser $browser) use ($user) {
            // Langkah 1: form terisi data tersimpan.
            $browser->loginAs($user)
                ->visit('/profil')
                ->assertInputValue('@name-input', 'Fulca Liya Rufaidah')
                ->assertInputValue('@email-input', $user->email)
                ->assertInputValue('@birth-date-input', '2004-05-15')
                ->assertSelected('@gender-select', 'female')
                ->assertInputValue('@phone-input', '081234567890')
                ->assertInputValue('@address-input', 'Bandung')
                ->assertSelected('@education-select', 'Fresh Graduate')
                ->assertInputValue('@institution-input', 'Telkom University')
                ->assertInputValue('@graduation-year-input', '2026')
                ->assertInputValue('@linkedin-input', 'https://www.linkedin.com/in/fulca')
                ->assertInputValue('@bio-input', 'Bio lama Fulca.')
                ->shot('Fulca/TC.UserProfile.008-step1');

            // Langkah 2: ubah nama, No. HP, status.
            $browser->type('@name-input', 'Fulca Liya R.')
                ->type('@phone-input', '089876543210')
                ->select('@education-select', 'Mahasiswa')
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/profil')
                ->assertSee('Profil berhasil disimpan')
                ->assertSeeIn('@sidebar-name', 'Fulca Liya R.')
                ->assertSeeIn('.navbar-public', 'Fulca Liya R.')
                ->assertInputValue('@phone-input', '089876543210')
                ->assertSelected('@education-select', 'Mahasiswa')
                ->shot('Fulca/TC.UserProfile.008-step2');
        });

        $profile = UserProfile::find($user->id);
        $this->assertSame('Fulca Liya R.', $user->fresh()->name);
        $this->assertSame('089876543210', $profile->phone);
        $this->assertSame(EducationLevel::CollegeStudent, $profile->education_level);
        $this->assertSame('Telkom University', $profile->institution);
    }

    /**
     * Case ID  : TC.UserProfile.009
     * Test Case: Pengguna mengganti foto profil lama dengan foto baru.
     */
    public function test_tc_user_profile_009_ganti_foto_profil(): void
    {
        $user = $this->makeUser([], $this->completeProfile());
        $oldAvatar = $this->giveAvatar($user);

        $this->browse(function (Browser $browser) use ($user, $oldAvatar) {
            $browser->loginAs($user)
                ->visit('/profil')
                ->assertAttributeContains('img[dusk="sidebar-avatar"]', 'src', $oldAvatar)
                ->attach('@avatar-input', $this->imageFile('png', 120))
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/profil')
                ->assertSee('Profil berhasil disimpan');

            $newAvatar = UserProfile::find($user->id)->avatar_path;

            $this->assertNotNull($newAvatar);
            $this->assertNotSame($oldAvatar, $newAvatar);
            $this->assertTrue(Storage::disk('public')->exists($newAvatar), 'Foto baru harus tersimpan.');
            $this->assertFalse(Storage::disk('public')->exists($oldAvatar), 'Foto lama harus terhapus.');

            $browser->assertAttributeContains('img[dusk="sidebar-avatar"]', 'src', $newAvatar)
                ->assertAttributeContains('.navbar-public img.avatar', 'src', $newAvatar)
                ->shot('Fulca/TC.UserProfile.009-step1');
        });
    }

    /**
     * Case ID  : TC.UserProfile.010
     * Test Case: Pengguna mengubah password melalui bagian 'Ubah Password (opsional)' pada form profil.
     */
    public function test_tc_user_profile_010_ubah_password(): void
    {
        $user = $this->makeUser(['email' => 'fulca.password@abislulus.test'], $this->completeProfile());

        $this->browse(function (Browser $browser) use ($user) {
            // Langkah 1: isi bagian Ubah Password (opsional) pada form yang sama.
            $browser->loginAs($user)
                ->visit('/profil')
                ->assertSeeIn('@password-section', 'Ubah Password')
                ->assertSeeIn('@password-section', '(opsional)')
                ->assertPresent('@profile-form @current-password-input')
                ->type('@current-password-input', self::TEST_PASSWORD)
                ->type('@password-input', self::NEW_PASSWORD)
                ->type('@password-confirmation-input', self::NEW_PASSWORD)
                ->assertAttribute('@current-password-input', 'type', 'password')
                ->assertAttribute('@password-input', 'type', 'password')
                ->assertAttribute('@password-confirmation-input', 'type', 'password')
                ->scrollIntoView('@password-section')
                ->shot('Fulca/TC.UserProfile.010-step1');

            // Langkah 2: Simpan Perubahan.
            $browser->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/profil')
                ->assertSee('Profil berhasil disimpan')
                ->shot('Fulca/TC.UserProfile.010-step2');

            // Langkah 3: logout lalu login dengan password baru.
            $browser->click('.navbar-public .dropdown-toggle')
                ->waitFor('.navbar-public .dropdown-menu.show')
                ->clickAndWaitForReload('.navbar-public form[action$="/logout"] button')
                ->assertGuest()
                ->visit('/login')
                ->type('email', 'fulca.password@abislulus.test')
                ->type('password', self::NEW_PASSWORD)
                ->clickAndWaitForReload('form[action$="/login"] button[type="submit"]')
                ->assertPathIs('/')
                ->assertAuthenticatedAs($user)
                ->shot('Fulca/TC.UserProfile.010-step3');
        });

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
    }

    /**
     * Case ID  : TC.UserProfile.011
     * Test Case: Pengguna memasukkan password lama yang salah saat mengubah password.
     */
    public function test_tc_user_profile_011_password_lama_salah(): void
    {
        $user = $this->makeUser([], $this->completeProfile());

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profil')
                ->disableHtmlValidation()
                ->type('@current-password-input', 'passwordSalah99')
                ->type('@password-input', self::NEW_PASSWORD)
                ->type('@password-confirmation-input', self::NEW_PASSWORD)
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/profil')
                ->assertDontSee('Profil berhasil disimpan')
                ->assertAttributeContains('@current-password-input', 'class', 'is-invalid')
                ->assertSeeIn('@current-password-input + .invalid-feedback', 'Password lama tidak sesuai.')
                ->scrollIntoView('@current-password-input')
                ->shot('Fulca/TC.UserProfile.011-step1');
        });

        $this->assertPasswordUnchanged($user);
    }

    /**
     * Case ID  : TC.UserProfile.012
     * Test Case: Pengguna memasukkan password baru kurang dari 8 karakter atau konfirmasi tidak cocok.
     */
    public function test_tc_user_profile_012_validasi_password_baru(): void
    {
        $user = $this->makeUser([], $this->completeProfile());

        $cases = [
            'step1-kurang-8' => ['abc123', 'abc123'],
            'step2-konfirmasi-beda' => [self::NEW_PASSWORD, 'PasswordLain456'],
        ];

        $this->browse(function (Browser $browser) use ($user, $cases) {
            $browser->loginAs($user);

            foreach ($cases as $label => [$password, $confirmation]) {
                $browser->visit('/profil')
                    ->disableHtmlValidation()
                    ->type('@current-password-input', self::TEST_PASSWORD)
                    ->type('@password-input', $password)
                    ->type('@password-confirmation-input', $confirmation)
                    ->clickAndWaitForReload('@submit-button')
                    ->assertPathIs('/profil')
                    ->assertDontSee('Profil berhasil disimpan')
                    ->assertAttributeContains('@password-input', 'class', 'is-invalid')
                    ->assertVisible('@password-input + .invalid-feedback');

                $this->assertNotSame('', trim($browser->text('@password-input + .invalid-feedback')));
                $browser->scrollIntoView('@password-input')->shot("Fulca/TC.UserProfile.012-{$label}");
            }
        });

        $this->assertPasswordUnchanged($user);
    }

    /**
     * Case ID  : TC.UserProfile.013
     * Test Case: Pengguna menekan tombol Batal pada modal konfirmasi hapus foto.
     */
    public function test_tc_user_profile_013_batal_hapus_foto(): void
    {
        $user = $this->makeUser([], $this->completeProfile());
        $avatar = $this->giveAvatar($user);

        $this->browse(function (Browser $browser) use ($user, $avatar) {
            $browser->loginAs($user)->visit('/profil');

            foreach (['batal' => '@delete-avatar-cancel', 'tutup-x' => '@delete-avatar-close'] as $label => $closeButton) {
                // Langkah 1: buka modal hapus foto.
                $browser->click('@delete-avatar-button')
                    ->waitFor('#deleteAvatarModal.show')
                    ->pause(500) // tunggu animasi modal selesai
                    ->assertSeeIn('@delete-avatar-modal', 'Hapus foto profil?')
                    ->assertSeeIn('@delete-avatar-cancel', 'Batal')
                    ->assertSeeIn('@delete-avatar-confirm', 'Ya, Hapus Foto')
                    ->shot("Fulca/TC.UserProfile.013-step1-{$label}");

                // Langkah 2: tutup modal.
                $browser->click($closeButton)
                    ->waitUntilMissing('#deleteAvatarModal.show')
                    ->waitUntilMissing('.modal-backdrop')
                    ->assertPathIs('/profil')
                    ->assertAttributeContains('img[dusk="sidebar-avatar"]', 'src', $avatar)
                    ->assertVisible('@delete-avatar-button')
                    ->shot("Fulca/TC.UserProfile.013-step2-{$label}");
            }
        });

        $this->assertSame($avatar, UserProfile::find($user->id)->avatar_path);
        $this->assertTrue(Storage::disk('public')->exists($avatar));
    }

    /**
     * Case ID  : TC.UserProfile.014
     * Test Case: Pengguna menghapus foto profil melalui modal konfirmasi.
     */
    public function test_tc_user_profile_014_hapus_foto(): void
    {
        $user = $this->makeUser(['name' => 'Fulca Liya Rufaidah'], $this->completeProfile());
        $avatar = $this->giveAvatar($user);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profil')
                ->click('@delete-avatar-button')
                ->waitFor('#deleteAvatarModal.show')
                ->pause(500) // tunggu animasi modal selesai
                ->clickAndWaitForReload('@delete-avatar-confirm')
                ->assertPathIs('/profil')
                ->assertSee('Foto profil berhasil dihapus')
                ->assertPresent('span[dusk="sidebar-avatar"]')
                ->assertSeeIn('span[dusk="sidebar-avatar"]', 'F')
                ->assertMissing('img[dusk="sidebar-avatar"]')
                ->assertMissing('@delete-avatar-button')
                ->shot('Fulca/TC.UserProfile.014-step1');
        });

        $this->assertNull(UserProfile::find($user->id)->avatar_path);
        $this->assertFalse(Storage::disk('public')->exists($avatar), 'File foto harus terhapus dari storage.');
    }

    // --- Helper ---------------------------------------------------------------

    private function completeProfile(): array
    {
        return [
            'phone' => '081234567890',
            'education_level' => 'Fresh Graduate',
            'birth_date' => '2004-05-15',
            'gender' => 'female',
            'address' => 'Bandung',
            'institution' => 'Telkom University',
            'graduation_year' => 2026,
            'linkedin_url' => 'https://www.linkedin.com/in/fulca',
            'bio' => 'Bio lama Fulca.',
            'avatar_path' => null,
        ];
    }

    private function assertPasswordUnchanged(User $user): void
    {
        $password = $user->fresh()->password;

        $this->assertTrue(Hash::check(self::TEST_PASSWORD, $password), 'Password lama harus tetap berlaku.');
        $this->assertFalse(Hash::check(self::NEW_PASSWORD, $password));
    }
}
