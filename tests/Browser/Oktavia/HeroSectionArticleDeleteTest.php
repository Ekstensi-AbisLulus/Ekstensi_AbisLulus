<?php

namespace Tests\Browser\Oktavia;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\HeroSection;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Concerns\CreatesTestUsers;
use Tests\Browser\Concerns\MakesUploadFiles;
use Tests\DuskTestCase;

/**
 * PBI-25 — Hapus Hero Section & Artikel (TC.LandingPage.025–029).
 */
#[Group('oktavia')]
class HeroSectionArticleDeleteTest extends DuskTestCase
{
    use CleansLandingUploads, CreatesTestUsers, DatabaseMigrations, MakesUploadFiles;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = $this->makeSuperAdmin();
    }

    /**
     * Case ID  : TC.LandingPage.025
     * Test Case: Super Admin menekan tombol Batal pada modal konfirmasi hapus hero section.
     */
    public function test_tc_landing_page_025_batal_hapus_hero(): void
    {
        $hero = $this->hero('Banner Karier Utama');
        $id = $hero->id;

        $this->browse(function (Browser $browser) use ($id) {
            $browser->loginAs($this->superAdmin)->visit('/super-admin/hero-sections');

            foreach (['batal' => "@delete-cancel-{$id}", 'tutup-x' => "@delete-close-{$id}"] as $label => $closeButton) {
                // Langkah 1: klik ikon Hapus → modal konfirmasi.
                $this->openModal($browser, "@delete-{$id}", "#deleteHeroModal-{$id}");
                $browser->assertSeeIn("@delete-modal-{$id}", 'Konfirmasi Hapus Hero Section')
                    ->assertSeeIn("@delete-modal-{$id}", "Hapus hero 'Banner Karier Utama'? Tindakan ini tidak dapat dibatalkan.")
                    ->assertSeeIn("@delete-cancel-{$id}", 'Batal')
                    ->assertSeeIn("@delete-confirm-{$id}", 'Hapus')
                    ->shot("Oktavia/TC.LandingPage.025-step1-{$label}");

                // Langkah 2: Batal / X.
                $browser->click($closeButton)
                    ->waitUntilMissing("#deleteHeroModal-{$id}.show")
                    ->waitUntilMissing('.modal-backdrop')
                    ->assertPathIs('/super-admin/hero-sections')
                    ->assertSeeIn("@hero-row-{$id}", 'Banner Karier Utama')
                    ->shot("Oktavia/TC.LandingPage.025-step2-{$label}");
            }
        });

        $this->assertDatabaseHas('hero_sections', ['id' => $id, 'title' => 'Banner Karier Utama']);
    }

    /**
     * Case ID  : TC.LandingPage.026
     * Test Case: Super Admin menghapus hero section melalui modal konfirmasi.
     */
    public function test_tc_landing_page_026_hapus_hero(): void
    {
        $this->hero('Banner Tetap Tampil');
        $hero = $this->hero('Banner Akan Dihapus');
        $image = $this->giveStoredHeroImage($hero);
        $id = $hero->id;

        $this->browse(function (Browser $browser) use ($id) {
            $browser->loginAs($this->superAdmin)->visit('/super-admin/hero-sections');

            $this->openModal($browser, "@delete-{$id}", "#deleteHeroModal-{$id}");
            $browser->clickAndWaitForReload("@delete-confirm-{$id}")
                ->assertPathIs('/super-admin/hero-sections')
                ->assertSee('Data berhasil dihapus.')
                ->assertMissing("@hero-row-{$id}")
                ->shot('Oktavia/TC.LandingPage.026-step1')
                ->visit('/')
                ->assertSourceMissing('Banner Akan Dihapus')
                ->assertSee('Banner Tetap Tampil')
                ->shot('Oktavia/TC.LandingPage.026-step1-landing');
        });

        $this->assertDatabaseMissing('hero_sections', ['id' => $id]);
        $this->assertFalse(Storage::disk('public')->exists($image), 'File gambar hero harus terhapus dari storage.');
    }

    /**
     * Case ID  : TC.LandingPage.027
     * Test Case: Super Admin menghapus hero aktif terakhir.
     */
    public function test_tc_landing_page_027_landing_setelah_hero_aktif_terakhir_dihapus(): void
    {
        $this->hero('Banner Nonaktif Lain', ['is_active' => false]);
        $hero = $this->hero('Banner Aktif Terakhir');
        $this->giveStoredHeroImage($hero);
        $id = $hero->id;

        $this->browse(function (Browser $browser) use ($id) {
            $browser->loginAs($this->superAdmin)->visit('/super-admin/hero-sections');

            $this->openModal($browser, "@delete-{$id}", "#deleteHeroModal-{$id}");
            $browser->clickAndWaitForReload("@delete-confirm-{$id}")
                ->assertSee('Data berhasil dihapus.')
                ->visit('/')
                ->assertDontSee('Server Error')
                ->assertMissing('@hero-carousel')
                ->assertMissing('@hero-single')
                ->assertSeeIn('@hero-default', 'Siap Melangkah Setelah Lulus Bersama AbisLulus')
                ->shot('Oktavia/TC.LandingPage.027-step1');
        });

        $this->assertSame(0, HeroSection::active()->count());
    }

    /**
     * Case ID  : TC.LandingPage.028
     * Test Case: Super Admin membatalkan lalu menghapus artikel melalui modal konfirmasi.
     */
    public function test_tc_landing_page_028_batal_lalu_hapus_artikel(): void
    {
        $article = Article::factory()->create([
            'super_admin_id' => $this->superAdmin->id,
            'title' => 'Tips Wawancara Pertama',
            'status' => ArticleStatus::Published,
        ]);
        $cover = $this->giveStoredCover($article);
        $id = $article->id;
        $slug = $article->slug;

        $this->browse(function (Browser $browser) use ($id, $slug) {
            // Langkah 1: klik ikon Hapus → modal.
            $browser->loginAs($this->superAdmin)->visit('/super-admin/articles');

            $this->openModal($browser, "@delete-{$id}", "#deleteArticleModal-{$id}");
            $browser->assertSeeIn("@delete-modal-{$id}", 'Konfirmasi Hapus Artikel')
                ->assertSeeIn("@delete-modal-{$id}", "Hapus artikel 'Tips Wawancara Pertama'? Tindakan ini tidak dapat dibatalkan.")
                ->shot('Oktavia/TC.LandingPage.028-step1');

            // Langkah 2: Batal.
            $browser->click("@delete-cancel-{$id}")
                ->waitUntilMissing("#deleteArticleModal-{$id}.show")
                ->waitUntilMissing('.modal-backdrop')
                ->assertSeeIn("@article-row-{$id}", 'Tips Wawancara Pertama')
                ->shot('Oktavia/TC.LandingPage.028-step2');

            $this->assertDatabaseHas('articles', ['id' => $id]);

            // Langkah 3: Hapus lagi → konfirmasi.
            $this->openModal($browser, "@delete-{$id}", "#deleteArticleModal-{$id}");
            $browser->clickAndWaitForReload("@delete-confirm-{$id}")
                ->assertPathIs('/super-admin/articles')
                ->assertSee('Data berhasil dihapus.')
                ->assertMissing("@article-row-{$id}")
                ->shot('Oktavia/TC.LandingPage.028-step3')
                ->visit("/artikel/{$slug}")
                ->assertSee('404')
                ->assertSourceHas('Not Found')
                ->shot('Oktavia/TC.LandingPage.028-step3-publik');
        });

        $this->assertDatabaseMissing('articles', ['id' => $id]);
        $this->assertFalse(Storage::disk('public')->exists($cover), 'File sampul harus terhapus dari storage.');
    }

    /**
     * Case ID  : TC.LandingPage.029
     * Test Case: User biasa atau Admin mencoba mengirim request hapus hero section/artikel.
     */
    public function test_tc_landing_page_029_otorisasi_hapus(): void
    {
        $hero = $this->hero('Hero Tidak Boleh Dihapus');
        $article = Article::factory()->create([
            'super_admin_id' => $this->superAdmin->id,
            'title' => 'Artikel Tidak Boleh Dihapus',
        ]);
        $user = $this->makeUser();
        $admin = $this->makeAdmin();

        $targets = [
            'hero' => "/super-admin/hero-sections/{$hero->id}",
            'artikel' => "/super-admin/articles/{$article->slug}",
        ];

        $this->browse(function (Browser $browser) use ($targets, $user, $admin) {
            foreach (['user' => $user, 'admin' => $admin] as $role => $account) {
                foreach ($targets as $label => $action) {
                    $browser->loginAs($account)
                        ->visit('/')
                        ->assertSourceHas('<meta name="csrf-token"');

                    $this->submitForgedDelete($browser, $action);

                    $browser->assertPathIs($action)
                        ->assertSee('403')
                        ->assertSourceHas('Anda tidak memiliki akses ke halaman ini.')
                        ->shot("Oktavia/TC.LandingPage.029-step1-{$role}-{$label}");
                }
            }
        });

        $this->assertDatabaseHas('hero_sections', ['id' => $hero->id]);
        $this->assertDatabaseHas('articles', ['id' => $article->id]);
    }

    // --- Helper ---------------------------------------------------------------

    private function hero(string $title, array $attributes = []): HeroSection
    {
        return HeroSection::factory()->create($attributes + [
            'super_admin_id' => $this->superAdmin->id,
            'title' => $title,
            'is_active' => true,
        ]);
    }

    private function openModal(Browser $browser, string $trigger, string $modal): void
    {
        $browser->click($trigger)
            ->waitFor("{$modal}.show")
            ->pause(500); // tunggu animasi modal selesai
    }

    /**
     * Kirim DELETE lewat form buatan JavaScript (setara memodifikasi form via Inspect Element).
     */
    private function submitForgedDelete(Browser $browser, string $action): void
    {
        $browser->waitForReload(function (Browser $browser) use ($action) {
            $browser->script(
                "var form = document.createElement('form'); form.method = 'POST'; form.action = '{$action}';"
                ."[['_token', document.querySelector('meta[name=\"csrf-token\"]').content], ['_method', 'DELETE']].forEach(function (pair) {"
                .'var input = document.createElement("input"); input.type = "hidden"; input.name = pair[0]; input.value = pair[1]; form.appendChild(input); });'
                .'document.body.appendChild(form); form.submit();'
            );
        });
    }
}
