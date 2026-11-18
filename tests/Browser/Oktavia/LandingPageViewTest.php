<?php

namespace Tests\Browser\Oktavia;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\HeroSection;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Concerns\CreatesTestUsers;
use Tests\DuskTestCase;

/**
 * PBI-23 — Lihat Hero Section & Artikel (panel Super Admin & publik) (TC.LandingPage.011–017).
 */
#[Group('oktavia')]
class LandingPageViewTest extends DuskTestCase
{
    use CreatesTestUsers, DatabaseMigrations;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = $this->makeSuperAdmin(['name' => 'Tim Redaksi AbisLulus']);
    }

    /**
     * Case ID  : TC.LandingPage.011
     * Test Case: Super Admin melihat daftar hero section dan pratinjau detail banner.
     */
    public function test_tc_landing_page_011_daftar_dan_detail_hero(): void
    {
        foreach (range(1, 10) as $n) {
            $this->hero(sprintf('Hero Pendukung %02d', $n), ['is_active' => $n % 2 === 0]);
        }
        $inactive = HeroSection::where('title', 'Hero Pendukung 09')->firstOrFail();
        $target = $this->hero('Banner Karier Utama', [
            'subtitle' => 'Ikuti mentoring karier gratis bersama praktisi.',
            'target_url' => '/e-learning',
        ]);

        $this->browse(function (Browser $browser) use ($target, $inactive) {
            // Langkah 1: menu Hero Section.
            $browser->loginAs($this->superAdmin)
                ->visit('/super-admin/dashboard')
                ->clickAndWaitForReload('.panel-sidebar a[href$="/super-admin/hero-sections"]')
                ->assertPathIs('/super-admin/hero-sections')
                ->assertSee('Daftar Hero Section')
                ->assertSeeIn('@create-button', '+ Tambah Hero');

            $this->assertSame(['Pratinjau', 'Judul', 'Subjudul', 'Status', 'Tanggal', 'Aksi'], $this->headers($browser, 'hero-table'));
            $this->assertSame(10, $this->rowCount($browser, 'hero-row-'), 'Maksimal 10 data per halaman.');

            $row = "@hero-row-{$target->id}";
            $browser->assertPresent("{$row} img")
                ->assertSeeIn($row, 'Banner Karier Utama')
                ->assertSeeIn($row, 'Ikuti mentoring karier gratis bersama praktisi.')
                ->assertSeeIn($row, 'Aktif')
                ->assertSeeIn($row, $target->created_at->format('d M Y'))
                ->assertSeeIn("@hero-row-{$inactive->id}", 'Nonaktif')
                ->assertAttribute("@view-{$target->id}", 'title', 'Lihat pratinjau')
                ->assertAttribute("@edit-{$target->id}", 'title', 'Ubah')
                ->assertAttribute("@delete-{$target->id}", 'title', 'Hapus')
                ->assertPresent('@pagination .pagination')
                ->assertPresent('@pagination a[href*="page=2"]')
                ->shot('Oktavia/TC.LandingPage.011-step1');

            // Langkah 2: Lihat pratinjau.
            $browser->clickAndWaitForReload("@view-{$target->id}")
                ->assertPathIs("/super-admin/hero-sections/{$target->id}")
                ->assertSee('Pratinjau Tampilan Landing Page')
                ->assertSeeIn('@hero-preview', 'Banner Karier Utama')
                ->assertSeeIn('@hero-preview', 'Ikuti mentoring karier gratis bersama praktisi.')
                ->assertPresent('@hero-preview img')
                ->assertSeeIn('@hero-detail', '/e-learning')
                ->assertSeeIn('@hero-detail', 'Aktif (ditampilkan di beranda)')
                ->shot('Oktavia/TC.LandingPage.011-step2');
        });
    }

    /**
     * Case ID  : TC.LandingPage.012
     * Test Case: Super Admin melihat daftar artikel, mencari berdasarkan judul, dan memfilter berdasarkan status.
     */
    public function test_tc_landing_page_012_daftar_cari_dan_filter_artikel(): void
    {
        foreach (range(1, 8) as $n) {
            $this->article(sprintf('Artikel Pendukung %02d', $n));
        }
        $this->article('Panduan Membuat CV');
        $this->article('Skill Excel Kantor', ['status' => ArticleStatus::Draft, 'published_at' => null]);
        $published = $this->article('Skill Komunikasi untuk Wawancara');

        $this->browse(function (Browser $browser) use ($published) {
            // Langkah 1: menu Artikel.
            $browser->loginAs($this->superAdmin)
                ->visit('/super-admin/dashboard')
                ->clickAndWaitForReload('.panel-sidebar a[href$="/super-admin/articles"]')
                ->assertPathIs('/super-admin/articles')
                ->assertSee('Daftar Artikel')
                ->assertSeeIn('@create-button', '+ Tambah Artikel');

            $this->assertSame(['Sampul', 'Judul', 'Status', 'Tanggal Publish', 'Aksi'], $this->headers($browser, 'article-table'));
            $this->assertSame(10, $this->rowCount($browser, 'article-row-'), 'Maksimal 10 data per halaman.');

            $row = "@article-row-{$published->id}";
            $browser->assertPresent("{$row} img")
                ->assertSeeIn($row, 'Dipublikasikan')
                ->assertSeeIn($row, $published->published_at->format('d M Y'))
                ->assertAttribute("@view-{$published->id}", 'title', 'Lihat pratinjau')
                ->assertAttribute("@edit-{$published->id}", 'title', 'Ubah')
                ->assertAttribute("@delete-{$published->id}", 'title', 'Hapus')
                ->assertPresent('@pagination .pagination')
                ->shot('Oktavia/TC.LandingPage.012-step1');

            // Langkah 2: cari "Skill".
            $browser->type('@search-input', 'Skill')
                ->clickAndWaitForReload('@search-submit')
                ->assertQueryStringHas('search', 'Skill');

            $this->assertSame(['Skill Komunikasi untuk Wawancara', 'Skill Excel Kantor'], $this->titles($browser));
            $browser->shot('Oktavia/TC.LandingPage.012-step2');

            // Langkah 3: filter status Draf.
            $browser->select('@status-filter', 'draft')
                ->clickAndWaitForReload('@search-submit')
                ->assertQueryStringHas('status', 'draft');

            $rows = $this->rows($browser);
            $this->assertNotEmpty($rows);
            foreach ($rows as $cells) {
                $this->assertSame('Draf', $cells[2], 'Hanya artikel berstatus Draf yang tampil.');
            }
            $this->assertSame(['Skill Excel Kantor'], $this->titles($browser));
            $browser->shot('Oktavia/TC.LandingPage.012-step3');
        });
    }

    /**
     * Case ID  : TC.LandingPage.013
     * Test Case: Super Admin mencari artikel dengan kata kunci yang tidak cocok dengan judul mana pun.
     */
    public function test_tc_landing_page_013_pencarian_artikel_tidak_ditemukan(): void
    {
        $this->article('Panduan Membuat CV');

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                ->visit('/super-admin/articles')
                ->type('@search-input', 'KataKunciTidakAda123')
                ->clickAndWaitForReload('@search-submit')
                ->assertMissing('@article-table')
                ->assertSeeIn('@empty-state', 'Tidak ada artikel yang cocok')
                ->shot('Oktavia/TC.LandingPage.013-step1');
        });
    }

    /**
     * Case ID  : TC.LandingPage.014
     * Test Case: Pengunjung melihat banner hero yang berstatus Aktif di halaman beranda.
     */
    public function test_tc_landing_page_014_hero_carousel_di_landing(): void
    {
        $this->hero('Hero Aktif Kedua', ['subtitle' => 'Subjudul hero aktif kedua.']);
        $this->hero('Hero Nonaktif Rahasia', ['is_active' => false]);
        $this->hero('Siap Berkarier Bersama Mentor', [
            'subtitle' => 'Ikuti Career Mentoring gratis bersama praktisi.',
            'target_url' => '/e-learning',
        ]);

        $this->browse(function (Browser $browser) {
            // Langkah 1: beranda sebagai Guest.
            $browser->visit('/')
                ->assertPresent('@hero-carousel')
                ->assertVisible('@hero-prev')
                ->assertVisible('@hero-next')
                ->assertAttribute('@hero-prev', 'aria-label', 'Slide sebelumnya')
                ->assertAttribute('@hero-next', 'aria-label', 'Slide berikutnya')
                ->assertSeeIn('.carousel-item.active', 'Siap Berkarier Bersama Mentor')
                ->assertSeeIn('.carousel-item.active', 'Ikuti Career Mentoring gratis bersama praktisi.')
                ->assertPresent('.carousel-item.active .hero-banner-overlay')
                ->assertPresent('.carousel-item.active img.hero-banner-img')
                ->assertSourceHas('Hero Aktif Kedua')
                ->assertSourceMissing('Hero Nonaktif Rahasia');

            $this->assertSame(2, $browser->script("return document.querySelectorAll('[dusk=\"hero-carousel\"] .carousel-item').length;")[0]);
            $browser->shot('Oktavia/TC.LandingPage.014-step1');

            // Langkah 2: klik "Jelajahi Program".
            $browser->assertSeeIn('.carousel-item.active [dusk="hero-cta"]', 'Jelajahi Program')
                ->clickAndWaitForReload('.carousel-item.active [dusk="hero-cta"]')
                ->assertPathIs('/e-learning')
                ->shot('Oktavia/TC.LandingPage.014-step2');
        });
    }

    /**
     * Case ID  : TC.LandingPage.015
     * Test Case: Landing page dibuka saat belum ada hero section berstatus Aktif.
     */
    public function test_tc_landing_page_015_hero_default(): void
    {
        $this->hero('Hero Nonaktif Saja', ['is_active' => false]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertDontSee('Server Error')
                ->assertMissing('@hero-carousel')
                ->assertMissing('@hero-single')
                ->assertSeeIn('@hero-default', 'Siap Melangkah Setelah Lulus Bersama AbisLulus')
                ->assertSeeIn('@default-learn-button', 'Mulai Belajar')
                ->assertSeeIn('@default-events-button', 'Lihat Kegiatan')
                ->assertDontSee('Hero Nonaktif Saja')
                ->shot('Oktavia/TC.LandingPage.015-step1');
        });
    }

    /**
     * Case ID  : TC.LandingPage.016
     * Test Case: Pengunjung melihat artikel terbaru di beranda, daftar artikel, dan detail artikel.
     */
    public function test_tc_landing_page_016_artikel_di_landing_dan_halaman_publik(): void
    {
        $articles = [];
        foreach (range(1, 10) as $n) {
            $articles[$n] = $this->article(sprintf('Artikel Karier %02d', $n), [
                'excerpt' => "Ringkasan artikel karier nomor {$n}.",
                'content' => "Paragraf pembuka artikel karier nomor {$n}.\n\nParagraf kedua.",
                'published_at' => now()->subDays(20 - $n),
            ]);
        }
        $this->article('Artikel Draf Tersembunyi', ['status' => ArticleStatus::Draft, 'published_at' => null]);
        $latest = $articles[10];

        $this->browse(function (Browser $browser) use ($latest) {
            // Langkah 1: section Artikel Terbaru di beranda.
            $browser->visit('/')
                ->scrollIntoView('@latest-articles')
                ->assertSeeIn('@latest-articles', 'Artikel Terbaru')
                ->assertSeeIn('@see-all-articles', 'Lihat Semua')
                ->assertDontSee('Artikel Draf Tersembunyi');

            $cards = $this->cardTitles($browser, 'latest-article-card');
            $this->assertSame(['Artikel Karier 10', 'Artikel Karier 09', 'Artikel Karier 08'], $cards);

            $browser->assertPresent('[dusk="latest-article-card"] img.article-card-cover')
                ->assertSeeIn('@latest-articles', 'Ringkasan artikel karier nomor 10.')
                ->assertSeeIn('@latest-articles', $latest->published_at->format('d M Y'))
                ->shot('Oktavia/TC.LandingPage.016-step1');

            // Langkah 2: Lihat Semua → /artikel (9 per halaman).
            $browser->clickAndWaitForReload('@see-all-articles')
                ->assertPathIs('/artikel')
                ->assertDontSee('Artikel Draf Tersembunyi')
                ->assertPresent('@pagination .pagination');

            $this->assertCount(9, $this->cardTitles($browser, 'article-card'), '9 artikel per halaman.');
            $browser->shot('Oktavia/TC.LandingPage.016-step2');

            // Langkah 3: klik kartu artikel → detail.
            $browser->clickAndWaitForReload('[dusk="article-card"] h2 a')
                ->assertPathIs("/artikel/{$latest->slug}")
                ->assertSeeIn('@article-title', 'Artikel Karier 10')
                ->assertSeeIn('@article-meta', 'Tim Redaksi AbisLulus')
                ->assertSeeIn('@article-meta', $latest->published_at->format('d M Y'))
                ->assertPresent('@article-cover')
                ->assertSeeIn('@article-content', 'Paragraf pembuka artikel karier nomor 10.')
                ->assertSeeIn('@article-content', 'Paragraf kedua.')
                ->shot('Oktavia/TC.LandingPage.016-step3');
        });
    }

    /**
     * Case ID  : TC.LandingPage.017
     * Test Case: Pengunjung mencoba melihat artikel yang masih berstatus Draf.
     */
    public function test_tc_landing_page_017_artikel_draf_tidak_tampil_publik(): void
    {
        $this->article('Artikel Publik Tayang');
        $draft = $this->article('Artikel Draf', ['status' => ArticleStatus::Draft, 'published_at' => null]);
        $this->assertSame('artikel-draf', $draft->slug);

        $this->browse(function (Browser $browser) {
            // Langkah 1: beranda & daftar artikel.
            $browser->visit('/')
                ->assertSeeIn('@latest-articles', 'Artikel Publik Tayang')
                ->assertDontSeeIn('@latest-articles', 'Artikel Draf')
                ->shot('Oktavia/TC.LandingPage.017-step1-beranda')
                ->visit('/artikel')
                ->assertSee('Artikel Publik Tayang')
                ->assertDontSee('Artikel Draf')
                ->shot('Oktavia/TC.LandingPage.017-step1-artikel');

            // Langkah 2: akses langsung slug draf.
            $browser->visit('/artikel/artikel-draf')
                ->assertSee('404')
                ->assertSourceHas('Not Found')
                ->shot('Oktavia/TC.LandingPage.017-step2');
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

    /**
     * @return list<string>
     */
    private function headers(Browser $browser, string $table): array
    {
        return $browser->script(
            "return Array.from(document.querySelectorAll('[dusk=\"{$table}\"] thead th')).map(function (th) { return th.textContent.trim(); });"
        )[0];
    }

    private function rowCount(Browser $browser, string $prefix): int
    {
        return $browser->script("return document.querySelectorAll('[dusk^=\"{$prefix}\"]').length;")[0];
    }

    /**
     * @return list<list<string>>
     */
    private function rows(Browser $browser): array
    {
        return $browser->script(
            "return Array.from(document.querySelectorAll('[dusk^=\"article-row-\"]')).map(function (tr) {"
            ."return Array.from(tr.cells).map(function (td) { return td.textContent.replace(/\\s+/g, ' ').trim(); }); });"
        )[0];
    }

    /**
     * Judul artikel di tabel panel (baris pertama sel Judul).
     *
     * @return list<string>
     */
    private function titles(Browser $browser): array
    {
        return $browser->script(
            "return Array.from(document.querySelectorAll('[dusk^=\"article-row-\"] td:nth-child(2) .fw-semibold')).map(function (el) { return el.textContent.trim(); });"
        )[0];
    }

    /**
     * @return list<string>
     */
    private function cardTitles(Browser $browser, string $dusk): array
    {
        return $browser->script(
            "return Array.from(document.querySelectorAll('[dusk=\"{$dusk}\"] h2 a, [dusk=\"{$dusk}\"] h3 a')).map(function (a) { return a.textContent.trim(); });"
        )[0];
    }
}
