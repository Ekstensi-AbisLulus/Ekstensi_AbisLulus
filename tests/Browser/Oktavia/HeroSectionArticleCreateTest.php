<?php

namespace Tests\Browser\Oktavia;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\HeroSection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Concerns\CreatesTestUsers;
use Tests\Browser\Concerns\MakesUploadFiles;
use Tests\DuskTestCase;

/**
 * PBI-22 — Tambah Hero Section & Artikel (TC.LandingPage.001–010).
 */
#[Group('oktavia')]
class HeroSectionArticleCreateTest extends DuskTestCase
{
    use CleansLandingUploads, CreatesTestUsers, DatabaseMigrations, MakesUploadFiles;

    private const HERO_TITLE = 'Siap Melangkah Setelah Lulus';

    private const HERO_SUBTITLE = 'Belajar gratis lewat E-Learning dan ikuti Career Mentoring bersama praktisi.';

    /**
     * Case ID  : TC.LandingPage.001
     * Test Case: Super Admin menambahkan banner hero section baru dengan gambar, subjudul, dan target URL tombol.
     */
    public function test_tc_landing_page_001_tambah_hero_section(): void
    {
        $superAdmin = $this->makeSuperAdmin();

        $this->browse(function (Browser $browser) use ($superAdmin) {
            // Langkah 1: klik "+ Tambah Hero".
            $browser->loginAs($superAdmin)
                ->visit('/super-admin/hero-sections')
                ->assertSeeIn('@create-button', '+ Tambah Hero')
                ->clickAndWaitForReload('@create-button')
                ->assertPathIs('/super-admin/hero-sections/create')
                ->assertSee('Buat Banner Hero Baru')
                ->assertSeeIn('@hero-form', 'Judul Hero')
                ->assertSeeIn('@hero-form', 'Subjudul (Opsional)')
                ->assertSeeIn('@hero-form', 'Target URL Tombol (Opsional)')
                ->assertSeeIn('@hero-form', 'Gambar Hero')
                ->assertSeeIn('@hero-form', 'Aktif')
                ->assertVisible('@title-input')
                ->assertVisible('@subtitle-input')
                ->assertVisible('@target-url-input')
                ->assertVisible('@image-input')
                ->assertPresent('@active-switch')
                ->shot('Oktavia/TC.LandingPage.001-step1');

            // Langkah 2: isi teks.
            $browser->type('@title-input', self::HERO_TITLE)
                ->type('@subtitle-input', self::HERO_SUBTITLE)
                ->type('@target-url-input', '/e-learning')
                ->assertInputValue('@title-input', self::HERO_TITLE)
                ->assertInputValue('@subtitle-input', self::HERO_SUBTITLE)
                ->assertInputValue('@target-url-input', '/e-learning')
                ->shot('Oktavia/TC.LandingPage.001-step2');

            // Langkah 3: pilih gambar → pratinjau tampil; switch Aktif menyala.
            $browser->attach('@image-input', $this->imageFile('png'))
                ->waitUsing(5, 100, fn () => str_starts_with((string) $browser->attribute('@image-preview', 'src'), 'data:image'))
                ->assertVisible('@image-preview')
                ->assertChecked('@active-switch')
                ->shot('Oktavia/TC.LandingPage.001-step3');

            // Langkah 4: simpan.
            $browser->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/super-admin/hero-sections')
                ->assertSee('Hero section berhasil ditambahkan.')
                ->assertSeeIn('@hero-table', self::HERO_TITLE)
                ->shot('Oktavia/TC.LandingPage.001-step4');
        });

        $hero = HeroSection::where('title', self::HERO_TITLE)->firstOrFail();
        $this->assertSame(self::HERO_SUBTITLE, $hero->subtitle);
        $this->assertSame('/e-learning', $hero->target_url);
        $this->assertTrue($hero->is_active);
        $this->assertSame($superAdmin->id, $hero->super_admin_id);
        $this->assertStringStartsWith('hero/', $hero->image_url);
        $this->assertTrue(Storage::disk('public')->exists($hero->image_url), 'File gambar harus tersimpan di storage/app/public/hero.');
    }

    /**
     * Case ID  : TC.LandingPage.002
     * Test Case: Super Admin menambahkan hero section dengan switch Aktif dimatikan.
     */
    public function test_tc_landing_page_002_tambah_hero_nonaktif(): void
    {
        $superAdmin = $this->makeSuperAdmin();

        $this->browse(function (Browser $browser) use ($superAdmin) {
            $browser->loginAs($superAdmin)
                ->visit('/super-admin/hero-sections/create')
                ->type('@title-input', 'Banner Nonaktif Uji')
                ->attach('@image-input', $this->imageFile('png'))
                ->uncheck('@active-switch')
                ->assertNotChecked('@active-switch')
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/super-admin/hero-sections')
                ->assertSee('Hero section berhasil ditambahkan.');

            $hero = HeroSection::where('title', 'Banner Nonaktif Uji')->firstOrFail();
            $this->assertFalse($hero->is_active);

            $browser->assertSeeIn("@hero-row-{$hero->id}", 'Nonaktif')
                ->shot('Oktavia/TC.LandingPage.002-step1')
                ->visit('/')
                ->assertDontSee('Banner Nonaktif Uji')
                ->assertSourceMissing('Banner Nonaktif Uji')
                ->shot('Oktavia/TC.LandingPage.002-step1-landing');
        });
    }

    /**
     * Case ID  : TC.LandingPage.003
     * Test Case: Super Admin menyimpan hero section dengan mengosongkan Judul Hero dan Gambar Hero.
     */
    public function test_tc_landing_page_003_validasi_form_hero(): void
    {
        $superAdmin = $this->makeSuperAdmin();

        $this->browse(function (Browser $browser) use ($superAdmin) {
            // Langkah 1: judul & gambar kosong.
            $browser->loginAs($superAdmin)
                ->visit('/super-admin/hero-sections/create')
                ->disableHtmlValidation()
                ->clear('@title-input')
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/super-admin/hero-sections/create')
                ->assertSee('Judul hero wajib diisi.')
                ->assertSee('Gambar hero wajib diunggah.')
                ->shot('Oktavia/TC.LandingPage.003-step1');

            // Langkah 2: judul > 200 karakter (maxlength dihapus, setara Inspect Element).
            $browser->visit('/super-admin/hero-sections/create')->disableHtmlValidation();
            $browser->script("document.querySelector('[dusk=\"title-input\"]').removeAttribute('maxlength');");

            $browser->type('@title-input', str_repeat('a', 201));
            $this->assertSame(201, mb_strlen($browser->value('@title-input')));

            $browser->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/super-admin/hero-sections/create')
                ->assertSee('Judul hero maksimal 200 karakter.')
                ->shot('Oktavia/TC.LandingPage.003-step2');
        });

        $this->assertDatabaseCount('hero_sections', 0);
    }

    /**
     * Case ID  : TC.LandingPage.004
     * Test Case: Super Admin mengunggah gambar hero dengan format tidak didukung atau ukuran > 3 MB.
     */
    public function test_tc_landing_page_004_validasi_unggah_gambar_hero(): void
    {
        $superAdmin = $this->makeSuperAdmin();

        $cases = [
            'pdf' => [$this->nonImageFile('pdf'), 'File yang diunggah harus berupa gambar.'],
            'gif' => [$this->imageFile('gif'), 'Format gambar harus JPG, JPEG, PNG, atau WebP.'],
            'lebih-3mb' => [$this->largeImageFile(3), 'Ukuran gambar maksimal 3 MB.'],
        ];

        $this->browse(function (Browser $browser) use ($superAdmin, $cases) {
            $browser->loginAs($superAdmin);

            foreach ($cases as $label => [$file, $message]) {
                $browser->visit('/super-admin/hero-sections/create')
                    ->type('@title-input', 'Hero Uji '.$label)
                    ->attach('@image-input', $file)
                    ->clickAndWaitForReload('@submit-button')
                    ->assertPathIs('/super-admin/hero-sections/create')
                    ->shot("Oktavia/TC.LandingPage.004-step1-{$label}")
                    ->assertDontSee('Hero section berhasil ditambahkan.')
                    ->assertSee($message);

                $this->assertDatabaseCount('hero_sections', 0);
            }
        });
    }

    /**
     * Case ID  : TC.LandingPage.005
     * Test Case: Super Admin memasukkan Target URL yang bukan URL valid atau menggunakan skema berbahaya.
     */
    public function test_tc_landing_page_005_validasi_target_url(): void
    {
        $superAdmin = $this->makeSuperAdmin();

        $this->browse(function (Browser $browser) use ($superAdmin) {
            $browser->loginAs($superAdmin);

            foreach (['bukan-url' => 'bukan-url', 'javascript' => 'javascript:alert(1)'] as $label => $targetUrl) {
                $browser->visit('/super-admin/hero-sections/create')
                    ->disableHtmlValidation()
                    ->type('@title-input', 'Hero Target '.$label)
                    ->type('@target-url-input', $targetUrl)
                    ->attach('@image-input', $this->imageFile('png'))
                    ->clickAndWaitForReload('@submit-button')
                    ->assertPathIs('/super-admin/hero-sections/create')
                    ->assertDontSee('Hero section berhasil ditambahkan.')
                    ->assertSeeIn('#target_url-error', 'Target URL harus berupa URL yang valid.')
                    ->assertInputValue('@target-url-input', $targetUrl)
                    ->shot("Oktavia/TC.LandingPage.005-step1-{$label}");
            }
        });

        $this->assertDatabaseCount('hero_sections', 0);
    }

    /**
     * Case ID  : TC.LandingPage.006
     * Test Case: Super Admin menulis artikel baru dengan gambar sampul dan langsung mempublikasikannya.
     */
    public function test_tc_landing_page_006_tambah_artikel_dipublikasikan(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $title = '5 Skill yang Paling Dicari Perusahaan';

        $this->browse(function (Browser $browser) use ($superAdmin, $title) {
            // Langkah 1: klik "+ Tambah Artikel".
            $browser->loginAs($superAdmin)
                ->visit('/super-admin/articles')
                ->assertSeeIn('@create-button', '+ Tambah Artikel')
                ->clickAndWaitForReload('@create-button')
                ->assertPathIs('/super-admin/articles/create')
                ->assertSee('Tulis Artikel Baru')
                ->assertSeeIn('@article-form', 'Judul')
                ->assertSeeIn('@article-form', 'Slug URL (Otomatis)')
                ->assertSeeIn('@article-form', 'Ringkasan / Excerpt (Opsional)')
                ->assertSeeIn('@article-form', 'Isi Artikel')
                ->assertSeeIn('@article-form', 'Status')
                ->assertSeeIn('@article-form', 'Gambar Sampul (Opsional)')
                ->shot('Oktavia/TC.LandingPage.006-step1');

            // Langkah 2: isi judul, ringkasan, isi → slug otomatis.
            $browser->type('@title-input', $title)
                ->type('@excerpt-input', 'Kenali skill yang paling dicari perusahaan saat merekrut lulusan baru.')
                ->type('@content-input', "Komunikasi, kerja sama tim, dan problem solving.\n\nPelajari cara melatihnya.")
                ->waitUsing(5, 100, fn () => $browser->value('@slug-preview') === '5-skill-yang-paling-dicari-perusahaan')
                ->assertInputValue('@slug-preview', '5-skill-yang-paling-dicari-perusahaan')
                ->shot('Oktavia/TC.LandingPage.006-step2');

            // Langkah 3: status Dipublikasikan + sampul → simpan.
            $browser->select('@status-select', 'published')
                ->attach('@cover-input', $this->imageFile('png'))
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/super-admin/articles')
                ->assertSee('Artikel berhasil ditambahkan.')
                ->assertSeeIn('@article-table', $title)
                ->shot('Oktavia/TC.LandingPage.006-step3');
        });

        $article = Article::where('title', $title)->firstOrFail();
        $this->assertSame('5-skill-yang-paling-dicari-perusahaan', $article->slug);
        $this->assertSame(ArticleStatus::Published, $article->status);
        $this->assertNotNull($article->published_at, 'Tanggal publish harus terisi.');
        $this->assertStringStartsWith('articles/', $article->cover_image_url);
        $this->assertTrue(Storage::disk('public')->exists($article->cover_image_url), 'File sampul harus tersimpan di storage/app/public/articles.');
    }

    /**
     * Case ID  : TC.LandingPage.007
     * Test Case: Super Admin menyimpan artikel sebagai Draf tanpa gambar sampul.
     */
    public function test_tc_landing_page_007_tambah_artikel_draf(): void
    {
        $superAdmin = $this->makeSuperAdmin();

        $this->browse(function (Browser $browser) use ($superAdmin) {
            $browser->loginAs($superAdmin)
                ->visit('/super-admin/articles/create')
                ->type('@title-input', 'Artikel Draf Persiapan Magang')
                ->type('@content-input', 'Isi artikel draf yang belum siap tayang.')
                ->select('@status-select', 'draft')
                ->assertSelected('@status-select', 'draft')
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/super-admin/articles')
                ->assertSee('Artikel berhasil ditambahkan.')
                ->shot('Oktavia/TC.LandingPage.007-step1')
                ->visit('/artikel')
                ->assertDontSee('Artikel Draf Persiapan Magang')
                ->shot('Oktavia/TC.LandingPage.007-step1-publik');
        });

        $article = Article::where('title', 'Artikel Draf Persiapan Magang')->firstOrFail();
        $this->assertSame(ArticleStatus::Draft, $article->status);
        $this->assertNull($article->published_at);
        $this->assertNull($article->cover_image_url);
    }

    /**
     * Case ID  : TC.LandingPage.008
     * Test Case: Super Admin menambahkan artikel dengan judul yang sama persis dengan artikel yang sudah ada.
     */
    public function test_tc_landing_page_008_slug_unik_untuk_judul_sama(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $existing = Article::factory()->create(['super_admin_id' => $superAdmin->id, 'title' => 'Tips Interview']);
        $this->assertSame('tips-interview', $existing->slug);

        $this->browse(function (Browser $browser) use ($superAdmin) {
            $browser->loginAs($superAdmin)
                ->visit('/super-admin/articles/create')
                ->type('@title-input', 'Tips Interview')
                ->type('@content-input', 'Artikel kedua dengan judul yang sama.')
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/super-admin/articles')
                ->assertSee('Artikel berhasil ditambahkan.')
                ->shot('Oktavia/TC.LandingPage.008-step1');
        });

        $this->assertSame(
            ['tips-interview', 'tips-interview-2'],
            Article::where('title', 'Tips Interview')->orderBy('id')->pluck('slug')->all()
        );
    }

    /**
     * Case ID  : TC.LandingPage.009
     * Test Case: Super Admin menyimpan artikel dengan field wajib kosong, ringkasan terlalu panjang,
     *            atau sampul tidak valid.
     */
    public function test_tc_landing_page_009_validasi_form_artikel(): void
    {
        $superAdmin = $this->makeSuperAdmin();

        $this->browse(function (Browser $browser) use ($superAdmin) {
            // Langkah 1: judul & isi kosong.
            $browser->loginAs($superAdmin)
                ->visit('/super-admin/articles/create')
                ->disableHtmlValidation()
                ->clear('@title-input')
                ->clear('@content-input')
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/super-admin/articles/create')
                ->assertSee('Judul artikel wajib diisi.')
                ->assertSee('Konten artikel wajib diisi.')
                ->shot('Oktavia/TC.LandingPage.009-step1');

            // Langkah 2: ringkasan > 300 karakter (maxlength dihapus, setara Inspect Element).
            $browser->visit('/super-admin/articles/create')->disableHtmlValidation();
            $browser->script("document.querySelector('[dusk=\"excerpt-input\"]').removeAttribute('maxlength');");

            $browser->type('@title-input', 'Artikel Validasi')
                ->type('@content-input', 'Isi artikel valid.')
                ->type('@excerpt-input', str_repeat('r', 301))
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/super-admin/articles/create')
                ->assertSee('Ringkasan maksimal 300 karakter.')
                ->shot('Oktavia/TC.LandingPage.009-step2');

            // Langkah 3: ringkasan valid, sampul tidak valid.
            $cases = [
                'pdf' => [$this->nonImageFile('pdf'), 'File sampul harus berupa gambar.'],
                'gif' => [$this->imageFile('gif'), 'Format sampul harus JPG, JPEG, PNG, atau WebP.'],
                'lebih-3mb' => [$this->largeImageFile(3), 'Ukuran sampul maksimal 3 MB.'],
            ];

            foreach ($cases as $label => [$file, $message]) {
                $browser->visit('/super-admin/articles/create')
                    ->type('@title-input', 'Artikel Sampul '.$label)
                    ->type('@excerpt-input', 'Ringkasan valid.')
                    ->type('@content-input', 'Isi artikel valid.')
                    ->attach('@cover-input', $file)
                    ->clickAndWaitForReload('@submit-button')
                    ->assertPathIs('/super-admin/articles/create')
                    ->shot("Oktavia/TC.LandingPage.009-step3-{$label}")
                    ->assertDontSee('Artikel berhasil ditambahkan.')
                    ->assertSee($message);
            }
        });

        $this->assertDatabaseCount('articles', 0);
    }

    /**
     * Case ID  : TC.LandingPage.010
     * Test Case: Guest, User biasa, atau Admin mencoba mengakses halaman tambah hero section dan artikel.
     */
    public function test_tc_landing_page_010_otorisasi_halaman_tambah(): void
    {
        $user = $this->makeUser();
        $admin = $this->makeAdmin();
        $urls = ['hero' => '/super-admin/hero-sections/create', 'artikel' => '/super-admin/articles/create'];

        $this->browse(function (Browser $browser) use ($user, $admin, $urls) {
            foreach ($urls as $label => $url) {
                $browser->visit($url)
                    ->assertPathIs('/login')
                    ->shot("Oktavia/TC.LandingPage.010-step1-guest-{$label}");
            }

            foreach (['user' => $user, 'admin' => $admin] as $role => $account) {
                foreach ($urls as $label => $url) {
                    $browser->loginAs($account)
                        ->visit($url)
                        ->assertPathIs($url)
                        ->assertSee('403')
                        ->assertSourceHas('Anda tidak memiliki akses ke halaman ini.')
                        ->shot("Oktavia/TC.LandingPage.010-step2-{$role}-{$label}");
                }
            }
        });
    }
}
