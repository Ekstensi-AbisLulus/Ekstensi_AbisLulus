<?php

namespace Tests\Feature;

use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialReadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_can_view_created_materials_in_index_and_detail(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Aqeel']);

        $this->actingAs($admin)->post(route('admin.materials.store'), [
            'title' => 'Materi Video Pembuatan CV',
            'description' => 'Panduan menyusun CV profesional untuk fresh graduate.',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_published' => '1',
        ])->assertRedirect(route('admin.materials.index'));

        $material = LearningMaterial::firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.materials.index'))
            ->assertOk()
            ->assertSee('Materi Video Pembuatan CV')
            ->assertSee('Admin Aqeel')
            ->assertSee('Dipublikasikan');

        $this->actingAs($admin)
            ->get(route('admin.materials.show', $material))
            ->assertOk()
            ->assertSee('Materi Video Pembuatan CV')
            ->assertSee('Panduan menyusun CV profesional untuk fresh graduate.')
            ->assertSee('https://www.youtube.com/embed/dQw4w9WgXcQ');
    }

    public function test_admin_can_search_materials_by_title(): void
    {
        $admin = User::factory()->admin()->create();

        LearningMaterial::factory()->for($admin, 'admin')->create(['title' => 'Belajar Microsoft Excel']);
        LearningMaterial::factory()->for($admin, 'admin')->create(['title' => 'Teknik Wawancara Kerja']);

        $this->actingAs($admin)
            ->get(route('admin.materials.index', ['search' => 'Excel']))
            ->assertOk()
            ->assertSee('Belajar Microsoft Excel')
            ->assertDontSee('Teknik Wawancara Kerja');
    }

    public function test_regular_user_and_guest_can_read_published_materials_created_by_admin(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Mentor Karir']);
        $user = User::factory()->create();

        $published = LearningMaterial::factory()->for($admin, 'admin')->create([
            'title' => 'Strategi Lolos Interview HRD',
            'description' => 'Tips dan trik menjawab pertanyaan interview HRD.',
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'is_published' => true,
        ]);

        $unpublished = LearningMaterial::factory()->for($admin, 'admin')->unpublished()->create([
            'title' => 'Materi Draf Rahasia',
        ]);

        Quiz::factory()->create([
            'material_id' => $published->id,
            'created_by' => $admin->id,
            'title' => 'Kuis Interview HRD',
        ]);

        // Guest dapat melihat daftar materi published
        $this->get(route('materials.index'))
            ->assertOk()
            ->assertSee('Strategi Lolos Interview HRD')
            ->assertSee('Mulai Belajar')
            ->assertDontSee('Materi Draf Rahasia');

        // User biasa dapat melihat daftar & mencari materi
        $this->actingAs($user)
            ->get(route('materials.index', ['search' => 'Interview']))
            ->assertOk()
            ->assertSee('Strategi Lolos Interview HRD');

        // User biasa dapat melihat halaman detail materi beserta video embed & tombol Mulai Kuis
        $this->actingAs($user)
            ->get(route('materials.show', $published))
            ->assertOk()
            ->assertSee('Strategi Lolos Interview HRD')
            ->assertSee('Tips dan trik menjawab pertanyaan interview HRD.')
            ->assertSee('https://www.youtube.com/embed/dQw4w9WgXcQ')
            ->assertSee('Mulai Kuis');

        // Materi yang belum dipublikasikan (draf) tidak bisa diakses user biasa (404)
        $this->actingAs($user)
            ->get(route('materials.show', $unpublished))
            ->assertNotFound();
    }
}
