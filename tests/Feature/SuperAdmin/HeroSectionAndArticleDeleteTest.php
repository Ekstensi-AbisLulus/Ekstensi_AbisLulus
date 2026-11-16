<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Article;
use App\Models\HeroSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HeroSectionAndArticleDeleteTest extends TestCase
{
    use RefreshDatabase;

    private const ONE_PIXEL_PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    private static function fakePng(string $name = 'gambar.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            (string) base64_decode(self::ONE_PIXEL_PNG_BASE64, true)
        );
    }

    public function test_index_pages_render_bootstrap_delete_confirmation_modals_for_hero_and_article(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $hero = HeroSection::factory()->create([
            'super_admin_id' => $superAdmin->id,
            'title' => 'Banner Karier Utama',
        ]);

        $article = Article::factory()->published()->create([
            'super_admin_id' => $superAdmin->id,
            'title' => 'Tips Wawancara Pertama',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.hero-sections.index'))
            ->assertOk()
            ->assertSee('data-bs-target="#deleteHeroModal-'.$hero->id.'"', false)
            ->assertSee("Hapus hero 'Banner Karier Utama'? Tindakan ini tidak dapat dibatalkan.", false)
            ->assertSee('data-bs-dismiss="modal"', false);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.articles.index'))
            ->assertOk()
            ->assertSee('data-bs-target="#deleteArticleModal-'.$article->id.'"', false)
            ->assertSee("Hapus artikel 'Tips Wawancara Pertama'? Tindakan ini tidak dapat dibatalkan.", false)
            ->assertSee('data-bs-dismiss="modal"', false);
    }

    public function test_super_admin_can_delete_hero_section_and_its_stored_image(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $imagePath = self::fakePng('hero-hapus.png')->store('hero', 'public');

        $hero = HeroSection::factory()->create([
            'super_admin_id' => $superAdmin->id,
            'title' => 'Hero Akan Dihapus',
            'image_url' => $imagePath,
            'is_active' => true,
        ]);

        Storage::disk('public')->assertExists($imagePath);

        $response = $this->actingAs($superAdmin)
            ->delete(route('super-admin.hero-sections.destroy', $hero));

        $response->assertRedirect(route('super-admin.hero-sections.index'))
            ->assertSessionHas('success', 'Data berhasil dihapus.');

        $this->assertDatabaseMissing('hero_sections', ['id' => $hero->id]);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_super_admin_can_delete_article_and_its_stored_cover_image(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $coverPath = self::fakePng('sampul-hapus.png')->store('articles', 'public');

        $article = Article::factory()->published()->create([
            'super_admin_id' => $superAdmin->id,
            'title' => 'Artikel Akan Dihapus',
            'cover_image_url' => $coverPath,
        ]);

        Storage::disk('public')->assertExists($coverPath);

        $response = $this->actingAs($superAdmin)
            ->delete(route('super-admin.articles.destroy', $article));

        $response->assertRedirect(route('super-admin.articles.index'))
            ->assertSessionHas('success', 'Data berhasil dihapus.');

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        Storage::disk('public')->assertMissing($coverPath);
    }

    public function test_super_admin_can_delete_article_without_cover_image(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $article = Article::factory()->draft()->create([
            'super_admin_id' => $superAdmin->id,
            'cover_image_url' => null,
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('super-admin.articles.destroy', $article))
            ->assertRedirect(route('super-admin.articles.index'))
            ->assertSessionHas('success', 'Data berhasil dihapus.');

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    public function test_landing_page_falls_back_to_default_hero_after_last_active_hero_is_deleted(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $imagePath = self::fakePng('hero-terakhir.png')->store('hero', 'public');

        $lastActiveHero = HeroSection::factory()->create([
            'super_admin_id' => $superAdmin->id,
            'title' => 'Satu-satunya Hero Aktif',
            'image_url' => $imagePath,
            'is_active' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Satu-satunya Hero Aktif');

        $this->actingAs($superAdmin)
            ->delete(route('super-admin.hero-sections.destroy', $lastActiveHero))
            ->assertRedirect(route('super-admin.hero-sections.index'))
            ->assertSessionHas('success', 'Data berhasil dihapus.');

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Satu-satunya Hero Aktif')
            ->assertSee('Siap Melangkah Setelah Lulus Bersama')
            ->assertSee('AbisLulus');
    }

    public function test_admin_and_user_are_forbidden_and_guest_is_redirected_when_deleting_hero_or_article(): void
    {
        $imagePath = self::fakePng('hero-aman.png')->store('hero', 'public');
        $coverPath = self::fakePng('sampul-aman.png')->store('articles', 'public');

        $hero = HeroSection::factory()->create(['image_url' => $imagePath]);
        $article = Article::factory()->published()->create(['cover_image_url' => $coverPath]);

        $this->delete(route('super-admin.hero-sections.destroy', $hero))->assertRedirect('/login');
        $this->delete(route('super-admin.articles.destroy', $article))->assertRedirect('/login');

        foreach ([User::factory()->create(), User::factory()->admin()->create()] as $unauthorizedUser) {
            $this->actingAs($unauthorizedUser)
                ->delete(route('super-admin.hero-sections.destroy', $hero))
                ->assertForbidden();

            $this->actingAs($unauthorizedUser)
                ->delete(route('super-admin.articles.destroy', $article))
                ->assertForbidden();
        }

        $this->assertDatabaseHas('hero_sections', ['id' => $hero->id]);
        $this->assertDatabaseHas('articles', ['id' => $article->id]);
        Storage::disk('public')->assertExists($imagePath);
        Storage::disk('public')->assertExists($coverPath);
    }
}
