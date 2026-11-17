<?php

namespace Tests\Feature\Admin;

use App\Models\LearningMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MaterialUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_edit_form_loads_existing_data_and_confirmation_modals(): void
    {
        $admin = User::factory()->admin()->create();
        $material = LearningMaterial::factory()->for($admin, 'admin')->create([
            'title' => 'Materi Lama',
            'description' => 'Deskripsi lama yang akan diubah.',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.materials.edit', $material))
            ->assertOk()
            ->assertSee('Materi Lama')
            ->assertSee('Deskripsi lama yang akan diubah.')
            ->assertSee('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->assertSee('id="confirmSaveModal"', false)
            ->assertSee('Konfirmasi Simpan Perubahan')
            ->assertSee('id="confirmCancelModal"', false)
            ->assertSee('Batalkan Perubahan?');
    }

    public function test_admin_can_update_own_material_and_changes_persist_in_database(): void
    {
        $admin = User::factory()->admin()->create();
        $material = LearningMaterial::factory()->for($admin, 'admin')->create([
            'title' => 'Judul Awal',
            'description' => 'Deskripsi awal.',
            'video_url' => 'https://www.youtube.com/watch?v=aaaaaaaaaaa',
            'thumbnail_url' => 'https://example.com/old.jpg',
            'is_published' => true,
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.materials.update', $material), [
                'title' => 'Judul Materi Diperbarui',
                'description' => 'Deskripsi materi yang sudah diperbarui.',
                'video_url' => 'https://www.youtube.com/watch?v=bbbbbbbbbbb',
                'thumbnail_url' => 'https://example.com/new.jpg',
                'is_published' => '0',
            ]);

        $response->assertRedirect(route('admin.materials.index'))
            ->assertSessionHas('success', 'Materi pembelajaran berhasil diperbarui.');

        $this->assertDatabaseHas('learning_materials', [
            'id' => $material->id,
            'admin_id' => $admin->id,
            'title' => 'Judul Materi Diperbarui',
            'description' => 'Deskripsi materi yang sudah diperbarui.',
            'video_url' => 'https://www.youtube.com/watch?v=bbbbbbbbbbb',
            'thumbnail_url' => 'https://example.com/new.jpg',
            'is_published' => false,
        ]);
    }

    public function test_updating_uploaded_thumbnail_replaces_old_stored_file(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $pngBytes = (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        $oldPath = UploadedFile::fake()->createWithContent('old.png', $pngBytes)->store('materials', 'public');

        $material = LearningMaterial::factory()->for($admin, 'admin')->create([
            'thumbnail_url' => $oldPath,
        ]);

        $newFile = UploadedFile::fake()->createWithContent('new.png', $pngBytes);

        $this->actingAs($admin)
            ->put(route('admin.materials.update', $material), [
                'title' => $material->title,
                'description' => $material->description,
                'video_url' => $material->video_url,
                'thumbnail' => $newFile,
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.materials.index'));

        $material->refresh();

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($material->thumbnail_url);
    }

    public function test_other_admin_cannot_update_material_owned_by_another_admin_but_super_admin_can(): void
    {
        $ownerAdmin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $material = LearningMaterial::factory()->for($ownerAdmin, 'admin')->create([
            'title' => 'Materi Milik Owner',
        ]);

        $this->actingAs($otherAdmin)
            ->get(route('admin.materials.edit', $material))
            ->assertForbidden();

        $this->actingAs($otherAdmin)
            ->put(route('admin.materials.update', $material), [
                'title' => 'Diubah Admin Lain',
                'description' => 'Deskripsi.',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ])
            ->assertForbidden();

        $this->actingAs($superAdmin)
            ->put(route('admin.materials.update', $material), [
                'title' => 'Diubah Super Admin',
                'description' => 'Deskripsi diperbarui oleh Super Admin.',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.materials.index'));

        $this->assertDatabaseHas('learning_materials', [
            'id' => $material->id,
            'title' => 'Diubah Super Admin',
        ]);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidUpdateDataProvider(): array
    {
        $valid = [
            'title' => 'Judul Valid',
            'description' => 'Deskripsi valid.',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ];

        return [
            'judul kosong' => [array_merge($valid, ['title' => '']), 'title'],
            'judul > 200 karakter' => [array_merge($valid, ['title' => str_repeat('a', 201)]), 'title'],
            'deskripsi kosong' => [array_merge($valid, ['description' => '']), 'description'],
            'link video kosong' => [array_merge($valid, ['video_url' => '']), 'video_url'],
            'link video tidak valid' => [array_merge($valid, ['video_url' => 'bukan-url']), 'video_url'],
            'thumbnail url tidak valid' => [array_merge($valid, ['thumbnail_url' => 'bukan-url']), 'thumbnail_url'],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    #[DataProvider('invalidUpdateDataProvider')]
    public function test_update_material_validation_errors(array $payload, string $errorField): void
    {
        $admin = User::factory()->admin()->create();
        $material = LearningMaterial::factory()->for($admin, 'admin')->create([
            'title' => 'Judul Tetap',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.materials.edit', $material))
            ->put(route('admin.materials.update', $material), $payload)
            ->assertRedirect(route('admin.materials.edit', $material))
            ->assertSessionHasErrors($errorField);

        $this->assertSame('Judul Tetap', $material->fresh()->title);
    }
}
