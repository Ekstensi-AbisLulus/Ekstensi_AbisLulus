<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\HeroSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HeroSectionUpdateTest extends TestCase
{
    use RefreshDatabase;

    private const ONE_PIXEL_PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    private static function fakePng(string $name = 'hero.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            (string) base64_decode(self::ONE_PIXEL_PNG_BASE64, true)
        );
    }

    public function test_super_admin_can_open_edit_hero_section_form_with_prefilled_data_and_preview(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $oldImagePath = self::fakePng('lama.png')->store('hero', 'public');

        $hero = HeroSection::factory()->create([
            'super_admin_id' => $superAdmin->id,
            'title' => 'Judul Hero Lama',
            'subtitle' => 'Subjudul Hero Lama',
            'image_url' => $oldImagePath,
            'target_url' => '/e-learning',
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.hero-sections.edit', $hero))
            ->assertOk()
            ->assertSee('Ubah Hero Section')
            ->assertSee('Judul Hero Lama')
            ->assertSee('Subjudul Hero Lama')
            ->assertSee('/e-learning')
            ->assertSee($hero->imageSrc(), false)
            ->assertSee(route('super-admin.hero-sections.index'), false);
    }

    public function test_super_admin_can_update_hero_section_without_uploading_new_image(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $oldImagePath = self::fakePng('lama.png')->store('hero', 'public');

        $hero = HeroSection::factory()->create([
            'super_admin_id' => $superAdmin->id,
            'title' => 'Judul Lama',
            'subtitle' => 'Subjudul Lama',
            'image_url' => $oldImagePath,
            'target_url' => '/e-learning',
            'is_active' => true,
        ]);

        $response = $this->actingAs($superAdmin)
            ->put(route('super-admin.hero-sections.update', $hero), [
                'title' => 'Judul Baru Setelah Diedit',
                'subtitle' => 'Subjudul Baru Setelah Diedit',
                'target_url' => 'https://abislulus.test/kegiatan',
                'is_active' => '0',
            ]);

        $response->assertRedirect(route('super-admin.hero-sections.index'))
            ->assertSessionHas('success', 'Data berhasil diperbarui.');

        $hero->refresh();
        $this->assertSame('Judul Baru Setelah Diedit', $hero->title);
        $this->assertSame('Subjudul Baru Setelah Diedit', $hero->subtitle);
        $this->assertSame('https://abislulus.test/kegiatan', $hero->target_url);
        $this->assertFalse($hero->is_active);
        $this->assertSame($oldImagePath, $hero->image_url);
        Storage::disk('public')->assertExists($oldImagePath);
    }

    public function test_replacing_hero_section_image_deletes_old_file_from_storage(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $oldImagePath = self::fakePng('lama.png')->store('hero', 'public');

        $hero = HeroSection::factory()->create([
            'super_admin_id' => $superAdmin->id,
            'title' => 'Hero Ganti Gambar',
            'image_url' => $oldImagePath,
            'is_active' => true,
        ]);

        Storage::disk('public')->assertExists($oldImagePath);

        $response = $this->actingAs($superAdmin)
            ->put(route('super-admin.hero-sections.update', $hero), [
                'title' => 'Hero Gambar Baru',
                'subtitle' => 'Subjudul diperbarui',
                'image' => self::fakePng('baru.png'),
                'target_url' => '/kegiatan',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('super-admin.hero-sections.index'))
            ->assertSessionHas('success', 'Data berhasil diperbarui.');

        $hero->refresh();
        $this->assertNotSame($oldImagePath, $hero->image_url);
        $this->assertStringStartsWith('hero/', $hero->image_url);
        Storage::disk('public')->assertMissing($oldImagePath);
        Storage::disk('public')->assertExists($hero->image_url);
    }

    public function test_super_admin_can_quick_toggle_hero_section_active_status(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $hero = HeroSection::factory()->create([
            'super_admin_id' => $superAdmin->id,
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)
            ->patch(route('super-admin.hero-sections.toggle', $hero))
            ->assertRedirect(route('super-admin.hero-sections.index'))
            ->assertSessionHas('success', 'Data berhasil diperbarui.');

        $this->assertFalse($hero->fresh()->is_active);

        $this->actingAs($superAdmin)
            ->patch(route('super-admin.hero-sections.toggle', $hero))
            ->assertRedirect(route('super-admin.hero-sections.index'))
            ->assertSessionHas('success', 'Data berhasil diperbarui.');

        $this->assertTrue($hero->fresh()->is_active);
    }

    public function test_admin_and_user_are_forbidden_and_guest_is_redirected_on_edit_update_and_toggle(): void
    {
        $hero = HeroSection::factory()->create();

        $this->get(route('super-admin.hero-sections.edit', $hero))->assertRedirect('/login');
        $this->put(route('super-admin.hero-sections.update', $hero), ['title' => 'Ubah'])->assertRedirect('/login');
        $this->patch(route('super-admin.hero-sections.toggle', $hero))->assertRedirect('/login');

        foreach ([User::factory()->create(), User::factory()->admin()->create()] as $unauthorizedUser) {
            $this->actingAs($unauthorizedUser)
                ->get(route('super-admin.hero-sections.edit', $hero))
                ->assertForbidden();

            $this->actingAs($unauthorizedUser)
                ->put(route('super-admin.hero-sections.update', $hero), ['title' => 'Ubah'])
                ->assertForbidden();

            $this->actingAs($unauthorizedUser)
                ->patch(route('super-admin.hero-sections.toggle', $hero))
                ->assertForbidden();
        }
    }
}
