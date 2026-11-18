<?php

namespace Tests\Browser\Aqeel;

use App\Models\LearningMaterial;
use App\Models\LearningReview;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Concerns\CreatesTestUsers;
use Tests\DuskTestCase;

/**
 * PBI-11 — Lihat & Cari Materi (Admin & Publik) (TC.Material.008–012).
 */
#[Group('aqeel')]
class MaterialReadTest extends DuskTestCase
{
    use CreatesTestUsers, DatabaseMigrations;

    /**
     * Case ID  : TC.Material.008
     * Test Case: Admin melihat daftar materi pembelajaran di halaman Manajemen E-Learning
     *            serta melihat halaman detail materi.
     */
    public function test_tc_material_008_daftar_dan_detail_materi_admin(): void
    {
        $admin = $this->makeAdmin(['name' => 'Admin Aqeel']);

        foreach (range(1, 10) as $n) {
            $this->material($admin, sprintf('Materi Pendukung %02d', $n), ['is_published' => $n % 2 === 0]);
        }

        $target = $this->material($admin, 'Persiapan Wawancara Kerja', [
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'description' => 'Panduan lengkap menjawab pertanyaan wawancara HRD dengan metode STAR.',
            'thumbnail_url' => 'https://example.com/images/wawancara.jpg',
        ]);
        $this->review($target, 4);
        $this->review($target, 5);
        Quiz::factory()->create(['created_by' => $admin->id, 'material_id' => $target->id]);

        $draft = LearningMaterial::where('title', 'Materi Pendukung 09')->firstOrFail();

        $this->browse(function (Browser $browser) use ($target, $draft, $admin) {
            // Langkah 1: buka menu Manajemen E-Learning.
            $browser->loginAs($admin)
                ->visit('/admin/dashboard')
                ->clickAndWaitForReload('.panel-sidebar a[href$="/admin/materials"]')
                ->assertPathIs('/admin/materials')
                ->assertSee('Daftar Materi Pembelajaran Video');

            $this->assertSame(['', 'Thumbnail', 'Judul', 'Pembuat', 'Tanggal Unggah', 'Aksi'], $this->headers($browser));
            $this->assertSame(10, $this->rowCount($browser), 'Maksimal 10 materi per halaman.');

            $row = "@material-row-{$target->id}";
            $browser->assertPresent("{$row} input[type=\"checkbox\"]")
                ->assertAttribute("{$row} img", 'src', 'https://example.com/images/wawancara.jpg')
                ->assertSeeIn($row, 'Persiapan Wawancara Kerja')
                ->assertSeeIn($row, 'Dipublikasikan')
                ->assertSeeIn($row, 'Admin Aqeel')
                ->assertSeeIn($row, $target->created_at->format('d M Y'))
                ->assertSeeIn("@material-row-{$draft->id}", 'Draf')
                ->assertAttribute("@view-{$target->id}", 'title', 'Lihat Detail')
                ->assertAttribute("@edit-{$target->id}", 'title', 'Ubah Materi')
                ->assertAttribute("@delete-{$target->id}", 'title', 'Hapus Materi')
                ->assertPresent('@pagination .pagination')
                ->assertPresent('@pagination a[href*="page=2"]')
                ->shot('Aqeel/TC.Material.008-step1');

            // Langkah 2: buka detail materi.
            $browser->clickAndWaitForReload("@view-{$target->id}")
                ->assertPathIs("/admin/materials/{$target->id}")
                ->assertSee('Persiapan Wawancara Kerja')
                ->assertSeeIn('@status-badge', 'Dipublikasikan')
                ->assertSeeIn('@summary', 'Admin Aqeel')
                ->assertAttribute('@video-embed', 'src', 'https://www.youtube.com/embed/dQw4w9WgXcQ')
                ->assertSeeIn('@description', 'Panduan lengkap menjawab pertanyaan wawancara HRD dengan metode STAR.')
                ->assertAttribute('@thumbnail', 'src', 'https://example.com/images/wawancara.jpg')
                ->assertSeeIn('@summary', '4.5')
                ->assertSeeIn('@summary', '(2 ulasan)')
                ->assertSeeIn('@summary', '1 kuis')
                ->shot('Aqeel/TC.Material.008-step2');
        });
    }

    /**
     * Case ID  : TC.Material.009
     * Test Case: Admin mencari materi pembelajaran berdasarkan kata kunci judul di halaman Manajemen E-Learning.
     */
    public function test_tc_material_009_pencarian_materi_admin(): void
    {
        $admin = $this->makeAdmin();
        $this->material($admin, 'Belajar Excel Dasar');
        $this->material($admin, 'Excel Lanjutan untuk Data');
        $this->material($admin, 'Public Speaking Efektif');

        $this->browse(function (Browser $browser) use ($admin) {
            // Langkah 1: cari "Excel".
            $browser->loginAs($admin)
                ->visit('/admin/materials')
                ->assertMissing('@search-reset')
                ->type('@search-input', 'Excel')
                ->clickAndWaitForReload('@search-submit')
                ->assertQueryStringHas('search', 'Excel')
                ->assertVisible('@search-reset');

            $this->assertSame(['Excel Lanjutan untuk Data', 'Belajar Excel Dasar'], $this->titles($browser));
            $browser->shot('Aqeel/TC.Material.009-step1');

            // Langkah 2: reset.
            $browser->clickAndWaitForReload('@search-reset')
                ->assertPathIs('/admin/materials')
                ->assertInputValue('@search-input', '')
                ->assertMissing('@search-reset');

            $this->assertSame(
                ['Public Speaking Efektif', 'Excel Lanjutan untuk Data', 'Belajar Excel Dasar'],
                $this->titles($browser)
            );
            $browser->shot('Aqeel/TC.Material.009-step2');
        });
    }

    /**
     * Case ID  : TC.Material.010
     * Test Case: Admin mencari materi dengan kata kunci yang tidak cocok dengan judul materi mana pun.
     */
    public function test_tc_material_010_pencarian_tidak_ditemukan(): void
    {
        $admin = $this->makeAdmin();
        $this->material($admin, 'Belajar Excel Dasar');

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/materials')
                ->type('@search-input', ' KataKunciTidakAda123 ')
                ->clickAndWaitForReload('@search-submit')
                ->assertMissing('@materials-table')
                ->assertSeeIn('@empty-state', 'Materi tidak ditemukan')
                ->assertSeeIn('@empty-state', 'Tidak ada materi dengan judul yang memuat "KataKunciTidakAda123".')
                ->assertSeeIn('@show-all-button', 'Tampilkan Semua Materi')
                ->shot('Aqeel/TC.Material.010-step1');
        });
    }

    /**
     * Case ID  : TC.Material.011
     * Test Case: Guest dan User biasa melihat daftar materi yang dipublikasikan, mencari materi,
     *            serta menonton video di halaman detail materi.
     */
    public function test_tc_material_011_daftar_dan_detail_materi_publik(): void
    {
        $admin = $this->makeAdmin(['name' => 'Mentor Aqeel']);
        $user = $this->makeUser();

        $interview = $this->material($admin, 'Tips Interview Kerja', [
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'description' => 'Cara menjawab pertanyaan interview dengan percaya diri.',
            'thumbnail_url' => 'https://example.com/images/interview.jpg',
        ]);
        $this->material($admin, 'Dasar Microsoft Excel', ['description' => 'Rumus dasar Excel untuk pemula.']);
        $this->material($admin, 'Materi Draf Rahasia', ['is_published' => false]);

        $reviewer = $this->makeUser(['name' => 'Peserta Uji']);
        $this->review($interview, 5, $reviewer, 'Sangat membantu persiapan interview.');
        Quiz::factory()->create(['created_by' => $admin->id, 'material_id' => $interview->id]);

        $this->browse(function (Browser $browser) use ($interview, $user) {
            // Langkah 1 (Guest): hanya materi dipublikasikan yang tampil.
            $browser->visit('/e-learning')
                ->assertSee('Tips Interview Kerja')
                ->assertSee('Dasar Microsoft Excel')
                ->assertDontSee('Materi Draf Rahasia')
                ->assertPresent('img[alt="Thumbnail Tips Interview Kerja"]')
                ->assertSee('Mentor Aqeel')
                ->assertSee('5.0')
                ->assertSee('Cara menjawab pertanyaan interview dengan percaya diri.')
                ->assertSeeIn("@start-button-{$interview->id}", 'Mulai Belajar');

            $this->assertSame(2, $this->cardCount($browser));
            $browser->shot('Aqeel/TC.Material.011-step1-guest');

            // Langkah 2: cari "Interview".
            $browser->type('@search-input', 'Interview')
                ->clickAndWaitForReload('@search-submit')
                ->assertSee('Tips Interview Kerja')
                ->assertDontSee('Dasar Microsoft Excel');

            $this->assertSame(1, $this->cardCount($browser));
            $browser->shot('Aqeel/TC.Material.011-step2');

            // Langkah 3: Mulai Belajar → detail materi.
            $browser->clickAndWaitForReload("@start-button-{$interview->id}")
                ->assertPathIs("/e-learning/{$interview->id}")
                ->assertAttribute('@video-embed', 'src', 'https://www.youtube.com/embed/dQw4w9WgXcQ')
                ->assertSeeIn('@title', 'Tips Interview Kerja')
                ->assertSeeIn('@meta', 'Mentor Aqeel')
                ->assertSeeIn('@meta', $interview->created_at->format('d M Y'))
                ->assertSeeIn('@meta', '5.0')
                ->assertSeeIn('@meta', '(1 ulasan)')
                ->assertSeeIn('@quiz-button', 'Mulai Kuis')
                ->assertSeeIn('@description', 'Cara menjawab pertanyaan interview dengan percaya diri.')
                ->assertSeeIn('@reviews', 'Peserta Uji')
                ->assertSeeIn('@reviews', 'Sangat membantu persiapan interview.')
                ->shot('Aqeel/TC.Material.011-step3');

            // Langkah 1 (User biasa): materi draf tetap tersembunyi.
            $browser->loginAs($user)
                ->visit('/e-learning')
                ->assertSee('Tips Interview Kerja')
                ->assertDontSee('Materi Draf Rahasia');

            $this->assertSame(2, $this->cardCount($browser));
            $browser->shot('Aqeel/TC.Material.011-step1-user');
        });
    }

    /**
     * Case ID  : TC.Material.012
     * Test Case: User biasa atau Guest mencoba mengakses URL detail materi yang masih berstatus Draf.
     */
    public function test_tc_material_012_akses_materi_draf_ditolak(): void
    {
        $admin = $this->makeAdmin();
        $user = $this->makeUser();
        $draft = $this->material($admin, 'Materi Draf Rahasia', ['is_published' => false]);

        $this->browse(function (Browser $browser) use ($draft, $user) {
            $browser->visit("/e-learning/{$draft->id}")
                ->assertSee('404')
                ->assertSourceHas('Not Found')
                ->assertDontSee('Materi Draf Rahasia')
                ->shot('Aqeel/TC.Material.012-step1-guest');

            $browser->loginAs($user)
                ->visit("/e-learning/{$draft->id}")
                ->assertSee('404')
                ->assertSourceHas('Not Found')
                ->assertDontSee('Materi Draf Rahasia')
                ->shot('Aqeel/TC.Material.012-step1-user');
        });
    }

    // --- Helper ---------------------------------------------------------------

    private function material(User $admin, string $title, array $attributes = []): LearningMaterial
    {
        return LearningMaterial::factory()->create(
            $attributes + ['admin_id' => $admin->id, 'title' => $title, 'thumbnail_url' => null, 'is_published' => true]
        );
    }

    private function review(LearningMaterial $material, int $rating, ?User $user = null, ?string $text = null): LearningReview
    {
        return LearningReview::factory()->create([
            'material_id' => $material->id,
            'user_id' => ($user ?? $this->makeUser([], null))->id,
            'rating' => $rating,
            'review_text' => $text,
        ]);
    }

    /**
     * @return list<string>
     */
    private function headers(Browser $browser): array
    {
        return $browser->script(
            "return Array.from(document.querySelectorAll('[dusk=\"materials-table\"] thead th')).map(function (th) { return th.textContent.trim(); });"
        )[0];
    }

    private function rowCount(Browser $browser): int
    {
        return $browser->script("return document.querySelectorAll('[dusk^=\"material-row-\"]').length;")[0];
    }

    /**
     * Judul materi di tabel admin, berurutan dari atas.
     *
     * @return list<string>
     */
    private function titles(Browser $browser): array
    {
        return $browser->script(
            "return Array.from(document.querySelectorAll('[dusk^=\"material-row-\"] td:nth-child(3) .fw-semibold a')).map(function (a) { return a.textContent.trim(); });"
        )[0];
    }

    private function cardCount(Browser $browser): int
    {
        return $browser->script("return document.querySelectorAll('[dusk=\"material-card\"]').length;")[0];
    }
}
