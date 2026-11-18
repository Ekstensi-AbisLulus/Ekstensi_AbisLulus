<?php

namespace Tests\Browser\Fulca;

use App\Enums\EventCategory;
use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Models\CareerEvent;
use App\Models\EventRegistration;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Concerns\CreatesTestUsers;
use Tests\DuskTestCase;

/**
 * PBI-06 — Melihat Riwayat Aktivitas E-Learning & Kegiatan (TC.UserProfile.015–021).
 */
#[Group('fulca')]
class UserActivityHistoryTest extends DuskTestCase
{
    use CreatesTestUsers, DatabaseMigrations;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->makeAdmin(['name' => 'Mentor AbisLulus']);
    }

    /**
     * Case ID  : TC.UserProfile.015
     * Test Case: Pengguna melihat riwayat kuis yang telah dikerjakan beserta nilainya.
     */
    public function test_tc_user_profile_015_riwayat_e_learning(): void
    {
        $user = $this->makeUser();

        $materialA = $this->material('Persiapan Wawancara Kerja');
        $materialB = $this->material('Dasar Microsoft Excel');
        $quizA = $this->quiz('Kuis Wawancara', $materialA);
        $quizB = $this->quiz('Kuis Excel', $materialB);
        $quizC = $this->quiz('Kuis Tanpa Materi');

        // Kuis A dikerjakan dua kali: hanya percobaan terakhir (nilai 80) yang tampil.
        $this->attempt($user, $quizA, 20, 1, 4, now()->subDays(5));
        $latestA = $this->attempt($user, $quizA, 80, 4, 1, now()->subDay());
        $attemptB = $this->attempt($user, $quizB, 60, 3, 2, now()->subDays(2));
        $attemptC = $this->attempt($user, $quizC, 40, 2, 3, now()->subDays(3));

        $this->browse(function (Browser $browser) use ($user, $materialA, $latestA, $attemptB, $attemptC) {
            // Langkah 1: klik menu Aktivitas E-Learning.
            $browser->loginAs($user)
                ->visit('/profil')
                ->clickAndWaitForReload('@nav-learning')
                ->assertPathIs('/profil/riwayat/e-learning')
                ->assertSee('Aktivitas E-Learning')
                ->assertAttributeContains('@nav-learning', 'class', 'active');

            $this->assertSame(['Materi', 'Kuis', 'Nilai', 'Benar/Salah', 'Tanggal selesai'], $this->headers($browser));

            $rows = $this->rows($browser);
            $this->assertCount(3, $rows, 'Setiap kuis hanya tampil satu kali (percobaan terakhir).');
            $this->assertSame(['Kuis Wawancara', 'Kuis Excel', 'Kuis Tanpa Materi'], array_column($rows, 1));

            $this->assertSame(
                ['Persiapan Wawancara Kerja', 'Kuis Wawancara', '80', '4/1', $this->indoDate($latestA->completed_at)],
                $rows[0]
            );
            $this->assertSame('Materi tidak tersedia', $rows[2][0]);

            $browser->assertPresent("@attempt-row-{$latestA->id} td:first-child span, @attempt-row-{$latestA->id} td:first-child img")
                ->shot('Fulca/TC.UserProfile.015-step1');

            // Langkah 2: warna badge nilai.
            $browser->assertAttributeContains("@attempt-row-{$latestA->id} @score-badge", 'class', 'text-bg-success')
                ->assertAttributeContains("@attempt-row-{$attemptB->id} @score-badge", 'class', 'text-bg-warning')
                ->assertAttributeContains("@attempt-row-{$attemptC->id} @score-badge", 'class', 'text-bg-danger')
                ->shot('Fulca/TC.UserProfile.015-step2');

            // Langkah 3: klik judul materi.
            $browser->clickAndWaitForReload("@attempt-row-{$latestA->id} @material-link")
                ->assertPathIs("/e-learning/{$materialA->id}")
                ->assertSee('Persiapan Wawancara Kerja')
                ->shot('Fulca/TC.UserProfile.015-step3');
        });
    }

    /**
     * Case ID  : TC.UserProfile.016
     * Test Case: Pengguna melihat daftar kegiatan Career Mentoring yang telah didaftarkan beserta link meeting.
     */
    public function test_tc_user_profile_016_riwayat_kegiatan_dan_link_meeting(): void
    {
        $user = $this->makeUser();
        $start = now()->addDays(5)->setTime(13, 0);

        $event = $this->event('Webinar Persiapan Karier', [
            'start_at' => $start,
            'end_at' => $start->copy()->addHours(2),
            'category' => EventCategory::Hardskill,
            'meeting_url' => 'https://zoom.us/j/1234567890',
        ]);
        $registration = $this->register($user, $event, RegistrationStatus::Registered, now()->subDay());
        $event->refresh();

        $this->browse(function (Browser $browser) use ($user, $event, $registration) {
            // Langkah 1: klik menu Aktivitas Kegiatan.
            $browser->loginAs($user)
                ->visit('/profil')
                ->clickAndWaitForReload('@nav-events')
                ->assertPathIs('/profil/riwayat/kegiatan')
                ->assertSee('Aktivitas Kegiatan')
                ->assertAttributeContains('@nav-events', 'class', 'active');

            $this->assertSame(['Sampul', 'Judul kegiatan', 'Kategori', 'Jadwal', 'Status', 'Tautan'], $this->headers($browser));

            $schedule = $event->start_at->locale('id')->translatedFormat('d F Y, H:i')
                .'–'.$event->end_at->locale('id')->translatedFormat('H:i');
            $row = $this->rows($browser)[0];

            $this->assertSame(['', 'Webinar Persiapan Karier', 'Hard Skill', $schedule, 'Terdaftar', 'Buka Link'], $row);

            $browser->assertAttributeContains("@registration-row-{$registration->id} .badge", 'class', 'text-bg-primary')
                ->shot('Fulca/TC.UserProfile.016-step1');

            // Langkah 2: tombol Buka Link membuka link meeting di tab baru.
            $browser->assertAttribute("@registration-row-{$registration->id} @meeting-link", 'href', 'https://zoom.us/j/1234567890')
                ->assertAttribute("@registration-row-{$registration->id} @meeting-link", 'target', '_blank')
                ->assertAttributeContains("@registration-row-{$registration->id} @meeting-link", 'rel', 'noopener')
                ->shot('Fulca/TC.UserProfile.016-step2');
        });
    }

    /**
     * Case ID  : TC.UserProfile.017
     * Test Case: Pengguna melihat riwayat kegiatan yang pendaftarannya dibatalkan atau kegiatannya
     *            sudah selesai/dibatalkan.
     */
    public function test_tc_user_profile_017_link_meeting_tidak_tampil_untuk_pendaftaran_tidak_aktif(): void
    {
        $user = $this->makeUser();

        $inactive = [
            'pendaftaran-dibatalkan' => $this->register(
                $user, $this->event('Kegiatan Pendaftaran Dibatalkan'), RegistrationStatus::Cancelled, now()->subDays(1)
            ),
            'kegiatan-selesai' => $this->register(
                $user, $this->event('Kegiatan Sudah Selesai', ['status' => EventStatus::Completed]), RegistrationStatus::Registered, now()->subDays(2)
            ),
            'kegiatan-dibatalkan' => $this->register(
                $user, $this->event('Kegiatan Dibatalkan Penyelenggara', ['status' => EventStatus::Cancelled]), RegistrationStatus::Registered, now()->subDays(3)
            ),
            'kegiatan-lewat' => $this->register(
                $user, $this->event('Kegiatan Sudah Lewat', [
                    'start_at' => now()->subDays(3)->setTime(9, 0),
                    'end_at' => now()->subDays(3)->setTime(11, 0),
                ]), RegistrationStatus::Registered, now()->subDays(10)
            ),
        ];

        $this->browse(function (Browser $browser) use ($user, $inactive) {
            $browser->loginAs($user)->visit('/profil/riwayat/kegiatan');

            foreach ($inactive as $label => $registration) {
                $browser->assertSeeIn("@registration-row-{$registration->id} @no-meeting-link", '—')
                    ->assertMissing("@registration-row-{$registration->id} @meeting-link");
            }

            $browser->assertSeeIn("@registration-row-{$inactive['pendaftaran-dibatalkan']->id}", 'Dibatalkan')
                ->assertMissing('@meeting-link')
                ->assertDontSee('Buka Link')
                ->shot('Fulca/TC.UserProfile.017-step1');
        });
    }

    /**
     * Case ID  : TC.UserProfile.018
     * Test Case: Pengguna baru yang belum pernah mengerjakan kuis maupun mendaftar kegiatan membuka halaman riwayat.
     */
    public function test_tc_user_profile_018_empty_state(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user) {
            // Langkah 1: Aktivitas E-Learning.
            $browser->loginAs($user)
                ->visit('/profil/riwayat/e-learning')
                ->assertMissing('@history-table')
                ->assertSeeIn('@history-empty', 'Belum ada riwayat aktivitas')
                ->assertSeeIn('@explore-button', 'Jelajahi E-Learning')
                ->shot('Fulca/TC.UserProfile.018-step1')
                ->clickAndWaitForReload('@explore-button')
                ->assertPathIs('/e-learning');

            // Langkah 2: Aktivitas Kegiatan.
            $browser->visit('/profil/riwayat/kegiatan')
                ->assertMissing('@history-table')
                ->assertSeeIn('@history-empty', 'Belum ada riwayat aktivitas')
                ->assertSeeIn('@explore-button', 'Jelajahi Kegiatan')
                ->shot('Fulca/TC.UserProfile.018-step2')
                ->clickAndWaitForReload('@explore-button')
                ->assertPathIs('/kegiatan');
        });
    }

    /**
     * Case ID  : TC.UserProfile.019
     * Test Case: Pengguna mencoba melihat riwayat aktivitas milik pengguna lain.
     */
    public function test_tc_user_profile_019_batasan_data_antar_pengguna(): void
    {
        $userA = $this->makeUser(['name' => 'User A']);
        $userB = $this->makeUser(['name' => 'User B']);

        $this->attempt($userA, $this->quiz('Kuis Milik User A', $this->material('Materi A')), 90, 9, 1, now()->subDay());
        $this->attempt($userB, $this->quiz('Kuis Milik User B', $this->material('Materi B')), 50, 5, 5, now()->subDay());
        $this->register($userA, $this->event('Kegiatan Milik User A'), RegistrationStatus::Registered, now()->subDay());
        $this->register($userB, $this->event('Kegiatan Milik User B'), RegistrationStatus::Registered, now()->subDay());

        $this->browse(function (Browser $browser) use ($userA) {
            $browser->loginAs($userA)
                ->visit('/profil/riwayat/e-learning')
                ->assertSeeIn('@history-table', 'Kuis Milik User A')
                ->assertDontSee('Kuis Milik User B');

            $this->assertCount(1, $this->rows($browser));
            $browser->shot('Fulca/TC.UserProfile.019-step1-e-learning')
                ->visit('/profil/riwayat/kegiatan')
                ->assertSeeIn('@history-table', 'Kegiatan Milik User A')
                ->assertDontSee('Kegiatan Milik User B');

            $this->assertCount(1, $this->rows($browser));
            $browser->shot('Fulca/TC.UserProfile.019-step1-kegiatan');
        });
    }

    /**
     * Case ID  : TC.UserProfile.020
     * Test Case: Pengguna memiliki lebih dari 10 data riwayat.
     */
    public function test_tc_user_profile_020_pagination(): void
    {
        $user = $this->makeUser();

        foreach (range(1, 12) as $n) {
            $this->attempt($user, $this->quiz(sprintf('Kuis Uji %02d', $n)), 75, 3, 1, now()->subDays(20 - $n));
            $this->register($user, $this->event(sprintf('Kegiatan Uji %02d', $n)), RegistrationStatus::Registered, now()->subDays(20 - $n));
        }

        $this->browse(function (Browser $browser) use ($user) {
            foreach (['e-learning', 'kegiatan'] as $page) {
                $browser->loginAs($user)->visit("/profil/riwayat/{$page}");

                $this->assertCount(10, $this->rows($browser), "Halaman {$page} maksimal 10 data.");
                $browser->assertPresent('.pagination')
                    ->assertVisible('.d-sm-flex .pagination a[href*="page=2"]')
                    ->shot("Fulca/TC.UserProfile.020-step1-{$page}")
                    ->clickAndWaitForReload('.d-sm-flex .pagination a[href*="page=2"]')
                    ->assertQueryStringHas('page', '2');

                $this->assertCount(2, $this->rows($browser), "Halaman 2 {$page} berisi sisa 2 data.");
                $browser->shot("Fulca/TC.UserProfile.020-step1-{$page}-halaman-2");
            }
        });
    }

    /**
     * Case ID  : TC.UserProfile.021
     * Test Case: Guest atau Admin mencoba mengakses halaman riwayat aktivitas pengguna.
     */
    public function test_tc_user_profile_021_otorisasi_halaman_riwayat(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $urls = ['e-learning' => '/profil/riwayat/e-learning', 'kegiatan' => '/profil/riwayat/kegiatan'];

        $this->browse(function (Browser $browser) use ($superAdmin, $urls) {
            foreach ($urls as $label => $url) {
                $browser->visit($url)
                    ->assertPathIs('/login')
                    ->shot("Fulca/TC.UserProfile.021-step1-guest-{$label}");
            }

            foreach (['admin' => $this->admin, 'super-admin' => $superAdmin] as $role => $account) {
                foreach ($urls as $label => $url) {
                    $browser->loginAs($account)
                        ->visit($url)
                        ->assertPathIs($url)
                        ->assertSee('403')
                        ->assertSourceHas('Anda tidak memiliki akses ke halaman ini.')
                        ->assertDontSee('Belum ada riwayat aktivitas')
                        ->shot("Fulca/TC.UserProfile.021-step2-{$role}-{$label}");
                }
            }
        });
    }

    // --- Helper data -----------------------------------------------------------

    private function material(string $title): LearningMaterial
    {
        return LearningMaterial::factory()->create([
            'admin_id' => $this->admin->id,
            'title' => $title,
            'thumbnail_url' => null,
            'is_published' => true,
        ]);
    }

    private function quiz(string $title, ?LearningMaterial $material = null): Quiz
    {
        return Quiz::factory()->create([
            'created_by' => $this->admin->id,
            'material_id' => $material?->id,
            'title' => $title,
        ]);
    }

    private function attempt(User $user, Quiz $quiz, float $score, int $correct, int $incorrect, CarbonInterface $completedAt): QuizAttempt
    {
        return QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'correct_count' => $correct,
            'incorrect_count' => $incorrect,
            'started_at' => $completedAt->copy()->subMinutes(15),
            'completed_at' => $completedAt->copy()->startOfMinute(),
        ])->fresh();
    }

    private function event(string $title, array $attributes = []): CareerEvent
    {
        return CareerEvent::factory()->create($attributes + [
            'admin_id' => $this->admin->id,
            'title' => $title,
            'thumbnail_url' => null,
            'status' => EventStatus::Published,
        ]);
    }

    private function register(User $user, CareerEvent $event, RegistrationStatus $status, CarbonInterface $registeredAt): EventRegistration
    {
        return EventRegistration::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => $status,
            'registered_at' => $registeredAt,
        ]);
    }

    // --- Helper tabel ----------------------------------------------------------

    private function indoDate(CarbonInterface $date): string
    {
        return $date->locale('id')->translatedFormat('d F Y, H:i');
    }

    /**
     * @return list<string>
     */
    private function headers(Browser $browser): array
    {
        return $browser->script(
            "return Array.from(document.querySelectorAll('[dusk=\"history-table\"] thead th')).map(function (th) { return th.textContent.trim(); });"
        )[0];
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
