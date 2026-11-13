<?php

namespace Tests\Feature\SuperAdmin;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleUpdateTest extends TestCase
{
    use RefreshDatabase;

    private const ONE_PIXEL_PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    private static function fakePng(string $name = 'sampul.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            (string) base64_decode(self::ONE_PIXEL_PNG_BASE64, true)
        );
    }

    public function test_super_admin_can_open_edit_article_form_with_prefilled_data_and_preview(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $oldCoverPath = self::fakePng('sampul-lama.png')->store('articles', 'public');

        $article = Article::factory()->published()->create([
            'super_admin_id' => $superAdmin->id,
            'title' => 'Judul Artikel Awal',
            'slug' => 'judul-artikel-awal',
            'excerpt' => 'Ringkasan artikel awal.',
            'content' => 'Konten pembahasan artikel awal.',
            'cover_image_url' => $oldCoverPath,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.articles.edit', $article))
            ->assertOk()
            ->assertSee('Ubah Artikel')
            ->assertSee('Judul Artikel Awal')
            ->assertSee('judul-artikel-awal')
            ->assertSee('Ringkasan artikel awal.')
            ->assertSee('Konten pembahasan artikel awal.')
            ->assertSee('name="update_slug"', false)
            ->assertSee((string) $article->coverImageSrc(), false)
            ->assertSee(route('super-admin.articles.index'), false);
    }

    public function test_editing_article_title_without_checking_update_slug_keeps_existing_slug(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $article = Article::factory()->published()->create([
            'super_admin_id' => $superAdmin->id,
            'title' => 'Judul Artikel Awal',
            'slug' => 'judul-artikel-awal',
        ]);

        $response = $this->actingAs($superAdmin)
            ->put(route('super-admin.articles.update', $article), [
                'title' => 'Judul Artikel Baru yang Berbeda',
                'excerpt' => 'Ringkasan baru.',
                'content' => 'Konten artikel yang sudah diperbarui.',
                'status' => ArticleStatus::Published->value,
            ]);

        $response->assertRedirect(route('super-admin.articles.index'))
            ->assertSessionHas('success', 'Data berhasil diperbarui.');

        $article->refresh();
        $this->assertSame('Judul Artikel Baru yang Berbeda', $article->title);
        $this->assertSame('judul-artikel-awal', $article->slug);
        $this->assertSame('Ringkasan baru.', $article->excerpt);
        $this->assertSame('Konten artikel yang sudah diperbarui.', $article->content);
    }

    public function test_checking_update_slug_regenerates_unique_slug_from_new_title(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        Article::factory()->published()->create([
            'super_admin_id' => $superAdmin->id,
            'title' => 'Panduan Portofolio Digital',
            'slug' => 'panduan-portofolio-digital',
        ]);

        $article = Article::factory()->published()->create([
            'super_admin_id' => $superAdmin->id,
            'title' => 'Judul Lama',
            'slug' => 'judul-lama',
        ]);

        $this->actingAs($superAdmin)
            ->put(route('super-admin.articles.update', $article), [
                'title' => 'Panduan Portofolio Digital',
                'excerpt' => 'Ringkasan diperbarui.',
                'content' => 'Isi konten diperbarui.',
                'status' => ArticleStatus::Published->value,
                'update_slug' => '1',
            ])
            ->assertRedirect(route('super-admin.articles.index'))
            ->assertSessionHas('success', 'Data berhasil diperbarui.');

        $this->assertSame('panduan-portofolio-digital-2', $article->fresh()->slug);
    }

    public function test_replacing_article_cover_image_deletes_old_file_from_storage(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $oldCoverPath = self::fakePng('sampul-lama.png')->store('articles', 'public');

        $article = Article::factory()->published()->create([
            'super_admin_id' => $superAdmin->id,
            'cover_image_url' => $oldCoverPath,
        ]);

        Storage::disk('public')->assertExists($oldCoverPath);

        $this->actingAs($superAdmin)
            ->put(route('super-admin.articles.update', $article), [
                'title' => $article->title,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'cover_image' => self::fakePng('sampul-baru.png'),
                'status' => ArticleStatus::Published->value,
            ])
            ->assertRedirect(route('super-admin.articles.index'))
            ->assertSessionHas('success', 'Data berhasil diperbarui.');

        $article->refresh();
        $this->assertNotNull($article->cover_image_url);
        $this->assertNotSame($oldCoverPath, $article->cover_image_url);
        Storage::disk('public')->assertMissing($oldCoverPath);
        Storage::disk('public')->assertExists($article->cover_image_url);
    }

    public function test_changing_article_status_from_draft_to_published_sets_published_at_when_empty(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $article = Article::factory()->draft()->create([
            'super_admin_id' => $superAdmin->id,
            'published_at' => null,
        ]);

        $this->assertNull($article->published_at);

        $this->actingAs($superAdmin)
            ->put(route('super-admin.articles.update', $article), [
                'title' => $article->title,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'status' => ArticleStatus::Published->value,
            ])
            ->assertRedirect(route('super-admin.articles.index'))
            ->assertSessionHas('success', 'Data berhasil diperbarui.');

        $article->refresh();
        $this->assertSame(ArticleStatus::Published, $article->status);
        $this->assertNotNull($article->published_at);
    }

    public function test_admin_and_user_are_forbidden_and_guest_is_redirected_on_edit_and_update(): void
    {
        $article = Article::factory()->published()->create();

        $this->get(route('super-admin.articles.edit', $article))->assertRedirect('/login');
        $this->put(route('super-admin.articles.update', $article), ['title' => 'Ubah'])->assertRedirect('/login');

        foreach ([User::factory()->create(), User::factory()->admin()->create()] as $unauthorizedUser) {
            $this->actingAs($unauthorizedUser)
                ->get(route('super-admin.articles.edit', $article))
                ->assertForbidden();

            $this->actingAs($unauthorizedUser)
                ->put(route('super-admin.articles.update', $article), ['title' => 'Ubah'])
                ->assertForbidden();
        }
    }
}
