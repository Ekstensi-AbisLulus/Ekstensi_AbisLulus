<?php

namespace Tests\Feature\Admin;

use App\Models\LearningMaterial;
use App\Models\LearningReview;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaterialDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_index_and_show_render_delete_confirmation_modal_for_owner_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $material = LearningMaterial::factory()->for($admin, 'admin')->create([
            'title' => 'Materi Akan Dihapus',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.materials.index'))
            ->assertOk()
            ->assertSee('id="deleteMaterialModal-'.$material->id.'"', false)
            ->assertSee('Konfirmasi Hapus Materi')
            ->assertSee('Materi Akan Dihapus');

        $this->actingAs($admin)
            ->get(route('admin.materials.show', $material))
            ->assertOk()
            ->assertSee('id="deleteMaterialModal"', false)
            ->assertSee('Konfirmasi Hapus Materi');
    }

    public function test_admin_can_delete_own_material_and_cascade_cleanup_runs(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $pngBytes = (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $thumbnailPath = UploadedFile::fake()->createWithContent('thumb.png', $pngBytes)->store('materials', 'public');

        $material = LearningMaterial::factory()->for($admin, 'admin')->create([
            'title' => 'Materi yang Dihapus',
            'thumbnail_url' => $thumbnailPath,
        ]);

        $review = LearningReview::factory()->for($material, 'material')->create();
        $quiz = Quiz::factory()->create([
            'material_id' => $material->id,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.materials.destroy', $material));

        $response->assertRedirect(route('admin.materials.index'))
            ->assertSessionHas('success', 'Materi pembelajaran berhasil dihapus.');

        $this->assertDatabaseMissing('learning_materials', ['id' => $material->id]);
        $this->assertModelMissing($review);
        $this->assertNull($quiz->fresh()->material_id);
        Storage::disk('public')->assertMissing($thumbnailPath);
    }

    public function test_other_admin_cannot_delete_material_owned_by_another_admin_but_super_admin_can(): void
    {
        $ownerAdmin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $material = LearningMaterial::factory()->for($ownerAdmin, 'admin')->create([
            'title' => 'Materi Milik Owner',
        ]);

        $this->actingAs($otherAdmin)
            ->delete(route('admin.materials.destroy', $material))
            ->assertForbidden();

        $this->assertDatabaseHas('learning_materials', ['id' => $material->id]);

        $this->actingAs($superAdmin)
            ->delete(route('admin.materials.destroy', $material))
            ->assertRedirect(route('admin.materials.index'))
            ->assertSessionHas('success', 'Materi pembelajaran berhasil dihapus.');

        $this->assertDatabaseMissing('learning_materials', ['id' => $material->id]);
    }
}
