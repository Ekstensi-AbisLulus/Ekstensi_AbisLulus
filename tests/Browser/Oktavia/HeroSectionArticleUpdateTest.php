<?php

namespace Tests\Browser\Oktavia;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\HeroSection;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Concerns\CreatesTestUsers;
use Tests\Browser\Concerns\MakesUploadFiles;
use Tests\DuskTestCase;

/**
 * PBI-24 — Ubah Hero Section & Artikel (TC.LandingPage.018–024).
 */
#[Group('oktavia')]
class HeroSectionArticleUpdateTest extends DuskTestCase
{
    use CleansLandingUploads, CreatesTestUsers, DatabaseMigrations, MakesUploadFiles;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = $this->makeSuperAdmin();
    }

    /**
     * Case ID  : TC.LandingPage.018
     * Test Case: Super Admin mengubah judul dan subjudul hero section tanpa mengunggah gambar baru.
     */
    public function test_tc_landing_page_018_ubah_hero_tanpa_ganti_gambar(): void
    {
        $hero = $this->hero('Judul Hero Lama', ['subtitle' => 'Subjudul hero lama.', 'target_url' => '/e-learning']);
        $image = $this->giveStoredHeroImage($hero);

        $this->browse(function (Browser $browser) use ($hero, $image) {
            // Langkah 1: klik ikon Ubah → form terisi data lama.
            $browser->loginAs($this->superAdmin)
                ->visit('/super-admin/hero-sections')
                ->clickAndWaitForReload("@edit-{$hero->id}")
                ->assertPathIs("/super-admin/hero-sections/{$hero->id}/edit")
                ->assertSee('Ubah Banner Hero')
                ->assertInputValue('@title-input', 'Judul Hero Lama')
                ->assertInputValue('@subtitle-input', 'Subjudul hero lama.')
                ->assertInputValue('@target-url-input', '/e-learning')
                ->assertChecked('@active-switch')
                ->assertAttributeContains('@image-preview', 'src', $image)
                ->assertSee('Ganti Gambar Hero (Opsional)')
                ->shot('Oktavia/TC.LandingPage.018-step1');

            // Langkah 2: ubah judul & subjudul tanpa gambar baru.
            $browser->type('@title-input', 'Judul Hero Diperbarui')
                ->type('@subtitle-input', 'Subjudul hero yang sudah diperbarui.')
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/super-admin/hero-sections')
                ->assertSee('Data berhasil diperbarui.')
                ->assertSeeIn("@hero-row-{$hero->id}", 'Judul Hero Diperbarui')
                ->assertAttributeContains("@hero-row-{$hero->id} img", 'src', $image)
                ->shot('Oktavia/TC.LandingPage.018-step2');
        });

        $fresh = $hero->fresh();
        $this->assertSame('Judul Hero Diperbarui', $fresh->title);
        $this->assertSame('Subjudul hero yang sudah diperbarui.', $fresh->subtitle);
        $this->assertSame($image, $fresh->image_url, 'Gambar lama harus tetap digunakan.');
        $this->assertTrue(Storage::disk('public')->exists($image));
    }

    /**
     * Case ID  : TC.LandingPage.019
     * Test Case: Super Admin mengganti gambar hero dengan gambar baru.
     */
    public function test_tc_landing_page_019_ganti_gambar_hero(): void
    {
        $hero = $this->hero('Banner Ganti Gambar');
        $oldImage = $this->giveStoredHeroImage($hero);

        $this->browse(function (Browser $browser) use ($hero, $oldImage) {
            $browser->loginAs($this->superAdmin)
                ->visit("/super-admin/hero-sections/{$hero->id}/edit")
                ->attach('@image-input', $this->imageFile('png', 120))
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/super-admin/hero-sections')
                ->assertSee('Data berhasil diperbarui.');

            $newImage = $hero->fresh()->image_url;

            $this->assertNotSame($oldImage, $newImage);
            $this->assertStringStartsWith('hero/', $newImage);
            $this->assertTrue(Storage::disk('public')->exists($newImage), 'Gambar baru harus tersimpan.');
            $this->assertFalse(Storage::disk('public')->exists($oldImage), 'Gambar lama harus terhapus.');

            $browser->assertAttributeContains("@hero-row-{$hero->id} img", 'src', $newImage)
                ->shot('Oktavia/TC.LandingPage.019-step1')
                ->visit('/')
                ->assertAttributeContains('@hero-single img.hero-banner-img', 'src', $newImage)
                ->shot('Oktavia/TC.LandingPage.019-step1-landing');
        });
    }

    /**
     * Case ID  : TC.LandingPage.020
     * Test Case: Super Admin mengubah status tampil hero langsung dari tabel daftar.
     */
    public function test_tc_landing_page_020_toggle_status_hero(): void
    {
        $hero = $this->hero('Banner Toggle Uji');

        $this->browse(function (Browser $browser) use ($hero) {
            // Langkah 1: Aktif → Nonaktif.
            $browser->loginAs($this->superAdmin)
                ->visit('/super-admin/hero-sections')
                ->assertSeeIn("@toggle-{$hero->id}", 'Aktif')
                ->clickAndWaitForReload("@toggle-{$hero->id}")
                ->assertSee('Data berhasil diperbarui.')
                ->assertSeeIn("@toggle-{$hero->id}", 'Nonaktif')
                ->shot('Oktavia/TC.LandingPage.020-step1');

            $this->assertFalse($hero->fresh()->is_active);

            $browser->visit('/')
                ->assertSourceMissing('Banner Toggle Uji')
                ->shot('Oktavia/TC.LandingPage.020-step1-landing');

            // Langkah 2: Nonaktif → Aktif.
            $browser->visit('/super-admin/hero-sections')
                ->clickAndWaitForReload("@toggle-{$hero->id}")
                ->assertSee('Data berhasil diperbarui.')
                ->assertSeeIn("@toggle-{$hero->id}", 'Aktif')
                ->assertDontSeeIn("@toggle-{$hero->id}", 'Nonaktif')
                ->shot('Oktavia/TC.LandingPage.020-step2');

            $this->assertTrue($hero->fresh()->is_active);

            $browser->visit('/')
                ->assertSeeIn('@hero-single', 'Banner Toggle Uji')
                ->shot('Oktavia/TC.LandingPage.020-step2-landing');
        });
    }

    /**
     * Case ID  : TC.LandingPage.021
     * Test Case: Super Admin mengubah judul artikel dengan dan tanpa memperbarui slug.
     */
    public function test_tc_landing_page_021_ubah_artikel_dan_slug(): void
    {
        $article = $this->article('Tips Interview', [
            'excerpt' => 'Ringkasan tips interview.',
            'content' => 'Isi artikel tips interview.',
        ]);
        $cover = $this->giveStoredCover($article);
        $this->assertSame('tips-interview', $article->fresh()->slug);

        $this->browse(function (Browser $browser) use ($article, $cover) {
            // Langkah 1: klik ikon Ubah → form terisi data lama.
            $browser->loginAs($this->superAdmin)
                ->visit('/super-admin/articles')
                ->clickAndWaitForReload("@edit-{$article->id}")
                ->assertPathIs("/super-admin/articles/{$article->slug}/edit")
                ->assertSee('Ubah Artikel')
                ->assertInputValue('@title-input', 'Tips Interview')
                ->assertInputValue('@slug-preview', 'tips-interview')
                ->assertInputValue('@excerpt-input', 'Ringkasan tips interview.')
                ->assertInputValue('@content-input', 'Isi artikel tips interview.')
                ->assertSelected('@status-select', 'published')
                ->assertNotChecked('@update-slug')
                ->assertAttributeContains('@cover-preview', 'src', $cover)
                ->shot('Oktavia/TC.LandingPage.021-step1');

            // Langkah 2: ubah judul tanpa centang "Perbarui slug".
            $browser->type('@title-input', 'Tips Interview Kerja')
                ->assertInputValue('@slug-preview', 'tips-interview')
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/super-admin/articles')
                ->assertSee('Data berhasil diperbarui.')
                ->shot('Oktavia/TC.LandingPage.021-step2');

            $this->assertSame('Tips Interview Kerja', $article->fresh()->title);
            $this->assertSame('tips-interview', $article->fresh()->slug);

            // Langkah 3: centang "Perbarui slug sesuai judul baru".
            $browser->visit("/super-admin/articles/{$article->slug}/edit")
                ->assertSee('Perbarui slug sesuai judul baru')
                ->check('@update-slug')
                ->waitUsing(5, 100, fn () => $browser->value('@slug-preview') === 'tips-interview-kerja')
                ->assertInputValue('@slug-preview', 'tips-interview-kerja')
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/super-admin/articles')
                ->assertSee('Data berhasil diperbarui.')
                ->shot('Oktavia/TC.LandingPage.021-step3');
        });

        $this->assertSame('tips-interview-kerja', $article->fresh()->slug);
        $this->assertSame(1, Article::where('slug', 'tips-interview-kerja')->count());
    }

    /**
     * Case ID  : TC.LandingPage.022
     * Test Case: Super Admin mengganti gambar sampul dan mempublikasikan artikel yang sebelumnya Draf.
     */
    public function test_tc_landing_page_022_ganti_sampul_dan_publikasikan(): void
    {
        $article = $this->article('Artikel Draf Bersampul', ['status' => ArticleStatus::Draft, 'published_at' => null]);
        $oldCover = $this->giveStoredCover($article);

        $this->browse(function (Browser $browser) use ($article, $oldCover) {
            $browser->loginAs($this->superAdmin)
                ->visit("/super-admin/articles/{$article->slug}/edit")
                ->assertSelected('@status-select', 'draft')
                ->attach('@cover-input', $this->imageFile('png', 120))
                ->select('@status-select', 'published')
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/super-admin/articles')
                ->assertSee('Data berhasil diperbarui.')
                ->assertSeeIn("@article-row-{$article->id}", 'Dipublikasikan');

            $fresh = $article->fresh();

            $this->assertSame(ArticleStatus::Published, $fresh->status);
            $this->assertNotNull($fresh->published_at, 'Tanggal publish harus terisi otomatis.');
            $this->assertNotSame($oldCover, $fresh->cover_image_url);
            $this->assertTrue(Storage::disk('public')->exists($fresh->cover_image_url), 'Sampul baru harus tersimpan.');
            $this->assertFalse(Storage::disk('public')->exists($oldCover), 'Sampul lama harus terhapus.');

            $browser->assertAttributeContains("@article-row-{$article->id} img", 'src', $fresh->cover_image_url)
                ->shot('Oktavia/TC.LandingPage.022-step1')
                ->visit('/artikel')
                ->assertSee('Artikel Draf Bersampul')
                ->shot('Oktavia/TC.LandingPage.022-step1-publik');
        });
    }

    /**
     * Case ID  : TC.LandingPage.023
     * Test Case: Super Admin mengosongkan field wajib saat mengubah hero section atau artikel.
     */
    public function test_tc_landing_page_023_validasi_form_ubah(): void
    {
        $hero = $this->hero('Judul Hero Asli');
        $article = $this->article('Judul Artikel Asli', ['content' => 'Isi artikel asli.']);

        $this->browse(function (Browser $browser) use ($hero, $article) {
            // Langkah 1: judul hero kosong.
            $browser->loginAs($this->superAdmin)
                ->visit("/super-admin/hero-sections/{$hero->id}/edit")
                ->disableHtmlValidation()
                ->clear('@title-input')
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs("/super-admin/hero-sections/{$hero->id}/edit")
                ->assertDontSee('Data berhasil diperbarui.')
                ->assertSee('Judul hero wajib diisi.')
                ->shot('Oktavia/TC.LandingPage.023-step1');

            // Langkah 2: judul & isi artikel kosong.
            $browser->visit("/super-admin/articles/{$article->slug}/edit")
                ->disableHtmlValidation()
                ->clear('@title-input')
                ->clear('@content-input')
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs("/super-admin/articles/{$article->slug}/edit")
                ->assertDontSee('Data berhasil diperbarui.')
                ->assertSee('Judul artikel wajib diisi.')
                ->assertSee('Konten artikel wajib diisi.')
                ->shot('Oktavia/TC.LandingPage.023-step2');
        });

        $this->assertSame('Judul Hero Asli', $hero->fresh()->title);
        $this->assertSame('Judul Artikel Asli', $article->fresh()->title);
        $this->assertSame('Isi artikel asli.', $article->fresh()->content);
    }

    /**
     * Case ID  : TC.LandingPage.024
     * Test Case: Guest, User biasa, atau Admin mencoba mengakses halaman ubah hero section dan artikel.
     */
    public function test_tc_landing_page_024_otorisasi_halaman_ubah(): void
    {
        $hero = $this->hero('Hero Terlindungi');
        $article = $this->article('Artikel Terlindungi');
        $user = $this->makeUser();
        $admin = $this->makeAdmin();

        $urls = [
            'hero' => "/super-admin/hero-sections/{$hero->id}/edit",
            'artikel' => "/super-admin/articles/{$article->slug}/edit",
        ];

        $this->browse(function (Browser $browser) use ($urls, $user, $admin) {
            foreach ($urls as $label => $url) {
                $browser->visit($url)
                    ->assertPathIs('/login')
                    ->shot("Oktavia/TC.LandingPage.024-step1-guest-{$label}");
            }

            foreach (['user' => $user, 'admin' => $admin] as $role => $account) {
                foreach ($urls as $label => $url) {
                    $browser->loginAs($account)
                        ->visit($url)
                        ->assertPathIs($url)
                        ->assertSee('403')
                        ->assertSourceHas('Anda tidak memiliki akses ke halaman ini.')
                        ->shot("Oktavia/TC.LandingPage.024-step2-{$role}-{$label}");
                }
            }
        });
    }

    // --- Helper ---------------------------------------------------------------

    private function hero(string $title, array $attributes = []): HeroSection
    {
        return HeroSection::factory()->create($attributes + [
            'super_admin_id' => $this->superAdmin->id,
            'title' => $title,
            'is_active' => true,
        ]);
    }

    private function article(string $title, array $attributes = []): Article
    {
        return Article::factory()->create($attributes + [
            'super_admin_id' => $this->superAdmin->id,
            'title' => $title,
            'status' => ArticleStatus::Published,
        ]);
    }
}
