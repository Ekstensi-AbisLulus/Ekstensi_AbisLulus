<?php

namespace Tests\Feature\Admin;

use App\Models\LearningMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MaterialCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_and_regular_user_cannot_access_create_material(): void
    {
        $this->get(route('admin.materials.create'))
            ->assertRedirect('/login');

        $this->post(route('admin.materials.store'), [])
            ->assertRedirect('/login');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.materials.create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.materials.store'), [
                'title' => 'Materi Baru',
                'description' => 'Deskripsi materi.',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ])
            ->assertForbidden();
    }

    public function test_admin_and_super_admin_can_open_create_material_form(): void
    {
        $admin = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.materials.create'))
            ->assertOk()
            ->assertSee('Tambah Materi Pembelajaran Baru')
            ->assertSee('Link Video');

        $this->actingAs($superAdmin)
            ->get(route('admin.materials.create'))
            ->assertOk()
            ->assertSee('Tambah Materi Pembelajaran Baru');
    }

    public function test_admin_can_create_video_material_with_link_and_save_to_database(): void
    {
        $admin = User::factory()->admin()->create();

        $payload = [
            'title' => 'Persiapan Wawancara Kerja',
            'description' => 'Panduan lengkap menjawab pertanyaan wawancara HRD dengan metode STAR.',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'thumbnail_url' => 'https://example.com/images/wawancara.jpg',
            'is_published' => '1',
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.materials.store'), $payload);

        $response->assertRedirect(route('admin.materials.index'))
            ->assertSessionHas('success', 'Materi pembelajaran berhasil ditambahkan.');

        $this->assertDatabaseHas('learning_materials', [
            'admin_id' => $admin->id,
            'title' => 'Persiapan Wawancara Kerja',
            'description' => 'Panduan lengkap menjawab pertanyaan wawancara HRD dengan metode STAR.',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'thumbnail_url' => 'https://example.com/images/wawancara.jpg',
            'is_published' => true,
        ]);
    }

    public function test_admin_can_create_material_with_uploaded_thumbnail_and_unpublished_status(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $pngBytes = (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $file = UploadedFile::fake()->createWithContent('thumbnail.png', $pngBytes);

        $response = $this->actingAs($admin)
            ->post(route('admin.materials.store'), [
                'title' => 'Dasar Microsoft Excel',
                'description' => 'Belajar rumus dasar Excel untuk dunia kerja.',
                'video_url' => 'https://drive.google.com/file/d/123456/view',
                'thumbnail' => $file,
                'is_published' => '0',
            ]);

        $response->assertRedirect(route('admin.materials.index'))
            ->assertSessionHas('success');

        $material = LearningMaterial::firstOrFail();

        $this->assertSame($admin->id, $material->admin_id);
        $this->assertSame('Dasar Microsoft Excel', $material->title);
        $this->assertFalse($material->is_published);
        $this->assertNotNull($material->thumbnail_url);
        Storage::disk('public')->assertExists($material->thumbnail_url);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidMaterialDataProvider(): array
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
            'link video bukan url valid' => [array_merge($valid, ['video_url' => 'bukan-url']), 'video_url'],
            'thumbnail url bukan url valid' => [array_merge($valid, ['thumbnail_url' => 'tidak-valid']), 'thumbnail_url'],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    #[DataProvider('invalidMaterialDataProvider')]
    public function test_store_material_validation_errors(array $payload, string $errorField): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.materials.create'))
            ->post(route('admin.materials.store'), $payload)
            ->assertRedirect(route('admin.materials.create'))
            ->assertSessionHasErrors($errorField);

        $this->assertDatabaseCount('learning_materials', 0);
    }
}
