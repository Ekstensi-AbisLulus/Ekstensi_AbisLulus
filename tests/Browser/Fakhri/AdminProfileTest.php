<?php

namespace Tests\Browser\Fakhri;

use App\Models\UserProfile;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Concerns\CreatesTestUsers;
use Tests\Browser\Concerns\MakesUploadFiles;
use Tests\DuskTestCase;

/**
 * PBI-07 — Melengkapi Profil Admin (TC.AdminProfile.001–009).
 */
#[Group('fakhri')]
class AdminProfileTest extends DuskTestCase
{
    use CleansAvatarUploads, CreatesTestUsers, DatabaseMigrations, MakesUploadFiles;

    private const PHONE_ERROR = 'No. HP hanya boleh berisi angka (boleh diawali +) dan terdiri dari 10–15 digit.';

    /**
     * Case ID  : TC.AdminProfile.001
     * Test Case: Admin melengkapi data profil (No. HP/WhatsApp, institusi/komunitas, bio, dan foto profil)
     *            yang sebelumnya belum lengkap.
     */
    public function test_tc_admin_profile_001_lengkapi_profil(): void
    {
        $admin = $this->makeAdmin(['name' => 'Admin Fakhri'], $this->emptyProfile());

        $this->browse(function (Browser $browser) use ($admin) {
            // Langkah 1: klik menu Profil pada sidebar.
            $browser->loginAs($admin)
                ->visit('/admin/dashboard')
                ->clickAndWaitForReload('.panel-sidebar a[href$="/admin/profil"]')
                ->assertPathIs('/admin/profil')
                ->assertSee('Profil Admin')
                ->assertSee('Foto Profil')
                ->assertSee('Nama Lengkap')
                ->assertSee('Email')
                ->assertSee('No. HP/WhatsApp')
                ->assertSee('Institusi/Komunitas')
                ->assertSee('Bio')
                ->assertSeeIn('@profile-submit', 'Simpan Perubahan')
                ->assertAttribute('@email-input', 'readonly', 'true')
                ->assertInputValue('@email-input', $admin->email)
                ->assertSeeIn('@profile-alert', 'Lengkapi profilmu agar identitas pengelola jelas.')
                ->assertAttributeContains('@profile-alert', 'class', 'alert-warning');

            // Langkah 2: isi No. HP, institusi, bio.
            $browser->type('@phone-input', '081234567890')
                ->type('@institution-input', 'Komunitas Digital Talent')
                ->type('@bio-input', 'Pengelola program E-Learning AbisLulus.')
                ->assertInputValue('@phone-input', '081234567890')
                ->assertInputValue('@institution-input', 'Komunitas Digital Talent')
                ->assertInputValue('@bio-input', 'Pengelola program E-Learning AbisLulus.');

            // Langkah 3: pilih foto → pratinjau avatar berubah.
            $browser->attach('@avatar-input', $this->imageFile('png'))
                ->waitUsing(5, 100, fn () => $this->avatarPreviewSrc($browser) !== null
                    && str_starts_with($this->avatarPreviewSrc($browser), 'blob:'));
            $this->assertStringStartsWith('blob:', $this->avatarPreviewSrc($browser));

            // Langkah 4: simpan.
            $browser->clickAndWaitForReload('@profile-submit')
                ->assertPathIs('/admin/profil')
                ->assertSee('Profil berhasil disimpan')
                ->assertMissing('@profile-alert');

            $profile = UserProfile::find($admin->id);
            $this->assertSame('081234567890', $profile->phone);
            $this->assertSame('Komunitas Digital Talent', $profile->institution);
            $this->assertSame('Pengelola program E-Learning AbisLulus.', $profile->bio);
            $this->assertNotNull($profile->avatar_path);
            $this->assertTrue(Storage::disk('public')->exists($profile->avatar_path));

            $browser->assertAttributeContains('#avatarPreview', 'src', $profile->avatar_path)
                ->assertAttributeContains('.panel-sidebar img.avatar', 'src', $profile->avatar_path)
                ->shot('Fakhri/TC.AdminProfile.001-step4')
                ->visit('/')
                ->assertAttributeContains('.navbar-public img.avatar', 'src', $profile->avatar_path)
                ->shot('Fakhri/TC.AdminProfile.001-step4-navbar');
        });
    }

    /**
     * Case ID  : TC.AdminProfile.002
     * Test Case: Admin mengisi No. HP dengan format berspasi/tanda hubung.
     */
    public function test_tc_admin_profile_002_normalisasi_no_hp(): void
    {
        $admin = $this->makeAdmin([], $this->emptyProfile());

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin);

            foreach (['0812-3456-7890' => 'tanda-hubung', '0812 3456 7890' => 'spasi'] as $input => $label) {
                UserProfile::whereKey($admin->id)->update(['phone' => null]);

                $browser->visit('/admin/profil')
                    ->type('@phone-input', $input)
                    ->clickAndWaitForReload('@profile-submit')
                    ->assertSee('Profil berhasil disimpan')
                    ->assertInputValue('@phone-input', '081234567890')
                    ->shot("Fakhri/TC.AdminProfile.002-step1-{$label}");

                $this->assertSame('081234567890', UserProfile::find($admin->id)->phone, "Input: {$input}");
            }
        });
    }

    /**
     * Case ID  : TC.AdminProfile.003
     * Test Case: Admin menyimpan profil dengan mengosongkan field wajib (Nama Lengkap dan No. HP/WhatsApp).
     */
    public function test_tc_admin_profile_003_field_wajib_kosong(): void
    {
        $admin = $this->makeAdmin(['name' => 'Admin Asli'], ['phone' => '081111111111']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/profil')
                ->disableHtmlValidation()
                ->clear('@name-input')
                ->clear('@phone-input')
                ->assertInputValue('@name-input', '')
                ->assertInputValue('@phone-input', '')
                ->clickAndWaitForReload('@profile-submit')
                ->assertPathIs('/admin/profil')
                ->assertDontSee('Profil berhasil disimpan')
                ->assertSee('Nama lengkap wajib diisi.')
                ->assertSee('No. HP/WhatsApp wajib diisi.')
                ->assertAttributeContains('@name-input', 'class', 'is-invalid')
                ->assertAttributeContains('@phone-input', 'class', 'is-invalid')
                ->shot('Fakhri/TC.AdminProfile.003-step2');
        });

        $this->assertSame('Admin Asli', $admin->fresh()->name);
        $this->assertSame('081111111111', UserProfile::find($admin->id)->phone);
    }

    /**
     * Case ID  : TC.AdminProfile.004
     * Test Case: Admin memasukkan No. HP yang mengandung huruf atau jumlah digit di luar 10–15.
     */
    public function test_tc_admin_profile_004_format_no_hp_tidak_valid(): void
    {
        $admin = $this->makeAdmin([], ['phone' => '081111111111']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin);

            $cases = ['08abc12345' => 'huruf', '12345' => 'kurang-digit', '0812345678901234567' => 'lebih-digit'];

            foreach ($cases as $phone => $label) {
                $browser->visit('/admin/profil')
                    ->disableHtmlValidation()
                    ->type('@phone-input', $phone)
                    ->type('@institution-input', 'Komunitas Uji '.$label)
                    ->clickAndWaitForReload('@profile-submit')
                    ->assertPathIs('/admin/profil')
                    ->assertDontSee('Profil berhasil disimpan')
                    ->assertSee(self::PHONE_ERROR)
                    ->assertInputValue('@institution-input', 'Komunitas Uji '.$label)
                    ->assertInputValue('@phone-input', $phone)
                    ->shot("Fakhri/TC.AdminProfile.004-step2-{$label}");
            }
        });

        $this->assertSame('081111111111', UserProfile::find($admin->id)->phone);
    }

    /**
     * Case ID  : TC.AdminProfile.005
     * Test Case: Admin mengisi Nama Lengkap > 100 karakter, Institusi/Komunitas > 150 karakter,
     *            atau Bio > 1000 karakter.
     *
     * Atribut maxlength di form membatasi ketikan di browser, sehingga dihapus lewat script
     * (setara Inspect Element) untuk menguji validasi di server.
     */
    public function test_tc_admin_profile_005_batas_panjang_karakter(): void
    {
        $admin = $this->makeAdmin(['name' => 'Admin Asli'], ['institution' => 'Komunitas Asli', 'bio' => 'Bio asli.']);

        $cases = [
            'nama' => ['@name-input', 101, 'Nama lengkap maksimal 100 karakter.'],
            'institusi' => ['@institution-input', 151, 'Institusi/komunitas maksimal 150 karakter.'],
            'bio' => ['@bio-input', 1001, 'Bio maksimal 1000 karakter.'],
        ];

        $this->browse(function (Browser $browser) use ($admin, $cases) {
            $browser->loginAs($admin);

            foreach ($cases as $label => [$field, $length, $message]) {
                $browser->visit('/admin/profil')
                    ->disableHtmlValidation();

                $browser->script("document.querySelectorAll('[maxlength]').forEach(function (el) { el.removeAttribute('maxlength'); });");

                $browser->type($field, str_repeat('a', $length))
                    ->clickAndWaitForReload('@profile-submit')
                    ->assertPathIs('/admin/profil')
                    ->assertDontSee('Profil berhasil disimpan')
                    ->assertSee($message)
                    ->shot("Fakhri/TC.AdminProfile.005-step1-{$label}");
            }
        });

        $profile = UserProfile::find($admin->id);
        $this->assertSame('Admin Asli', $admin->fresh()->name);
        $this->assertSame('Komunitas Asli', $profile->institution);
        $this->assertSame('Bio asli.', $profile->bio);
    }

    /**
     * Case ID  : TC.AdminProfile.006
     * Test Case: Admin mengunggah foto profil dengan format selain JPG/JPEG/PNG atau ukuran > 2 MB.
     */
    public function test_tc_admin_profile_006_validasi_unggah_foto(): void
    {
        $admin = $this->makeAdmin();
        $oldAvatar = $this->giveAvatar($admin);

        $cases = [
            'pdf' => [$this->nonImageFile('pdf'), 'Foto profil harus berupa gambar.'],
            'gif' => [$this->imageFile('gif'), 'Foto profil harus berformat JPG, JPEG, atau PNG.'],
            'lebih-2mb' => [$this->largeImageFile(2), 'Ukuran foto profil maksimal 2 MB.'],
        ];

        $this->browse(function (Browser $browser) use ($admin, $cases, $oldAvatar) {
            $browser->loginAs($admin);

            foreach ($cases as $label => [$file, $message]) {
                $browser->visit('/admin/profil')
                    ->attach('@avatar-input', $file)
                    ->clickAndWaitForReload('@profile-submit')
                    ->assertPathIs('/admin/profil')
                    ->shot("Fakhri/TC.AdminProfile.006-step2-{$label}")
                    ->assertDontSee('Profil berhasil disimpan')
                    ->assertSee($message)
                    ->assertAttributeContains('#avatarPreview', 'src', $oldAvatar);

                $this->assertSame($oldAvatar, UserProfile::find($admin->id)->avatar_path, "Kasus: {$label}");
            }
        });

        $this->assertTrue(Storage::disk('public')->exists($oldAvatar));
    }

    /**
     * Case ID  : TC.AdminProfile.007
     * Test Case: Admin mencoba mengubah alamat email dari form Profil Admin.
     */
    public function test_tc_admin_profile_007_email_read_only(): void
    {
        $admin = $this->makeAdmin(['email' => 'admin.asli@abislulus.test']);

        $this->browse(function (Browser $browser) use ($admin) {
            // Langkah 1: field email tidak bisa diedit.
            $browser->loginAs($admin)
                ->visit('/admin/profil')
                ->assertAttribute('@email-input', 'readonly', 'true')
                ->click('@email-input');

            $browser->driver->getKeyboard()->sendKeys('tambahan');

            $browser->assertInputValue('@email-input', 'admin.asli@abislulus.test')
                ->shot('Fakhri/TC.AdminProfile.007-step1');

            // Langkah 2: ubah via "Inspect Element" lalu simpan.
            $browser->script(
                "var el = document.querySelector('[dusk=\"email-input\"]');"
                ."el.removeAttribute('readonly'); el.setAttribute('name', 'email'); el.value = 'hacker@abislulus.test';"
            );

            $browser->assertInputValue('@email-input', 'hacker@abislulus.test')
                ->clickAndWaitForReload('@profile-submit')
                ->assertSee('Profil berhasil disimpan')
                ->assertInputValue('@email-input', 'admin.asli@abislulus.test')
                ->shot('Fakhri/TC.AdminProfile.007-step2');
        });

        $this->assertSame('admin.asli@abislulus.test', $admin->fresh()->email);
        $this->assertDatabaseMissing('users', ['email' => 'hacker@abislulus.test']);
    }

    /**
     * Case ID  : TC.AdminProfile.008
     * Test Case: Guest (belum login) atau User biasa (role: user) mencoba mengakses halaman Profil Admin.
     */
    public function test_tc_admin_profile_008_otorisasi_halaman_profil(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/admin/profil')
                ->assertPathIs('/login')
                ->shot('Fakhri/TC.AdminProfile.008-step1-guest');

            $browser->loginAs($user)
                ->visit('/admin/profil')
                ->assertPathIs('/admin/profil')
                ->assertSee('403')
                ->assertSourceHas('Anda tidak memiliki akses ke halaman ini.')
                ->assertDontSee('Simpan Perubahan')
                ->shot('Fakhri/TC.AdminProfile.008-step2-user');
        });
    }

    /**
     * Case ID  : TC.AdminProfile.009
     * Test Case: Super Admin membuka dan menyimpan data profilnya melalui halaman Profil.
     */
    public function test_tc_admin_profile_009_super_admin_kelola_profil(): void
    {
        $superAdmin = $this->makeSuperAdmin([], $this->emptyProfile());

        $this->browse(function (Browser $browser) use ($superAdmin) {
            $browser->loginAs($superAdmin)
                ->visit('/super-admin/dashboard')
                ->clickAndWaitForReload('.panel-sidebar a[href$="/admin/profil"]')
                ->assertPathIs('/admin/profil')
                ->type('@phone-input', '081298765432')
                ->clickAndWaitForReload('@profile-submit')
                ->assertSee('Profil berhasil disimpan')
                ->shot('Fakhri/TC.AdminProfile.009-step1');
        });

        $this->assertSame('081298765432', UserProfile::find($superAdmin->id)->phone);
    }

    private function avatarPreviewSrc(Browser $browser): ?string
    {
        return $browser->script(
            "var el = document.querySelector('#avatarPreview'); return el && el.tagName === 'IMG' ? el.src : null;"
        )[0] ?? null;
    }
}
