<?php

namespace Tests\Feature\Admin;

use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Models\CareerEvent;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * PBI-09 Riwayat aktivitas admin (PRD §7 F3).
 */
class ActivityHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $otherAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->admin = User::factory()->admin()->withProfile()->create(['name' => 'Admin Satu']);
        $this->otherAdmin = User::factory()->admin()->withProfile()->create(['name' => 'Admin Dua']);
    }

    /**
     * Satu materi, kuis, dan event untuk masing-masing admin.
     */
    private function seedBothAdmins(): void
    {
        foreach ([[$this->admin, 'Milik Saya'], [$this->otherAdmin, 'Milik Orang Lain']] as [$owner, $suffix]) {
            LearningMaterial::factory()->create(['admin_id' => $owner->id, 'title' => "Materi {$suffix}"]);
            Quiz::factory()->create(['created_by' => $owner->id, 'title' => "Kuis {$suffix}"]);
            CareerEvent::factory()->create(['admin_id' => $owner->id, 'title' => "Event {$suffix}"]);
        }
    }

    // --- Akses ---

    public function test_regular_user_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/riwayat')->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/riwayat')->assertRedirect('/login');
    }

    public function test_page_uses_panel_layout_with_active_menu(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/riwayat')
            ->assertOk()
            ->assertSee('Riwayat Aktivitas')
            ->assertSeeInOrder(['E-Learning', 'Kuis', 'Kegiatan'])
            ->assertSee('class="nav-link active" href="'.route('admin.history').'"', false);
    }

    // --- Kepemilikan data ---

    /**
     * @return array<string, array{string, string}>
     */
    public static function tabs(): array
    {
        return [
            'tab e-learning' => ['materials', 'Materi'],
            'tab kuis' => ['quizzes', 'Kuis'],
            'tab kegiatan' => ['events', 'Event'],
        ];
    }

    #[DataProvider('tabs')]
    public function test_admin_only_sees_own_data(string $tab, string $prefix): void
    {
        $this->seedBothAdmins();

        $this->actingAs($this->admin)
            ->get('/admin/riwayat?tab='.$tab)
            ->assertOk()
            ->assertSee("{$prefix} Milik Saya")
            ->assertDontSee("{$prefix} Milik Orang Lain")
            ->assertDontSee('Pembuat');
    }

    #[DataProvider('tabs')]
    public function test_super_admin_sees_all_data_with_creator_column(string $tab, string $prefix): void
    {
        $this->seedBothAdmins();
        $superAdmin = User::factory()->superAdmin()->withProfile()->create();

        $this->actingAs($superAdmin)
            ->get('/admin/riwayat?tab='.$tab)
            ->assertOk()
            ->assertSee('Pembuat')
            ->assertSee("{$prefix} Milik Saya")
            ->assertSee("{$prefix} Milik Orang Lain")
            ->assertSee('Admin Satu')
            ->assertSee('Admin Dua');
    }

    public function test_tab_counts_only_include_own_data(): void
    {
        $this->seedBothAdmins();
        LearningMaterial::factory()->count(2)->create(['admin_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get('/admin/riwayat')
            ->assertViewHas('counts', fn ($counts) => $counts->all() === ['materials' => 3, 'quizzes' => 1, 'events' => 1]);
    }

    // --- Empty state ---

    #[DataProvider('tabs')]
    public function test_empty_state_is_shown_when_no_data(string $tab): void
    {
        // Data admin lain tidak boleh "mengisi" riwayat admin ini.
        LearningMaterial::factory()->create(['admin_id' => $this->otherAdmin->id]);

        $this->actingAs($this->admin)
            ->get('/admin/riwayat?tab='.$tab)
            ->assertOk()
            ->assertSee('Belum ada riwayat aktivitas')
            ->assertSee('bi-inbox', false)
            ->assertDontSee('<table', false);
    }

    public function test_unknown_tab_falls_back_to_e_learning(): void
    {
        LearningMaterial::factory()->create(['admin_id' => $this->admin->id, 'title' => 'Materi Default']);

        $this->actingAs($this->admin)
            ->get('/admin/riwayat?tab=hacker')
            ->assertOk()
            ->assertViewHas('tab', 'materials')
            ->assertSee('Materi Default');
    }

    // --- Isi kolom ---

    public function test_material_columns(): void
    {
        LearningMaterial::factory()->create(['admin_id' => $this->admin->id, 'title' => 'Materi Draf', 'is_published' => false, 'thumbnail_url' => null]);
        LearningMaterial::factory()->create(['admin_id' => $this->admin->id, 'title' => 'Materi Tayang', 'thumbnail_url' => 'thumbnails/a.jpg']);

        $this->actingAs($this->admin)
            ->get('/admin/riwayat')
            ->assertSee('Dipublikasikan')
            ->assertSee('Draf')
            ->assertSee('Terakhir Diubah')
            ->assertSee(asset('storage/thumbnails/a.jpg'), false)
            ->assertSee('history-thumb-empty', false);
    }

    public function test_quiz_columns_show_material_and_counts(): void
    {
        $material = LearningMaterial::factory()->create(['admin_id' => $this->admin->id, 'title' => 'Materi Induk']);
        $quiz = Quiz::factory()->create(['created_by' => $this->admin->id, 'material_id' => $material->id, 'title' => 'Kuis Bermateri']);
        Quiz::factory()->create(['created_by' => $this->admin->id, 'material_id' => null, 'title' => 'Kuis Mandiri']);

        foreach (range(1, 3) as $n) {
            $quiz->questions()->create(['question_text' => "Soal {$n}", 'sort_order' => $n]);
        }

        foreach (User::factory()->count(2)->create() as $user) {
            QuizAttempt::create(['user_id' => $user->id, 'quiz_id' => $quiz->id, 'started_at' => now()]);
        }

        $response = $this->actingAs($this->admin)->get('/admin/riwayat?tab=quizzes')->assertOk();

        $response->assertSee('Materi Induk');
        $response->assertViewHas('items', function ($items) {
            $byTitle = collect($items->items())->keyBy('title');

            return $byTitle['Kuis Bermateri']->questions_count === 3
                && $byTitle['Kuis Bermateri']->attempts_count === 2
                && $byTitle['Kuis Mandiri']->material === null;
        });
        $response->assertSeeInOrder(['Kuis Mandiri', '<td class="text-secondary">-</td>'], false);
    }

    public function test_event_columns_show_status_and_registrants_excluding_cancelled(): void
    {
        $event = CareerEvent::factory()->create([
            'admin_id' => $this->admin->id,
            'title' => 'Event Ramai',
            'status' => EventStatus::Cancelled,
            'capacity' => 30,
        ]);

        $users = User::factory()->count(3)->create();
        foreach ([RegistrationStatus::Registered, RegistrationStatus::Attended, RegistrationStatus::Cancelled] as $i => $status) {
            $event->registrations()->create(['user_id' => $users[$i]->id, 'status' => $status, 'registered_at' => now()]);
        }

        $this->actingAs($this->admin)
            ->get('/admin/riwayat?tab=events')
            ->assertSee('Dibatalkan')
            ->assertSee('text-bg-danger', false)
            ->assertViewHas('items', fn ($items) => $items->first()->registrants_count === 2)
            ->assertSee('/ 30');
    }

    public function test_edit_buttons_link_to_module_edit_routes(): void
    {
        $material = LearningMaterial::factory()->create(['admin_id' => $this->admin->id]);
        $quiz = Quiz::factory()->create(['created_by' => $this->admin->id]);
        $event = CareerEvent::factory()->create(['admin_id' => $this->admin->id]);

        $this->actingAs($this->admin)->get('/admin/riwayat')
            ->assertSee(route('admin.materials.edit', $material), false);
        $this->actingAs($this->admin)->get('/admin/riwayat?tab=quizzes')
            ->assertSee(route('admin.quizzes.edit', $quiz), false);
        $this->actingAs($this->admin)->get('/admin/riwayat?tab=events')
            ->assertSee(route('admin.events.edit', $event), false);
    }

    public function test_edit_button_is_disabled_when_route_does_not_exist(): void
    {
        $material = LearningMaterial::factory()->create(['admin_id' => $this->admin->id]);

        $html = (string) $this->blade(
            '@include("admin.history.partials.edit-button", ["route" => "admin.belum-ada.edit", "model" => $material, "label" => "X"])',
            ['material' => $material],
        );

        $this->assertStringContainsString('Segera tersedia', $html);
        $this->assertStringContainsString('disabled', $html);
        $this->assertStringNotContainsString('href=', $html);
    }

    // --- Urutan, pagination, pencarian ---

    public function test_sorted_by_latest_update_and_paginated_by_ten(): void
    {
        foreach (range(1, 12) as $n) {
            LearningMaterial::factory()->create([
                'admin_id' => $this->admin->id,
                'title' => sprintf('Materi Urut %02d', $n),
                'updated_at' => now()->subDays(20 - $n),
            ]);
        }

        $this->actingAs($this->admin)
            ->get('/admin/riwayat')
            ->assertSeeInOrder(['Materi Urut 12', 'Materi Urut 11', 'Materi Urut 03'])
            ->assertDontSee('Materi Urut 02')
            ->assertSee('Menampilkan 1–10 dari 12')
            ->assertSee('page=2', false);

        $this->actingAs($this->admin)
            ->get('/admin/riwayat?page=2')
            ->assertSeeInOrder(['Materi Urut 02', 'Materi Urut 01'])
            ->assertDontSee('Materi Urut 03');
    }

    public function test_pagination_keeps_tab_and_search_in_query_string(): void
    {
        Quiz::factory()->count(11)->create(['created_by' => $this->admin->id, 'title' => 'Kuis CV']);

        $this->actingAs($this->admin)
            ->get('/admin/riwayat?tab=quizzes&q=CV')
            ->assertSee('tab=quizzes&amp;q=CV&amp;page=2', false);
    }

    public function test_search_filters_by_title_within_own_data(): void
    {
        LearningMaterial::factory()->create(['admin_id' => $this->admin->id, 'title' => 'Membuat CV Menarik']);
        LearningMaterial::factory()->create(['admin_id' => $this->admin->id, 'title' => 'Dasar Excel']);
        LearningMaterial::factory()->create(['admin_id' => $this->otherAdmin->id, 'title' => 'CV Milik Admin Lain']);

        $this->actingAs($this->admin)
            ->get('/admin/riwayat?q=cv')
            ->assertSee('Membuat CV Menarik')
            ->assertDontSee('Dasar Excel')
            ->assertDontSee('CV Milik Admin Lain')
            ->assertSee('Reset');

        $this->actingAs($this->admin)
            ->get('/admin/riwayat?q=tidak-ada')
            ->assertSee('Belum ada riwayat aktivitas')
            ->assertSee('Tidak ada e-learning dengan judul “tidak-ada”.');
    }

    // --- Performa ---

    #[DataProvider('tabs')]
    public function test_no_n_plus_one_queries(string $tab): void
    {
        $superAdmin = User::factory()->superAdmin()->withProfile()->create();

        $queriesFor = function () use ($tab, $superAdmin): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($superAdmin)->get('/admin/riwayat?tab='.$tab)->assertOk();
            DB::disableQueryLog();

            return count(DB::getQueryLog());
        };

        $this->createRows(2);
        $few = $queriesFor();

        $this->createRows(10);
        $many = $queriesFor();

        $this->assertSame($few, $many, "Jumlah query bertambah seiring jumlah baris di tab {$tab}.");
    }

    private function createRows(int $count): void
    {
        foreach (range(1, $count) as $n) {
            $owner = User::factory()->admin()->create();
            $material = LearningMaterial::factory()->create(['admin_id' => $owner->id]);
            $quiz = Quiz::factory()->create(['created_by' => $owner->id, 'material_id' => $material->id]);
            $quiz->questions()->create(['question_text' => 'Soal', 'sort_order' => 1]);
            $event = CareerEvent::factory()->create(['admin_id' => $owner->id]);
            $event->registrations()->create(['user_id' => $owner->id, 'status' => RegistrationStatus::Registered, 'registered_at' => now()]);
        }
    }
}
