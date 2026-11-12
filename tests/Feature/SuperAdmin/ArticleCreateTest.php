<?php

namespace Tests\Feature\SuperAdmin;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ArticleCreateTest extends TestCase
{
    use RefreshDatabase;

    private const ONE_PIXEL_PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    private static function fakePng(string $name = 'sampul.png', int $extraKilobytes = 0): UploadedFile
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
            'title' => '5 Skill yang Paling Dicari Perusahaan',
            'excerpt' => 'Komunikasi, kerja sama tim, dan kemampuan digital menempati urutan teratas.',
            'content' => "Paragraf pembuka artikel karier.\n\nParagraf kedua berisi langkah praktis untuk lulusan baru.",
            'cover_image' => self::fakePng('sampul.png'),
            'status' => ArticleStatus::Published->value,
        ], $overrides);
    }

    public function test_super_admin_can_open_create_article_form(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->get(route('super-admin.articles.create'))
            ->assertOk()
            ->assertSee('Tambah Artikel')
            ->assertSee('Tulis Artikel Baru')
            ->assertSee('name="title"', false)
            ->assertSee('id="slug-preview"', false)
            ->assertSee('name="excerpt"', false)
            ->assertSee('name="content"', false)
            ->assertSee('name="cover_image"', false)
            ->assertSee('name="status"', false);
    }

    public function test_super_admin_can_store_published_article_with_cover_image_and_auto_slug(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->post(route('super-admin.articles.store'), $this->validData());

        $response->assertRedirect(route('super-admin.articles.index'))
            ->assertSessionHas('success', 'Artikel berhasil ditambahkan.');

        $article = Article::firstOrFail();
        $this->assertSame($superAdmin->id, $article->super_admin_id);
        $this->assertSame('5 Skill yang Paling Dicari Perusahaan', $article->title);
        $this->assertSame('5-skill-yang-paling-dicari-perusahaan', $article->slug);
        $this->assertSame('Komunikasi, kerja sama tim, dan kemampuan digital menempati urutan teratas.', $article->excerpt);
        $this->assertSame(ArticleStatus::Published, $article->status);
        $this->assertNotNull($article->published_at);
        $this->assertNotNull($article->cover_image_url);
        $this->assertStringStartsWith('articles/', $article->cover_image_url);

        Storage::disk('public')->assertExists($article->cover_image_url);
        $this->assertStringContainsString('/storage/'.$article->cover_image_url, (string) $article->coverImageSrc());
    }

    public function test_super_admin_can_store_draft_article_without_cover_image(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->post(route('super-admin.articles.store'), $this->validData([
                'excerpt' => '   ',
                'cover_image' => null,
                'status' => ArticleStatus::Draft->value,
            ]))
            ->assertRedirect(route('super-admin.articles.index'));

        $article = Article::firstOrFail();
        $this->assertNull($article->excerpt);
        $this->assertNull($article->cover_image_url);
        $this->assertNull($article->coverImageSrc());
        $this->assertSame(ArticleStatus::Draft, $article->status);
        $this->assertNull($article->published_at);
    }

    public function test_duplicate_article_title_generates_unique_slug_with_suffix(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        foreach (range(1, 3) as $_) {
            $this->actingAs($superAdmin)->post(route('super-admin.articles.store'), $this->validData([
                'title' => 'Panduan Karier Lulusan Baru',
                'cover_image' => null,
            ]));
        }

        $slugs = Article::orderBy('id')->pluck('slug')->all();
        $this->assertSame([
            'panduan-karier-lulusan-baru',
            'panduan-karier-lulusan-baru-2',
            'panduan-karier-lulusan-baru-3',
        ], $slugs);
    }

    public function test_admin_and_user_are_forbidden_and_guest_is_redirected(): void
    {
        $this->get(route('super-admin.articles.create'))->assertRedirect('/login');
        $this->post(route('super-admin.articles.store'), $this->validData())->assertRedirect('/login');

        foreach ([User::factory()->create(), User::factory()->admin()->create()] as $unauthorizedUser) {
            $this->actingAs($unauthorizedUser)
                ->get(route('super-admin.articles.create'))
                ->assertForbidden();

            $this->actingAs($unauthorizedUser)
                ->post(route('super-admin.articles.store'), $this->validData())
                ->assertForbidden();
        }

        $this->assertDatabaseCount('articles', 0);
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
                'Judul artikel wajib diisi.',
            ],
            'judul > 200 karakter' => [
                ['title' => str_repeat('a', 201)],
                'title',
                'Judul artikel maksimal 200 karakter.',
            ],
            'ringkasan > 300 karakter' => [
                ['excerpt' => str_repeat('b', 301)],
                'excerpt',
                'Ringkasan maksimal 300 karakter.',
            ],
            'konten kosong' => [
                ['content' => '   '],
                'content',
                'Konten artikel wajib diisi.',
            ],
            'file sampul bukan gambar' => [
                ['cover_image' => UploadedFile::fake()->create('lampiran.pdf', 100, 'application/pdf')],
                'cover_image',
                'File sampul harus berupa gambar.',
            ],
            'ukuran sampul > 3 MB' => [
                ['cover_image' => self::fakePng('sampul-besar.png', 3100)],
                'cover_image',
                'Ukuran sampul maksimal 3 MB.',
            ],
            'status kosong' => [
                ['status' => ''],
                'status',
                'Status publikasi wajib dipilih.',
            ],
            'status di luar enum' => [
                ['status' => 'archived'],
                'status',
                'Status publikasi tidak valid.',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidData')]
    public function test_article_validation(array $overrides, string $field, string $message): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->from(route('super-admin.articles.create'))
            ->post(route('super-admin.articles.store'), $this->validData($overrides))
            ->assertRedirect(route('super-admin.articles.create'))
            ->assertSessionHasErrors([$field => $message]);

        $this->assertDatabaseCount('articles', 0);
    }

    public function test_invalid_field_is_marked_for_accessibility_on_article_form(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->followingRedirects()
            ->from(route('super-admin.articles.create'))
            ->post(route('super-admin.articles.store'), $this->validData(['content' => '']))
            ->assertSee('Konten artikel wajib diisi.')
            ->assertSee('aria-invalid="true" aria-describedby="content-error"', false)
            ->assertSee('id="content-error"', false);
    }
}
