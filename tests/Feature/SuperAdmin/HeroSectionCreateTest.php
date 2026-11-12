<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\HeroSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HeroSectionCreateTest extends TestCase
{
    use RefreshDatabase;

    private const ONE_PIXEL_PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    private static function fakePng(string $name = 'hero.png', int $extraKilobytes = 0): UploadedFile
    {
        $bytes = (string) base64_decode(self::ONE_PIXEL_PNG_BASE64, true);
        if ($extraKilobytes > 0) {
            $bytes .= str_repeat("\0", $extraKilobytes * 1024);
        }

        return UploadedFile::fake()->createWithContent($name, $bytes);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Siap Melangkah Setelah Lulus',
            'subtitle' => 'Belajar gratis lewat E-Learning dan ikuti Career Mentoring bersama praktisi.',
            'image' => self::fakePng('hero.png'),
            'target_url' => 'https://abislulus.test/e-learning',
            'is_active' => '1',
        ], $overrides);
    }

    public function test_super_admin_can_open_create_hero_section_form(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->get(route('super-admin.hero-sections.create'))
            ->assertOk()
            ->assertSee('Tambah Hero Section')
            ->assertSee('Buat Banner Hero Baru')
            ->assertSee('name="title"', false)
            ->assertSee('name="subtitle"', false)
            ->assertSee('name="image"', false)
            ->assertSee('name="target_url"', false)
            ->assertSee('name="is_active"', false);
    }

    public function test_super_admin_can_store_hero_section_in_public_hero_storage(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->post(route('super-admin.hero-sections.store'), $this->validData());

        $response->assertRedirect(route('super-admin.hero-sections.index'))
            ->assertSessionHas('success', 'Hero section berhasil ditambahkan.');

        $hero = HeroSection::firstOrFail();
        $this->assertSame($superAdmin->id, $hero->super_admin_id);
        $this->assertSame('Siap Melangkah Setelah Lulus', $hero->title);
        $this->assertSame('Belajar gratis lewat E-Learning dan ikuti Career Mentoring bersama praktisi.', $hero->subtitle);
        $this->assertSame('https://abislulus.test/e-learning', $hero->target_url);
        $this->assertTrue($hero->is_active);
        $this->assertStringStartsWith('hero/', $hero->image_url);

        Storage::disk('public')->assertExists($hero->image_url);
        $this->assertStringContainsString('/storage/'.$hero->image_url, $hero->imageSrc());
    }

    public function test_super_admin_can_store_inactive_hero_section_with_internal_target_url(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->post(route('super-admin.hero-sections.store'), $this->validData([
                'subtitle' => '   ',
                'target_url' => '/e-learning',
                'is_active' => '0',
            ]))
            ->assertRedirect(route('super-admin.hero-sections.index'));

        $hero = HeroSection::firstOrFail();
        $this->assertNull($hero->subtitle);
        $this->assertSame('/e-learning', $hero->target_url);
        $this->assertFalse($hero->is_active);
    }

    public function test_hero_section_defaults_to_active_when_is_active_is_omitted(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $payload = $this->validData();
        unset($payload['is_active']);

        $this->actingAs($superAdmin)
            ->post(route('super-admin.hero-sections.store'), $payload)
            ->assertRedirect(route('super-admin.hero-sections.index'));

        $this->assertTrue(HeroSection::firstOrFail()->is_active);
    }

    public function test_admin_and_user_are_forbidden_and_guest_is_redirected(): void
    {
        $this->get(route('super-admin.hero-sections.create'))->assertRedirect('/login');
        $this->post(route('super-admin.hero-sections.store'), $this->validData())->assertRedirect('/login');

        foreach ([User::factory()->create(), User::factory()->admin()->create()] as $unauthorizedUser) {
            $this->actingAs($unauthorizedUser)
                ->get(route('super-admin.hero-sections.create'))
                ->assertForbidden();

            $this->actingAs($unauthorizedUser)
                ->post(route('super-admin.hero-sections.store'), $this->validData())
                ->assertForbidden();
        }

        $this->assertDatabaseCount('hero_sections', 0);
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function invalidData(): array
    {
        return [
            'judul kosong' => [
                ['title' => '   '],
                'title',
                'Judul hero wajib diisi.',
            ],
            'judul > 200 karakter' => [
                ['title' => str_repeat('a', 201)],
                'title',
                'Judul hero maksimal 200 karakter.',
            ],
            'gambar wajib' => [
                ['image' => null],
                'image',
                'Gambar hero wajib diunggah.',
            ],
            'file bukan gambar' => [
                ['image' => UploadedFile::fake()->create('dokumen.pdf', 200, 'application/pdf')],
                'image',
                'File yang diunggah harus berupa gambar.',
            ],
            'ukuran gambar > 3 MB' => [
                ['image' => self::fakePng('besar.png', 3100)],
                'image',
                'Ukuran gambar maksimal 3 MB.',
            ],
            'target url tidak valid' => [
                ['target_url' => 'bukan-url-valid'],
                'target_url',
                'Target URL harus berupa URL yang valid.',
            ],
            'target url skema berbahaya' => [
                ['target_url' => 'javascript:alert(1)'],
                'target_url',
                'Target URL harus berupa URL yang valid.',
            ],
            'is_active bukan boolean' => [
                ['is_active' => 'bukan-boolean'],
                'is_active',
                'Status aktif tidak valid.',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidData')]
    public function test_hero_section_validation(array $overrides, string $field, string $message): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->from(route('super-admin.hero-sections.create'))
            ->post(route('super-admin.hero-sections.store'), $this->validData($overrides))
            ->assertRedirect(route('super-admin.hero-sections.create'))
            ->assertSessionHasErrors([$field => $message]);

        $this->assertDatabaseCount('hero_sections', 0);
    }

    public function test_invalid_field_is_marked_for_accessibility_on_hero_form(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->followingRedirects()
            ->from(route('super-admin.hero-sections.create'))
            ->post(route('super-admin.hero-sections.store'), $this->validData(['title' => '']))
            ->assertSee('Judul hero wajib diisi.')
            ->assertSee('aria-invalid="true" aria-describedby="title-error"', false)
            ->assertSee('id="title-error"', false);
    }
}
