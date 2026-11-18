<?php

namespace Tests\Browser\Fakhri;

use App\Enums\EventCategory;
use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Models\CareerEvent;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Concerns\CreatesTestUsers;
use Tests\DuskTestCase;

/**
 * PBI-09 — Melihat Riwayat Aktivitas Admin (TC.AdminProfile.019–028).
 */
#[Group('fakhri')]
class AdminActivityHistoryTest extends DuskTestCase
{
    use CreatesTestUsers, DatabaseMigrations;

    /**
     * Case ID  : TC.AdminProfile.019
     * Test Case: Admin melihat riwayat materi E-Learning, kuis, dan kegiatan yang telah dibuatnya.
     */
    public function test_tc_admin_profile_019_lihat_riwayat_aktivitas(): void
    {
        $admin = $this->makeAdmin(['name' => 'Admin Satu']);
        $other = $this->makeAdmin(['name' => 'Admin Dua']);

        $oldMaterial = $this->material($admin, 'Materi Lama Pemrograman', ['updated_at' => now()->subDays(5)]);
        $this->material($admin, 'Materi Baru Wawancara', ['is_published' => false, 'updated_at' => now()->subDay()]);
        $this->material($other, 'Materi Milik Admin Lain');

        $quiz = Quiz::factory()->withQuestions(3, 2)->create([
            'created_by' => $admin->id, 'material_id' => $oldMaterial->id, 'title' => 'Kuis Bermateri',
        ]);
        Quiz::factory()->withQuestions(1, 2)->create(['created_by' => $admin->id, 'title' => 'Kuis Mandiri']);
        Quiz::factory()->create(['created_by' => $other->id, 'title' => 'Kuis Milik Admin Lain']);

        foreach ([$this->makeUser([], null), $this->makeUser([], null)] as $learner) {
            QuizAttempt::create(['user_id' => $learner->id, 'quiz_id' => $quiz->id, 'started_at' => now()]);
        }

        $event = $this->event($admin, 'Kegiatan Ramai', [
            'capacity' => 30, 'category' => EventCategory::Hardskill, 'status' => EventStatus::Published,
        ]);
        foreach ([RegistrationStatus::Registered, RegistrationStatus::Attended, RegistrationStatus::Cancelled] as $status) {
            $event->registrations()->create([
                'user_id' => $this->makeUser([], null)->id, 'status' => $status, 'registered_at' => now(),
            ]);
        }
        $this->event($admin, 'Kegiatan Dibatalkan', [
            'capacity' => null, 'category' => EventCategory::Softskill, 'status' => EventStatus::Cancelled,
        ]);
        $this->event($other, 'Kegiatan Milik Admin Lain');

        $this->browse(function (Browser $browser) use ($admin) {
            // Langkah 1: buka menu Riwayat Aktivitas — tab E-Learning aktif.
            $browser->loginAs($admin)
                ->visit('/admin/profil')
                ->clickAndWaitForReload('.panel-sidebar a[href$="/admin/riwayat"]')
                ->assertPathIs('/admin/riwayat')
                ->assertSee('Riwayat Aktivitas')
                ->assertSeeIn('@history-tab-materials', 'E-Learning')
                ->assertSeeIn('@history-tab-quizzes', 'Kuis')
                ->assertSeeIn('@history-tab-events', 'Kegiatan')
                ->assertSeeIn('@history-count-materials', '2')
                ->assertSeeIn('@history-count-quizzes', '2')
                ->assertSeeIn('@history-count-events', '2')
                ->assertAttributeContains('@history-tab-materials', 'class', 'active');

            $this->assertSame(['Thumbnail', 'Judul', 'Status', 'Dibuat', 'Terakhir Diubah', 'Aksi'], $this->headers($browser));
            $this->assertSame(['Materi Baru Wawancara', 'Materi Lama Pemrograman'], $this->column($browser, 1));
            $this->assertSame(['Draf', 'Dipublikasikan'], $this->column($browser, 2));
            $browser->shot('Fakhri/TC.AdminProfile.019-step1');

            // Langkah 2: tab Kuis.
            $browser->clickAndWaitForReload('@history-tab-quizzes')
                ->assertPathIs('/admin/riwayat')
                ->assertQueryStringHas('tab', 'quizzes')
                ->assertAttributeContains('@history-tab-quizzes', 'class', 'active');

            $this->assertSame(['Judul Kuis', 'Materi Terkait', 'Jumlah Soal', 'Percobaan', 'Dibuat', 'Aksi'], $this->headers($browser));
            $bermateri = $this->row($browser, 'Kuis Bermateri');
            $this->assertSame(['Kuis Bermateri', 'Materi Lama Pemrograman', '3', '2'], array_slice($bermateri, 0, 4));
            $mandiri = $this->row($browser, 'Kuis Mandiri');
            $this->assertSame(['Kuis Mandiri', '-', '1', '0'], array_slice($mandiri, 0, 4));
            $browser->assertDontSee('Kuis Milik Admin Lain')
                ->shot('Fakhri/TC.AdminProfile.019-step2');

            // Langkah 3: tab Kegiatan — pendaftar tidak menghitung yang dibatalkan.
            $browser->clickAndWaitForReload('@history-tab-events')
                ->assertQueryStringHas('tab', 'events')
                ->assertAttributeContains('@history-tab-events', 'class', 'active');

            $this->assertSame(['Sampul', 'Judul', 'Kategori', 'Jadwal Mulai', 'Status', 'Pendaftar', 'Aksi'], $this->headers($browser));
            $ramai = $this->row($browser, 'Kegiatan Ramai');
            $this->assertSame('Hard Skill', $ramai[2]);
            $this->assertSame('Dipublikasikan', $ramai[4]);
            $this->assertSame('2 / 30', preg_replace('/\s+/', ' ', $ramai[5]));
            $batal = $this->row($browser, 'Kegiatan Dibatalkan');
            $this->assertSame('Dibatalkan', $batal[4]);
            $this->assertSame('0', $batal[5]);
            $browser->assertPresent('@history-table .badge.text-bg-danger')
                ->assertDontSee('Kegiatan Milik Admin Lain')
                ->shot('Fakhri/TC.AdminProfile.019-step3');
        });
    }

    /**
     * Case ID  : TC.AdminProfile.020
     * Test Case: Admin membuka halaman edit konten langsung dari tabel riwayat.
     */
    public function test_tc_admin_profile_020_tombol_edit_ke_modul(): void
    {
        $admin = $this->makeAdmin();
        $material = $this->material($admin, 'Materi Untuk Diedit');
        $quiz = Quiz::factory()->create(['created_by' => $admin->id, 'title' => 'Kuis Untuk Diedit']);
        $event = $this->event($admin, 'Kegiatan Untuk Diedit');

        $this->browse(function (Browser $browser) use ($admin, $material, $quiz, $event) {
            // Langkah 1: edit dari tab E-Learning.
            $browser->loginAs($admin)
                ->visit('/admin/riwayat')
                ->assertAttribute('@history-edit', 'href', route('admin.materials.edit', $material))
                ->clickAndWaitForReload('@history-edit')
                ->assertPathIs("/admin/materials/{$material->id}/edit")
                ->shot('Fakhri/TC.AdminProfile.020-step1-elearning');

            // Langkah 2: kembali, lalu edit dari tab Kuis dan Kegiatan.
            $browser->back()
                ->assertPathIs('/admin/riwayat')
                ->clickAndWaitForReload('@history-tab-quizzes')
                ->assertAttribute('@history-edit', 'href', route('admin.quizzes.edit', $quiz))
                ->clickAndWaitForReload('@history-edit')
                ->assertPathIs("/admin/quizzes/{$quiz->id}/edit")
                ->shot('Fakhri/TC.AdminProfile.020-step2-kuis')
                ->visit('/admin/riwayat?tab=events')
                ->assertAttribute('@history-edit', 'href', route('admin.events.edit', $event))
                ->clickAndWaitForReload('@history-edit')
                ->assertPathIs("/admin/events/{$event->id}/edit")
                ->shot('Fakhri/TC.AdminProfile.020-step2-kegiatan');
        });
    }

    /**
     * Case ID  : TC.AdminProfile.021
     * Test Case: Admin mencari riwayat berdasarkan kata kunci judul pada tab yang aktif.
     */
    public function test_tc_admin_profile_021_pencarian_riwayat(): void
    {
        $admin = $this->makeAdmin();
        $other = $this->makeAdmin();

        $this->material($admin, 'Belajar Excel Dasar', ['updated_at' => now()->subDays(3)]);
        $this->material($admin, 'Excel Lanjutan untuk Data', ['updated_at' => now()->subDays(2)]);
        $this->material($admin, 'Public Speaking Efektif', ['updated_at' => now()->subDay()]);
        $this->material($other, 'Excel Milik Admin Lain');

        $this->browse(function (Browser $browser) use ($admin) {
            // Langkah 1: cari "Excel".
            $browser->loginAs($admin)
                ->visit('/admin/riwayat')
                ->assertMissing('@history-search-reset')
                ->type('@history-search-input', 'Excel')
                ->clickAndWaitForReload('@history-search-submit')
                ->assertQueryStringHas('tab', 'materials')
                ->assertQueryStringHas('q', 'Excel')
                ->assertAttributeContains('@history-tab-materials', 'class', 'active')
                ->assertInputValue('@history-search-input', 'Excel')
                ->assertVisible('@history-search-reset');

            $this->assertSame(['Excel Lanjutan untuk Data', 'Belajar Excel Dasar'], $this->column($browser, 1));
            $browser->assertDontSeeIn('@history-table', 'Excel Milik Admin Lain')
                ->shot('Fakhri/TC.AdminProfile.021-step1');

            // Langkah 2: reset.
            $browser->clickAndWaitForReload('@history-search-reset')
                ->assertQueryStringMissing('q')
                ->assertInputValue('@history-search-input', '')
                ->assertMissing('@history-search-reset');

            $this->assertSame(
                ['Public Speaking Efektif', 'Excel Lanjutan untuk Data', 'Belajar Excel Dasar'],
                $this->column($browser, 1)
            );
            $browser->shot('Fakhri/TC.AdminProfile.021-step2');
        });
    }

    /**
     * Case ID  : TC.AdminProfile.022
     * Test Case: Admin mencari dengan kata kunci yang tidak cocok dengan judul mana pun.
     */
    public function test_tc_admin_profile_022_pencarian_tidak_ditemukan(): void
    {
        $admin = $this->makeAdmin();
        $this->material($admin, 'Belajar Excel Dasar');

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/riwayat')
                ->type('@history-search-input', 'KataKunciTidakAda123')
                ->clickAndWaitForReload('@history-search-submit')
                ->assertMissing('@history-table')
                ->assertSeeIn('@history-empty', 'Belum ada riwayat aktivitas')
                ->assertSeeIn('@history-empty', 'Tidak ada e-learning dengan judul “KataKunciTidakAda123”.')
                ->shot('Fakhri/TC.AdminProfile.022-step1');
        });
    }

    /**
     * Case ID  : TC.AdminProfile.023
     * Test Case: Admin baru yang belum membuat konten apa pun membuka halaman Riwayat Aktivitas.
     */
    public function test_tc_admin_profile_023_empty_state(): void
    {
        $admin = $this->makeAdmin();
        $other = $this->makeAdmin();
        // Konten admin lain tidak boleh mengisi riwayat admin baru.
        $this->material($other, 'Materi Admin Lain');
        Quiz::factory()->create(['created_by' => $other->id]);
        $this->event($other, 'Kegiatan Admin Lain');

        $tabs = [
            'materials' => 'E-Learning yang dibuat akan muncul di sini.',
            'quizzes' => 'Kuis yang dibuat akan muncul di sini.',
            'events' => 'Kegiatan yang dibuat akan muncul di sini.',
        ];

        $this->browse(function (Browser $browser) use ($admin, $tabs) {
            $browser->loginAs($admin)->visit('/admin/riwayat');

            foreach ($tabs as $tab => $description) {
                $browser->clickAndWaitForReload("@history-tab-{$tab}")
                    ->assertAttributeContains("@history-tab-{$tab}", 'class', 'active')
                    ->assertMissing('@history-table')
                    ->assertPresent('@history-empty i.bi-inbox')
                    ->assertSeeIn('@history-empty', 'Belum ada riwayat aktivitas')
                    ->assertSeeIn('@history-empty', $description)
                    ->assertSeeIn('@history-count-materials', '0')
                    ->assertSeeIn('@history-count-quizzes', '0')
                    ->assertSeeIn('@history-count-events', '0')
                    ->shot("Fakhri/TC.AdminProfile.023-step1-{$tab}");
            }
        });
    }

    /**
     * Case ID  : TC.AdminProfile.024
     * Test Case: Admin memiliki lebih dari 10 data pada satu tab.
     */
    public function test_tc_admin_profile_024_pagination(): void
    {
        $admin = $this->makeAdmin();

        foreach (range(1, 12) as $n) {
            $this->material($admin, sprintf('Materi Uji %02d', $n), ['updated_at' => now()->subDays(20 - $n)]);
        }
        $this->material($admin, 'Materi Pengantar Karier', ['updated_at' => now()->subDays(30)]);

        $this->browse(function (Browser $browser) use ($admin) {
            // Langkah 1: maksimal 10 data per halaman + navigasi pagination.
            $browser->loginAs($admin)
                ->visit('/admin/riwayat')
                ->assertSeeIn('@history-count-materials', '13')
                ->assertPresent('@history-pagination .pagination')
                ->assertSeeIn('@history-pagination', 'Menampilkan 1–10 dari 13');

            $this->assertCount(10, $this->column($browser, 1));
            $browser->shot('Fakhri/TC.AdminProfile.024-step1');

            // Langkah 2: cari lalu buka halaman ke-2.
            $browser->type('@history-search-input', 'Materi Uji')
                ->clickAndWaitForReload('@history-search-submit')
                ->assertSeeIn('@history-pagination', 'Menampilkan 1–10 dari 12')
                ->clickAndWaitForReload('@history-pagination a[aria-label="Halaman 2"]')
                ->assertQueryStringHas('tab', 'materials')
                ->assertQueryStringHas('q', 'Materi Uji')
                ->assertQueryStringHas('page', '2')
                ->assertInputValue('@history-search-input', 'Materi Uji')
                ->assertAttributeContains('@history-tab-materials', 'class', 'active')
                ->assertSeeIn('@history-pagination', 'Menampilkan 11–12 dari 12');

            $this->assertSame(['Materi Uji 02', 'Materi Uji 01'], $this->column($browser, 1));
            $browser->shot('Fakhri/TC.AdminProfile.024-step2');
        });
    }

    /**
     * Case ID  : TC.AdminProfile.025
     * Test Case: Super Admin membuka halaman Riwayat Aktivitas.
     */
    public function test_tc_admin_profile_025_super_admin_lihat_semua(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $adminSatu = $this->makeAdmin(['name' => 'Admin Satu']);
        $adminDua = $this->makeAdmin(['name' => 'Admin Dua']);

        foreach ([[$adminSatu, 'Satu'], [$adminDua, 'Dua']] as [$owner, $suffix]) {
            $this->material($owner, "Materi Admin {$suffix}");
            Quiz::factory()->create(['created_by' => $owner->id, 'title' => "Kuis Admin {$suffix}"]);
            $this->event($owner, "Kegiatan Admin {$suffix}");
        }

        $tabs = ['materials' => 'Materi', 'quizzes' => 'Kuis', 'events' => 'Kegiatan'];

        $this->browse(function (Browser $browser) use ($superAdmin, $tabs) {
            $browser->loginAs($superAdmin)
                ->visit('/super-admin/dashboard')
                ->clickAndWaitForReload('.panel-sidebar a[href$="/admin/riwayat"]')
                ->assertPathIs('/admin/riwayat');

            foreach ($tabs as $tab => $prefix) {
                $browser->clickAndWaitForReload("@history-tab-{$tab}");

                $headers = $this->headers($browser);
                $this->assertContains('Pembuat', $headers, "Tab {$tab}");

                $creatorColumn = array_search('Pembuat', $headers, true);
                $titleColumn = $tab === 'quizzes' ? 0 : 1;
                $rows = array_combine($this->column($browser, $titleColumn), $this->column($browser, $creatorColumn));

                $this->assertSame('Admin Satu', $rows["{$prefix} Admin Satu"] ?? null, "Tab {$tab}");
                $this->assertSame('Admin Dua', $rows["{$prefix} Admin Dua"] ?? null, "Tab {$tab}");
                $browser->assertSeeIn("@history-count-{$tab}", '2')
                    ->shot("Fakhri/TC.AdminProfile.025-step1-{$tab}");
            }
        });
    }

    /**
     * Case ID  : TC.AdminProfile.026
     * Test Case: Admin mencoba melihat riwayat konten milik admin lain.
     */
    public function test_tc_admin_profile_026_batasan_data_antar_admin(): void
    {
        $adminA = $this->makeAdmin(['name' => 'Admin A']);
        $adminB = $this->makeAdmin(['name' => 'Admin B']);

        $this->material($adminA, 'Materi Admin A Pertama');
        $this->material($adminA, 'Materi Admin A Kedua');
        Quiz::factory()->create(['created_by' => $adminA->id, 'title' => 'Kuis Admin A']);
        $this->event($adminA, 'Kegiatan Admin A');

        $this->material($adminB, 'Materi Rahasia Admin B');
        Quiz::factory()->count(2)->create(['created_by' => $adminB->id, 'title' => 'Kuis Rahasia Admin B']);
        $this->event($adminB, 'Kegiatan Rahasia Admin B');

        $tabs = [
            'materials' => [2, 'Materi Admin A Pertama', 'Materi Rahasia Admin B'],
            'quizzes' => [1, 'Kuis Admin A', 'Kuis Rahasia Admin B'],
            'events' => [1, 'Kegiatan Admin A', 'Kegiatan Rahasia Admin B'],
        ];

        $this->browse(function (Browser $browser) use ($adminA, $tabs) {
            // Langkah 1: tiap tab hanya berisi konten Admin A.
            $browser->loginAs($adminA)->visit('/admin/riwayat');

            foreach ($tabs as $tab => [$count, $ownTitle, $foreignTitle]) {
                $browser->assertSeeIn("@history-count-{$tab}", (string) $count);
            }

            foreach ($tabs as $tab => [$count, $ownTitle, $foreignTitle]) {
                $browser->clickAndWaitForReload("@history-tab-{$tab}")
                    ->assertSeeIn('@history-table', $ownTitle)
                    ->assertDontSee($foreignTitle)
                    ->assertDontSee('Pembuat');

                $this->assertCount($count, $this->column($browser, 1));
                $browser->shot("Fakhri/TC.AdminProfile.026-step1-{$tab}");
            }

            // Langkah 2: cari judul milik Admin B.
            $browser->clickAndWaitForReload('@history-tab-materials')
                ->type('@history-search-input', 'Materi Rahasia Admin B')
                ->clickAndWaitForReload('@history-search-submit')
                ->assertMissing('@history-table')
                ->assertSeeIn('@history-empty', 'Belum ada riwayat aktivitas')
                ->shot('Fakhri/TC.AdminProfile.026-step2');
        });
    }

    /**
     * Case ID  : TC.AdminProfile.027
     * Test Case: Admin mengakses URL riwayat dengan nilai tab yang tidak dikenal.
     */
    public function test_tc_admin_profile_027_parameter_tab_tidak_valid(): void
    {
        $admin = $this->makeAdmin();
        $this->material($admin, 'Materi Tab Default');

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/riwayat?tab=xyz')
                ->assertPathIs('/admin/riwayat')
                ->assertDontSee('Server Error')
                ->assertSee('Riwayat Aktivitas')
                ->assertAttributeContains('@history-tab-materials', 'class', 'active')
                ->assertSeeIn('@history-table', 'Materi Tab Default');

            $this->assertSame(['Thumbnail', 'Judul', 'Status', 'Dibuat', 'Terakhir Diubah', 'Aksi'], $this->headers($browser));
            $browser->shot('Fakhri/TC.AdminProfile.027-step1');
        });
    }

    /**
     * Case ID  : TC.AdminProfile.028
     * Test Case: Guest atau User biasa mencoba mengakses halaman Riwayat Aktivitas Admin.
     */
    public function test_tc_admin_profile_028_otorisasi_halaman_riwayat(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/admin/riwayat')
                ->assertPathIs('/login')
                ->shot('Fakhri/TC.AdminProfile.028-step1-guest');

            $browser->loginAs($user)
                ->visit('/admin/riwayat')
                ->assertPathIs('/admin/riwayat')
                ->assertSee('403')
                ->assertSourceHas('Anda tidak memiliki akses ke halaman ini.')
                ->assertDontSee('Belum ada riwayat aktivitas')
                ->shot('Fakhri/TC.AdminProfile.028-step2-user');
        });
    }

    // --- Helper data & tabel -------------------------------------------------

    private function material(User $admin, string $title, array $attributes = []): LearningMaterial
    {
        return LearningMaterial::factory()->create(
            ['admin_id' => $admin->id, 'title' => $title, 'thumbnail_url' => null] + $attributes
        );
    }

    private function event(User $admin, string $title, array $attributes = []): CareerEvent
    {
        return CareerEvent::factory()->create(
            ['admin_id' => $admin->id, 'title' => $title, 'thumbnail_url' => null] + $attributes
        );
    }

    /**
     * Judul kolom tabel riwayat (textContent, tidak terpengaruh CSS text-transform).
     *
     * @return list<string>
     */
    private function headers(Browser $browser): array
    {
        return $browser->script(
            "return Array.from(document.querySelectorAll('[dusk=\"history-table\"] thead th')).map(function (th) { return th.textContent.trim(); });"
        )[0];
    }

    /**
     * Isi seluruh sel pada satu kolom, berurutan dari atas.
     *
     * @return list<string>
     */
    private function column(Browser $browser, int $index): array
    {
        return array_map(fn (array $cells) => $cells[$index] ?? '', $this->rows($browser));
    }

    /**
     * Sel-sel pada baris yang kolom judulnya sama dengan $title.
     *
     * @return list<string>
     */
    private function row(Browser $browser, string $title): array
    {
        foreach ($this->rows($browser) as $cells) {
            if (in_array($title, $cells, true)) {
                return $cells;
            }
        }

        $this->fail("Baris dengan judul \"{$title}\" tidak ditemukan.");
    }

    /**
     * @return list<list<string>>
     */
    private function rows(Browser $browser): array
    {
        return $browser->script(
            "return Array.from(document.querySelectorAll('[dusk=\"history-table\"] tbody tr')).map(function (tr) {"
            ."return Array.from(tr.cells).map(function (td) { return td.textContent.replace(/\\s+/g, ' ').trim(); }); });"
        )[0];
    }
}
