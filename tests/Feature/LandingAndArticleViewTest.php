<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\HeroSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingAndArticleViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_landing_page_shows_default_hero_when_no_active_hero_exists(): void
    {
        HeroSection::factory()->create([
            'title' => 'Hero Nonaktif Tersembunyi',
            'is_active' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('AbisLulus')
            ->assertDontSee('Hero Nonaktif Tersembunyi')
            ->assertDontSee('id="heroCarousel"', false);
    }

    public function test_landing_page_shows_only_active_heroes_and_renders_carousel_when_multiple(): void
    {
        HeroSection::factory()->create([
            'title' => 'Hero Aktif Pertama',
            'subtitle' => 'Subjudul pertama',
            'target_url' => '/e-learning',
            'is_active' => true,
        ]);
        HeroSection::factory()->create([
            'title' => 'Hero Aktif Kedua',
            'subtitle' => 'Subjudul kedua',
            'target_url' => '/kegiatan',
            'is_active' => true,
        ]);
        HeroSection::factory()->create([
            'title' => 'Hero Nonaktif Jangan Tampil',
            'is_active' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('id="heroCarousel"', false)
            ->assertSee('Hero Aktif Pertama')
            ->assertSee('Hero Aktif Kedua')
            ->assertDontSee('Hero Nonaktif Jangan Tampil');
    }

    public function test_landing_page_shows_three_latest_published_articles_and_hides_drafts(): void
    {
        foreach (range(1, 4) as $i) {
            Article::factory()->create([
                'title' => "Artikel Publik {$i}",
                'status' => ArticleStatus::Published,
                'published_at' => now()->subDays(5 - $i),
            ]);
        }

        Article::factory()->draft()->create([
            'title' => 'Artikel Draf Rahasia',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Artikel Terbaru')
            ->assertSee('Lihat Semua')
            ->assertSee('Artikel Publik 4')
            ->assertSee('Artikel Publik 3')
            ->assertSee('Artikel Publik 2')
            ->assertDontSee('Artikel Publik 1')
            ->assertDontSee('Artikel Draf Rahasia');
    }

    public function test_public_article_index_shows_published_only_and_paginates_nine_per_page(): void
    {
        foreach (range(1, 10) as $i) {
            Article::factory()->create([
                'title' => sprintf('Panduan Karier %02d', $i),
                'status' => ArticleStatus::Published,
                'published_at' => now()->subMinutes(20 - $i),
            ]);
        }

        Article::factory()->draft()->create([
            'title' => 'Draf Tidak Boleh Muncul di Publik',
        ]);

        $this->get(route('articles.index'))
            ->assertOk()
            ->assertSee('Panduan Karier 10')
            ->assertSee('Panduan Karier 02')
            ->assertDontSee('Panduan Karier 01')
            ->assertDontSee('Draf Tidak Boleh Muncul di Publik');

        $this->get(route('articles.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Panduan Karier 01');
    }

    public function test_public_article_detail_renders_published_and_returns_404_for_draft(): void
    {
        $published = Article::factory()->create([
            'title' => 'Cara Sukses Wawancara Kerja',
            'content' => 'Isi lengkap panduan wawancara kerja.',
            'status' => ArticleStatus::Published,
        ]);

        $draft = Article::factory()->draft()->create([
            'title' => 'Konsep Artikel Belum Rilis',
        ]);

        $this->get(route('articles.show', $published))
            ->assertOk()
            ->assertSee('Cara Sukses Wawancara Kerja')
            ->assertSee('Isi lengkap panduan wawancara kerja.');

        $this->get(route('articles.show', $draft))
            ->assertNotFound();
    }

    public function test_panel_hero_and_article_index_and_show_are_restricted_to_super_admin(): void
    {
        $hero = HeroSection::factory()->create(['title' => 'Hero Panel Uji', 'is_active' => false]);
        $article = Article::factory()->draft()->create(['title' => 'Artikel Draf Panel Uji']);

        $paths = [
            route('super-admin.hero-sections.index'),
            route('super-admin.hero-sections.show', $hero),
            route('super-admin.articles.index'),
            route('super-admin.articles.show', $article),
        ];

        foreach ($paths as $path) {
            $this->get($path)->assertRedirect('/login');
        }

        foreach ([User::factory()->create(), User::factory()->admin()->create()] as $unauthorizedUser) {
            foreach ($paths as $path) {
                $this->actingAs($unauthorizedUser)->get($path)->assertForbidden();
            }
        }

        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->get(route('super-admin.hero-sections.index'))
            ->assertOk()
            ->assertSee('+ Tambah Hero')
            ->assertSee('Hero Panel Uji')
            ->assertSee('Nonaktif');

        $this->actingAs($superAdmin)
            ->get(route('super-admin.hero-sections.show', $hero))
            ->assertOk()
            ->assertSee('Pratinjau Hero Section')
            ->assertSee('Hero Panel Uji');

        $this->actingAs($superAdmin)
            ->get(route('super-admin.articles.index'))
            ->assertOk()
            ->assertSee('+ Tambah Artikel')
            ->assertSee('Artikel Draf Panel Uji')
            ->assertSee('Draf');

        $this->actingAs($superAdmin)
            ->get(route('super-admin.articles.show', $article))
            ->assertOk()
            ->assertSee('Pratinjau Artikel')
            ->assertSee('Artikel Draf Panel Uji');
    }

    public function test_super_admin_article_index_supports_search_status_filter_and_pagination(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        foreach (range(1, 11) as $i) {
            Article::factory()->create([
                'title' => sprintf('Tips CV Nomor %02d', $i),
                'status' => ArticleStatus::Published,
            ]);
        }

        Article::factory()->draft()->create([
            'title' => 'Strategi LinkedIn Khusus Draf',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.articles.index'))
            ->assertOk()
            ->assertSee('Strategi LinkedIn Khusus Draf')
            ->assertDontSee('Tips CV Nomor 01');

        $this->actingAs($superAdmin)
            ->get(route('super-admin.articles.index', ['search' => 'LinkedIn']))
            ->assertOk()
            ->assertSee('Strategi LinkedIn Khusus Draf')
            ->assertDontSee('Tips CV Nomor 11');

        $this->actingAs($superAdmin)
            ->get(route('super-admin.articles.index', ['status' => 'draft']))
            ->assertOk()
            ->assertSee('Strategi LinkedIn Khusus Draf')
            ->assertDontSee('Tips CV Nomor 11');
    }
}
