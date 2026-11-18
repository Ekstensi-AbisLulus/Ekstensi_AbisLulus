<?php

namespace Tests\Browser\Aqeel;

use App\Models\LearningMaterial;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Concerns\CreatesTestUsers;
use Tests\Browser\Concerns\MakesUploadFiles;
use Tests\DuskTestCase;

/**
 * PBI-10 — Tambah Materi Pembelajaran Video (TC.Material.001–007).
 */
#[Group('aqeel')]
class MaterialCreateTest extends DuskTestCase
{
    use CleansMaterialUploads, CreatesTestUsers, DatabaseMigrations, MakesUploadFiles;

    private const VIDEO_URL = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';

    /**
     * Case ID  : TC.Material.001
     * Test Case: Admin menambahkan materi pembelajaran video baru menggunakan tautan video (YouTube)
     *            dan URL thumbnail dengan status dipublikasikan.
     */
    public function test_tc_material_001_tambah_materi_dengan_url_thumbnail(): void
    {
        $admin = $this->makeAdmin();

        $this->browse(function (Browser $browser) use ($admin) {
            // Langkah 1: klik "+ Tambah Materi Baru".
            $browser->loginAs($admin)
                ->visit('/admin/materials')
                ->assertSeeIn('@create-button', 'Tambah Materi Baru')
                ->clickAndWaitForReload('@create-button')
                ->assertPathIs('/admin/materials/create')
                ->assertSee('Tambah Materi Pembelajaran Baru')
                ->assertSee('Judul Materi')
                ->assertSee('Link Video')
                ->assertSee('Deskripsi Materi')
                ->assertSee('Unggah Thumbnail (Opsional)')
                ->assertSee('URL Thumbnail')
                ->assertSee('Publikasikan materi sekarang')
                ->assertVisible('@title-input')
                ->assertVisible('@video-url-input')
                ->assertVisible('@description-input')
                ->assertVisible('@thumbnail-input')
                ->assertVisible('@thumbnail-url-input')
                ->assertPresent('@publish-switch')
                ->shot('Aqeel/TC.Material.001-step1');

            // Langkah 2–5: isi field.
            $browser->type('@title-input', 'Persiapan Wawancara Kerja')
                ->assertInputValue('@title-input', 'Persiapan Wawancara Kerja')
                ->type('@video-url-input', self::VIDEO_URL)
                ->assertInputValue('@video-url-input', self::VIDEO_URL)
                ->type('@description-input', 'Panduan lengkap menjawab pertanyaan wawancara HRD dengan metode STAR.')
                ->assertInputValue('@description-input', 'Panduan lengkap menjawab pertanyaan wawancara HRD dengan metode STAR.')
                ->type('@thumbnail-url-input', 'https://example.com/images/wawancara.jpg')
                ->assertInputValue('@thumbnail-url-input', 'https://example.com/images/wawancara.jpg')
                ->assertChecked('@publish-switch')
                ->shot('Aqeel/TC.Material.001-step5');

            // Langkah 6: simpan.
            $browser->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/admin/materials')
                ->assertSee('Materi pembelajaran berhasil ditambahkan.')
                ->assertSeeIn('@materials-table', 'Persiapan Wawancara Kerja')
                ->shot('Aqeel/TC.Material.001-step6');
        });

        $this->assertDatabaseHas('learning_materials', [
            'admin_id' => $admin->id,
            'title' => 'Persiapan Wawancara Kerja',
            'video_url' => self::VIDEO_URL,
            'description' => 'Panduan lengkap menjawab pertanyaan wawancara HRD dengan metode STAR.',
            'thumbnail_url' => 'https://example.com/images/wawancara.jpg',
            'is_published' => true,
        ]);
    }

    /**
     * Case ID  : TC.Material.002
     * Test Case: Admin menambahkan materi pembelajaran baru dengan mengunggah file gambar thumbnail
     *            (JPG/JPEG/PNG/WebP <= 2 MB) dan status Draf (tidak dipublikasikan).
     */
    public function test_tc_material_002_tambah_materi_unggah_thumbnail_draf(): void
    {
        $admin = $this->makeAdmin();

        $this->browse(function (Browser $browser) use ($admin) {
            // Langkah 1: isi field wajib (link Google Drive).
            $browser->loginAs($admin)
                ->visit('/admin/materials/create')
                ->type('@title-input', 'Dasar Microsoft Excel')
                ->type('@video-url-input', 'https://drive.google.com/file/d/123456/view')
                ->type('@description-input', 'Pengenalan rumus dasar dan pengolahan data di Microsoft Excel.')
                ->assertInputValue('@title-input', 'Dasar Microsoft Excel')
                ->assertInputValue('@video-url-input', 'https://drive.google.com/file/d/123456/view');

            // Langkah 2: pilih file gambar.
            $browser->attach('@thumbnail-input', $this->imageFile('png'));
            $this->assertStringEndsWith('.png', $browser->value('@thumbnail-input'));

            // Langkah 3: matikan switch publikasi lalu simpan.
            $browser->uncheck('@publish-switch')
                ->assertNotChecked('@publish-switch')
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/admin/materials')
                ->assertSee('Materi pembelajaran berhasil ditambahkan.');

            $material = LearningMaterial::where('title', 'Dasar Microsoft Excel')->firstOrFail();

            $this->assertFalse($material->is_published);
            $this->assertStringStartsWith('materials/', $material->thumbnail_url);
            $this->assertTrue(Storage::disk('public')->exists($material->thumbnail_url), 'File thumbnail harus tersimpan.');

            $browser->assertSeeIn("@material-row-{$material->id}", 'Draf')
                ->assertAttributeContains("@material-row-{$material->id} img", 'src', $material->thumbnail_url)
                ->shot('Aqeel/TC.Material.002-step3');
        });
    }

    /**
     * Case ID  : TC.Material.003
     * Test Case: Admin menambahkan materi dengan mengosongkan field wajib
     *            (Judul Materi, Link Video, atau Deskripsi Materi).
     */
    public function test_tc_material_003_field_wajib_kosong(): void
    {
        $admin = $this->makeAdmin();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/materials/create')
                ->disableHtmlValidation()
                ->clear('@title-input')
                ->clear('@video-url-input')
                ->clear('@description-input')
                ->assertInputValue('@title-input', '')
                ->assertInputValue('@video-url-input', '')
                ->assertInputValue('@description-input', '')
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/admin/materials/create')
                ->assertDontSee('Materi pembelajaran berhasil ditambahkan.')
                ->assertSee('Judul materi wajib diisi.')
                ->assertSee('Link video wajib diisi.')
                ->assertSee('Deskripsi materi wajib diisi.')
                ->shot('Aqeel/TC.Material.003-step2');
        });

        $this->assertDatabaseCount('learning_materials', 0);
    }

    /**
     * Case ID  : TC.Material.004
     * Test Case: Admin menambahkan materi dengan Judul Materi melebihi 200 karakter.
     *
     * Atribut maxlength="200" membatasi ketikan di browser, sehingga dihapus lewat script
     * (setara Inspect Element) untuk menguji validasi di server.
     */
    public function test_tc_material_004_judul_lebih_200_karakter(): void
    {
        $admin = $this->makeAdmin();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/materials/create')
                ->disableHtmlValidation();

            $browser->script("document.querySelector('[dusk=\"title-input\"]').removeAttribute('maxlength');");

            $browser->type('@title-input', str_repeat('a', 201))
                ->type('@video-url-input', self::VIDEO_URL)
                ->type('@description-input', 'Deskripsi valid.');

            $this->assertSame(201, mb_strlen($browser->value('@title-input')));

            $browser->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/admin/materials/create')
                ->assertSee('Judul materi maksimal 200 karakter.')
                ->shot('Aqeel/TC.Material.004-step2');
        });

        $this->assertDatabaseCount('learning_materials', 0);
    }

    /**
     * Case ID  : TC.Material.005
     * Test Case: Admin menambahkan materi dengan format Link Video atau URL Thumbnail yang bukan URL valid.
     */
    public function test_tc_material_005_format_url_tidak_valid(): void
    {
        $admin = $this->makeAdmin();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/materials/create')
                ->disableHtmlValidation()
                ->type('@title-input', 'Materi URL Salah')
                ->type('@description-input', 'Deskripsi valid.')
                ->type('@video-url-input', 'bukan-url')
                ->type('@thumbnail-url-input', 'tidak-valid')
                ->assertInputValue('@video-url-input', 'bukan-url')
                ->assertInputValue('@thumbnail-url-input', 'tidak-valid')
                ->clickAndWaitForReload('@submit-button')
                ->assertPathIs('/admin/materials/create')
                ->assertSee('Link video harus berupa URL yang valid (http/https).')
                ->assertSee('URL thumbnail harus berupa URL yang valid (http/https).')
                ->shot('Aqeel/TC.Material.005-step2');
        });

        $this->assertDatabaseCount('learning_materials', 0);
    }

    /**
     * Case ID  : TC.Material.006
     * Test Case: Admin mengunggah file thumbnail dengan format selain gambar (JPG/JPEG/PNG/WebP)
     *            atau ukuran melebihi 2 MB.
     */
    public function test_tc_material_006_validasi_unggah_thumbnail(): void
    {
        $admin = $this->makeAdmin();

        $cases = [
            'pdf' => [$this->nonImageFile('pdf'), 'File thumbnail harus berupa gambar.'],
            'gif' => [$this->imageFile('gif'), 'Format gambar thumbnail harus JPG, JPEG, PNG, atau WebP.'],
            'lebih-2mb' => [$this->largeImageFile(2), 'Ukuran gambar thumbnail maksimal 2 MB.'],
        ];

        $this->browse(function (Browser $browser) use ($admin, $cases) {
            $browser->loginAs($admin);

            foreach ($cases as $label => [$file, $message]) {
                $browser->visit('/admin/materials/create')
                    ->type('@title-input', 'Materi Thumbnail '.$label)
                    ->type('@video-url-input', self::VIDEO_URL)
                    ->type('@description-input', 'Deskripsi valid.')
                    ->attach('@thumbnail-input', $file)
                    ->shot("Aqeel/TC.Material.006-step1-{$label}")
                    ->clickAndWaitForReload('@submit-button')
                    ->assertPathIs('/admin/materials/create')
                    ->shot("Aqeel/TC.Material.006-step2-{$label}")
                    ->assertDontSee('Materi pembelajaran berhasil ditambahkan.')
                    ->assertSee($message);

                $this->assertDatabaseCount('learning_materials', 0);
            }
        });
    }

    /**
     * Case ID  : TC.Material.007
     * Test Case: Guest (belum login) atau User biasa (role: user) mencoba mengakses halaman
     *            atau mengirim request Tambah Materi.
     */
    public function test_tc_material_007_otorisasi_halaman_tambah(): void
    {
        $user = $this->makeUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/admin/materials/create')
                ->assertPathIs('/login')
                ->shot('Aqeel/TC.Material.007-step1-guest');

            $browser->loginAs($user)
                ->visit('/admin/materials/create')
                ->assertPathIs('/admin/materials/create')
                ->assertSee('403')
                ->assertSourceHas('Anda tidak memiliki akses ke halaman ini.')
                ->assertDontSee('Simpan Materi')
                ->shot('Aqeel/TC.Material.007-step2-user');
        });
    }
}
