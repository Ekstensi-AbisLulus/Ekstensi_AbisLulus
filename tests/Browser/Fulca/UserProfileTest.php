<?php

namespace Tests\Browser\Fulca;

use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Concerns\CreatesTestUsers;
use Tests\Browser\Concerns\MakesUploadFiles;
use Tests\DuskTestCase;

/**
 * PBI-04 — Melengkapi Profil Pengguna (TC.UserProfile.001–007).
 */
#[Group('fulca')]
class UserProfileTest extends DuskTestCase
{
    use CleansAvatarUploads, CreatesTestUsers, DatabaseMigrations, MakesUploadFiles;

    private const PHONE_ERROR = 'No. HP harus berisi 10–15 angka dan boleh diawali tanda +.';

    /**
     * Case ID  : TC.UserProfile.001
     * Test Case: Pengguna melengkapi seluruh data profil beserta foto profil setelah registrasi.
     */
    public function test_tc_user_profile_001_lengkapi_profil(): void
    {
        $user = $this->makeUser(['name' => 'Fulca Liya Rufaidah'], $this->emptyProfile());

        $this->browse(function (Browser $browser) use ($user) {
            // Langkah 1: avatar navbar → Profil Saya.
            $browser->loginAs($user)
                ->visit('/')
                ->click('.navbar-public .dropdown-toggle')
                ->waitFor('.navbar-public .dropdown-menu.show')
                ->assertSeeIn('.navbar-public .dropdown-menu', 'Profil Saya')
                ->clickAndWaitForReload('.navbar-public .dropdown-menu a[href$="/profil"]')
                ->assertPathIs('/profil')
                ->assertSeeIn('@sidebar-avatar', 'F')
                ->assertSeeIn('@sidebar-name', 'Fulca Liya Rufaidah')
                ->assertSeeIn('@nav-profile', 'Profil')
                ->assertSeeIn('@nav-learning', 'Aktivitas E-Learning')
                ->assertSeeIn('@nav-events', 'Aktivitas Kegiatan')
                ->assertSeeIn('@welcome', 'Selamat Datang, Fulca Liya Rufaidah')
                ->assertSeeIn('@profile-alert', 'Lengkapi profilmu untuk pengalaman yang lebih baik');

            foreach (['Nama lengkap', 'Email', 'Tanggal lahir', 'Jenis kelamin', 'No. HP/WhatsApp', 'Alamat domisili',
                'Status', 'Institusi', 'Tahun lulus', 'Link CV/LinkedIn', 'Bio', 'Foto profil'] as $label) {
                $browser->assertSeeIn('@profile-form', $label);
            }

            $browser->assertAttribute('@email-input', 'readonly', 'true')
                ->assertInputValue('@email-input', $user->email)
                ->assertAttribute('@birth-date-input', 'type', 'date')
                ->shot('Fulca/TC.UserProfile.001-step1');

            // Langkah 2: isi seluruh field.
            $this->setBirthDate($browser, '2004-05-15');
            $browser->select('@gender-select', 'female')
                ->type('@phone-input', '081234567890')
                ->type('@address-input', 'Jl. Telekomunikasi No. 1, Bandung')
                ->select('@education-select', 'Fresh Graduate')
                ->type('@institution-input', 'Telkom University')
                ->type('@graduation-year-input', '2026')
                ->type('@linkedin-input', 'https://www.linkedin.com/in/contoh')
                ->type('@bio-input', 'Fresh graduate yang tertarik di bidang data.')
                ->assertInputValue('@birth-date-input', '2004-05-15')
                ->assertSelected('@gender-select', 'female')
                ->assertInputValue('@phone-input', '081234567890')
                ->assertInputValue('@address-input', 'Jl. Telekomunikasi No. 1, Bandung')
                ->assertSelected('@education-select', 'Fresh Graduate')
                ->assertInputValue('@institution-input', 'Telkom University')
                ->assertInputValue('@graduation-year-input', '2026')
                ->assertInputValue('@linkedin-input', 'https://www.linkedin.com/in/contoh')
                ->assertInputValue('@bio-input', 'Fresh graduate yang tertarik di bidang data.')
                ->shot('Fulca/TC.UserProfile.001-step2');

            // Langkah 3: pilih foto.
            $browser->attach('@avatar-input', $this->imageFile('jpg'));
            $this->assertStringEndsWith('.jpg', $browser->value('@avatar-input'));
            $browser->shot('Fulca/TC.UserProfile.001-step3');

            // Langkah 4: simpan.
            $browser->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/profil')
                ->assertSee('Profil berhasil disimpan')
                ->assertMissing('@profile-alert')
                ->assertSeeIn('@sidebar-name', 'Fulca Liya Rufaidah');

            $profile = UserProfile::find($user->id);
            $this->assertNotNull($profile->avatar_path);
            $this->assertTrue(Storage::disk('public')->exists($profile->avatar_path), 'File foto harus tersimpan.');

            $browser->assertAttributeContains('img[dusk="sidebar-avatar"]', 'src', $profile->avatar_path)
                ->shot('Fulca/TC.UserProfile.001-step4');
        });

        $profile = UserProfile::find($user->id);
        $this->assertSame('Fulca Liya Rufaidah', $user->fresh()->name);
        $this->assertSame('2004-05-15', $profile->birth_date->format('Y-m-d'));
        $this->assertSame(Gender::Female, $profile->gender);
        $this->assertSame('081234567890', $profile->phone);
        $this->assertSame('Jl. Telekomunikasi No. 1, Bandung', $profile->address);
        $this->assertSame(EducationLevel::FreshGraduate, $profile->education_level);
        $this->assertSame('Telkom University', $profile->institution);
        $this->assertSame(2026, $profile->graduation_year);
        $this->assertSame('https://www.linkedin.com/in/contoh', $profile->linkedin_url);
        $this->assertSame('Fresh graduate yang tertarik di bidang data.', $profile->bio);
    }

    /**
     * Case ID  : TC.UserProfile.002
     * Test Case: Pengguna menyimpan profil dengan mengosongkan field wajib (Nama lengkap, No. HP/WhatsApp, Status).
     */
    public function test_tc_user_profile_002_field_wajib_kosong(): void
    {
        $user = $this->makeUser(['name' => 'Nama Asli'], ['phone' => '081111111111']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profil')
                ->disableHtmlValidation()
                ->clear('@name-input')
                ->clear('@phone-input')
                ->select('@education-select', '')
                ->assertInputValue('@name-input', '')
                ->assertInputValue('@phone-input', '')
                ->assertSelected('@education-select', '')
                ->shot('Fulca/TC.UserProfile.002-step1')
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/profil')
                ->assertDontSee('Profil berhasil disimpan');

            $this->assertFieldInvalid($browser, '@name-input');
            $this->assertFieldInvalid($browser, '@phone-input');
            $this->assertFieldInvalid($browser, '@education-select');
            $browser->shot('Fulca/TC.UserProfile.002-step2');
        });

        $this->assertSame('Nama Asli', $user->fresh()->name);
        $this->assertSame('081111111111', UserProfile::find($user->id)->phone);
    }

    /**
     * Case ID  : TC.UserProfile.003
     * Test Case: Pengguna memasukkan No. HP yang mengandung huruf atau jumlah digit di luar 10–15.
     */
    public function test_tc_user_profile_003_format_no_hp_tidak_valid(): void
    {
        $user = $this->makeUser([], ['phone' => '081111111111']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user);

            foreach (['08abc12345' => 'huruf', '12345' => 'kurang-digit'] as $phone => $label) {
                $browser->visit('/profil')
                    ->disableHtmlValidation()
                    ->type('@institution-input', 'Institusi Uji '.$label)
                    ->type('@address-input', 'Alamat Uji '.$label)
                    ->type('@phone-input', $phone)
                    ->assertInputValue('@phone-input', $phone)
                    ->shot("Fulca/TC.UserProfile.003-step1-{$label}")
                    ->clickAndWaitForReload('@submit-button')
                    ->assertPathIs('/profil')
                    ->assertDontSee('Profil berhasil disimpan')
                    ->assertSeeIn('@phone-input + .invalid-feedback', self::PHONE_ERROR)
                    ->assertAttributeContains('@phone-input', 'class', 'is-invalid')
                    ->assertInputValue('@institution-input', 'Institusi Uji '.$label)
                    ->assertInputValue('@address-input', 'Alamat Uji '.$label)
                    ->assertInputValue('@phone-input', $phone)
                    ->scrollIntoView('@phone-input')
                    ->shot("Fulca/TC.UserProfile.003-step2-{$label}");
            }
        });

        $this->assertSame('081111111111', UserProfile::find($user->id)->phone);
    }

    /**
     * Case ID  : TC.UserProfile.004
     * Test Case: Pengguna mengisi tanggal lahir di masa depan atau tahun lulus di luar rentang
     *            1990 – (tahun ini + 5).
     */
    public function test_tc_user_profile_004_validasi_tanggal_lahir_dan_tahun_lulus(): void
    {
        $user = $this->makeUser([], ['graduation_year' => 2024, 'birth_date' => '2004-05-15']);
        $maxYear = now()->year + 5;

        $this->browse(function (Browser $browser) use ($user, $maxYear) {
            $browser->loginAs($user);

            // Langkah 1: tahun lulus di luar rentang.
            foreach (['1985', '2040'] as $year) {
                $browser->visit('/profil')
                    ->disableHtmlValidation()
                    ->type('@graduation-year-input', $year)
                    ->clickAndWaitForReload('@submit-button')
                    ->assertPathIs('/profil')
                    ->assertDontSee('Profil berhasil disimpan')
                    ->assertSeeIn('@graduation-year-input + .invalid-feedback', "Tahun lulus harus antara 1990 dan {$maxYear}.")
                    ->assertAttributeContains('@graduation-year-input', 'class', 'is-invalid')
                    ->scrollIntoView('@graduation-year-input')
                    ->shot("Fulca/TC.UserProfile.004-step1-{$year}");
            }

            // Langkah 2: tanggal lahir hari ini.
            $browser->visit('/profil')->disableHtmlValidation();
            $this->setBirthDate($browser, now()->toDateString());

            $browser->assertInputValue('@birth-date-input', now()->toDateString())
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/profil')
                ->assertDontSee('Profil berhasil disimpan');

            $this->assertFieldInvalid($browser, '@birth-date-input');
            $browser->shot('Fulca/TC.UserProfile.004-step2');
        });

        $profile = UserProfile::find($user->id);
        $this->assertSame(2024, $profile->graduation_year);
        $this->assertSame('2004-05-15', $profile->birth_date->format('Y-m-d'));
    }

    /**
     * Case ID  : TC.UserProfile.005
     * Test Case: Pengguna memasukkan Link CV/LinkedIn yang bukan format URL.
     */
    public function test_tc_user_profile_005_link_linkedin_tidak_valid(): void
    {
        $user = $this->makeUser([], ['linkedin_url' => null]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/profil')
                ->disableHtmlValidation()
                ->type('@linkedin-input', 'linkedin-saya')
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/profil')
                ->assertDontSee('Profil berhasil disimpan')
                ->assertInputValue('@linkedin-input', 'linkedin-saya');

            $this->assertFieldInvalid($browser, '@linkedin-input');
            $browser->shot('Fulca/TC.UserProfile.005-step1');
        });

        $this->assertNull(UserProfile::find($user->id)->linkedin_url);
    }

    /**
     * Case ID  : TC.UserProfile.006
     * Test Case: Pengguna mengunggah foto profil dengan format selain JPG/JPEG/PNG atau ukuran > 2 MB.
     */
    public function test_tc_user_profile_006_validasi_unggah_foto(): void
    {
        $user = $this->makeUser();
        $oldAvatar = $this->giveAvatar($user);

        $cases = [
            'pdf' => $this->nonImageFile('pdf'),
            'lebih-2mb' => $this->largeImageFile(2),
        ];

        $this->browse(function (Browser $browser) use ($user, $cases, $oldAvatar) {
            $browser->loginAs($user);

            foreach ($cases as $label => $file) {
                $browser->visit('/profil')
                    ->attach('@avatar-input', $file)
                    ->clickAndWaitForReload('@submit-button')
                    ->assertPathIs('/profil')
                    ->assertDontSee('Profil berhasil disimpan')
                    ->assertAttributeContains('img[dusk="sidebar-avatar"]', 'src', $oldAvatar);

                $this->assertFieldInvalid($browser, '@avatar-input');
                $browser->shot("Fulca/TC.UserProfile.006-step1-{$label}");

                $this->assertSame($oldAvatar, UserProfile::find($user->id)->avatar_path, "Kasus: {$label}");
            }
        });

        $this->assertTrue(Storage::disk('public')->exists($oldAvatar), 'Foto lama harus tetap ada.');
    }

    /**
     * Case ID  : TC.UserProfile.007
     * Test Case: Guest, Admin, atau Super Admin mencoba mengakses halaman Profil Pengguna.
     */
    public function test_tc_user_profile_007_otorisasi_halaman_profil(): void
    {
        $admin = $this->makeAdmin();
        $superAdmin = $this->makeSuperAdmin();

        $this->browse(function (Browser $browser) use ($admin, $superAdmin) {
            $browser->visit('/profil')
                ->assertPathIs('/login')
                ->shot('Fulca/TC.UserProfile.007-step1-guest');

            foreach (['admin' => $admin, 'super-admin' => $superAdmin] as $label => $account) {
                $browser->loginAs($account)
                    ->visit('/profil')
                    ->assertPathIs('/profil')
                    ->assertSee('403')
                    ->assertSourceHas('Anda tidak memiliki akses ke halaman ini.')
                    ->assertDontSee('Simpan Perubahan')
                    ->shot("Fulca/TC.UserProfile.007-step2-{$label}");
            }
        });
    }

    // --- Helper ---------------------------------------------------------------

    /**
     * Isi input type="date" langsung (format YYYY-MM-DD) agar tidak bergantung locale browser.
     */
    private function setBirthDate(Browser $browser, string $date): void
    {
        $browser->script("document.querySelector('[dusk=\"birth-date-input\"]').value = '{$date}';");
    }

    /**
     * Field ditandai is-invalid dan pesan .invalid-feedback tampil di bawahnya (teks tidak dicocokkan
     * karena masih pesan bawaan Laravel berbahasa Inggris).
     */
    private function assertFieldInvalid(Browser $browser, string $field): void
    {
        $browser->scrollIntoView($field)
            ->assertAttributeContains($field, 'class', 'is-invalid')
            ->assertVisible("{$field} + .invalid-feedback");

        $this->assertNotSame('', trim($browser->text("{$field} + .invalid-feedback")), "Pesan error {$field} kosong.");
    }
}
