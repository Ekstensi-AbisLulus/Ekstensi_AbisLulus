<?php

namespace Tests\Browser\Aqeel;

use App\Models\LearningMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Concerns\CreatesTestUsers;
use Tests\Browser\Concerns\MakesUploadFiles;
use Tests\DuskTestCase;

/**
 * PBI-12 — Edit Materi Pembelajaran Video (TC.Material.013–017).
 */
#[Group('aqeel')]
class MaterialUpdateTest extends DuskTestCase
{
    use CleansMaterialUploads, CreatesTestUsers, DatabaseMigrations, MakesUploadFiles;

    /**
     * Case ID  : TC.Material.013
     * Test Case: Admin memperbarui data materi pembelajaran miliknya sendiri melalui modal
     *            konfirmasi simpan perubahan.
     */
    public function test_tc_material_013_edit_materi_dengan_konfirmasi(): void
    {
        $admin = $this->makeAdmin();
        $material = $this->material($admin);

        $this->browse(function (Browser $browser) use ($admin, $material) {
            // Langkah 1: klik ikon Ubah Materi → form terisi data saat ini.
            $browser->loginAs($admin)
                ->visit('/admin/materials')
                ->clickAndWaitForReload("@edit-{$material->id}")
                ->assertPathIs("/admin/materials/{$material->id}/edit")
                ->assertSee('Ubah Materi Pembelajaran Video')
                ->assertInputValue('@title-input', 'Judul Materi Lama')
                ->assertInputValue('@video-url-input', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
                ->assertInputValue('@description-input', 'Deskripsi materi lama.')
                ->assertInputValue('@thumbnail-url-input', 'https://example.com/images/lama.jpg')
                ->assertChecked('@publish-switch')
                ->shot('Aqeel/TC.Material.013-step1');

            // Langkah 2: ubah field.
            $browser->type('@title-input', 'Judul Materi Diperbarui')
                ->type('@video-url-input', 'https://youtu.be/9bZkp7q19f0')
                ->type('@description-input', 'Deskripsi materi yang sudah diperbarui.')
                ->type('@thumbnail-url-input', 'https://example.com/images/baru.jpg')
                ->uncheck('@publish-switch')
                ->assertInputValue('@title-input', 'Judul Materi Diperbarui')
                ->assertNotChecked('@publish-switch')
                ->shot('Aqeel/TC.Material.013-step2');

            // Langkah 3: klik Simpan Perubahan → modal konfirmasi.
            $browser->click('@save-button')
                ->waitFor('#confirmSaveModal.show')
                ->assertSeeIn('@save-modal', 'Konfirmasi Simpan Perubahan')
                ->assertSeeIn('@save-modal', 'Apakah Anda yakin ingin menyimpan perubahan pada materi pembelajaran ini?')
                ->assertSeeIn('@save-review', 'Periksa Kembali')
                ->assertSeeIn('@save-confirm', 'Ya, Simpan Perubahan')
                ->shot('Aqeel/TC.Material.013-step3');

            // Langkah 4: konfirmasi simpan.
            $browser->clickAndWaitForReload('@save-confirm')
                ->assertPathIs('/admin/materials')
                ->assertSee('Materi pembelajaran berhasil diperbarui.')
                ->assertSeeIn("@material-row-{$material->id}", 'Judul Materi Diperbarui')
                ->assertSeeIn("@material-row-{$material->id}", 'Draf')
                ->shot('Aqeel/TC.Material.013-step4');
        });

        $this->assertDatabaseHas('learning_materials', [
            'id' => $material->id,
            'title' => 'Judul Materi Diperbarui',
            'video_url' => 'https://youtu.be/9bZkp7q19f0',
            'description' => 'Deskripsi materi yang sudah diperbarui.',
            'thumbnail_url' => 'https://example.com/images/baru.jpg',
            'is_published' => false,
        ]);
    }

    /**
     * Case ID  : TC.Material.014
     * Test Case: Admin mengganti thumbnail materi dengan mengunggah file gambar baru.
     */
    public function test_tc_material_014_ganti_file_thumbnail(): void
    {
        $admin = $this->makeAdmin();
        $material = $this->material($admin);
        $oldThumbnail = $this->giveStoredThumbnail($material);

        $this->browse(function (Browser $browser) use ($admin, $material, $oldThumbnail) {
            // Langkah 1: pilih file baru.
            $browser->loginAs($admin)
                ->visit("/admin/materials/{$material->id}/edit")
                ->assertAttributeContains('@current-thumbnail', 'src', $oldThumbnail)
                ->assertSee('Ganti dengan Unggah Thumbnail (Opsional)')
                ->attach('@thumbnail-input', $this->imageFile('png', 80));

            $this->assertStringEndsWith('.png', $browser->value('@thumbnail-input'));
            $browser->shot('Aqeel/TC.Material.014-step1');

            // Langkah 2: simpan lewat modal konfirmasi.
            $browser->click('@save-button')
                ->waitFor('#confirmSaveModal.show')
                ->clickAndWaitForReload('@save-confirm')
                ->assertPathIs('/admin/materials')
                ->assertSee('Materi pembelajaran berhasil diperbarui.');

            $newThumbnail = $material->fresh()->thumbnail_url;

            $this->assertNotSame($oldThumbnail, $newThumbnail);
            $this->assertStringStartsWith('materials/', $newThumbnail);
            $this->assertTrue(Storage::disk('public')->exists($newThumbnail), 'File thumbnail baru harus tersimpan.');
            $this->assertFalse(Storage::disk('public')->exists($oldThumbnail), 'File thumbnail lama harus terhapus.');

            $browser->assertAttributeContains("@material-row-{$material->id} img", 'src', $newThumbnail)
                ->shot('Aqeel/TC.Material.014-step2');
        });
    }

    /**
     * Case ID  : TC.Material.015
     * Test Case: Admin membatalkan proses pengeditan materi melalui pop-up modal Konfirmasi Batal Perubahan.
     */
    public function test_tc_material_015_batal_perubahan(): void
    {
        $admin = $this->makeAdmin();
        $material = $this->material($admin);

        $this->browse(function (Browser $browser) use ($admin, $material) {
            $browser->loginAs($admin)
                ->visit("/admin/materials/{$material->id}/edit")
                ->type('@title-input', 'Judul Belum Disimpan')
                ->type('@description-input', 'Deskripsi belum disimpan.');

            // Langkah 1: klik Batal → modal konfirmasi batal.
            $this->openCancelModal($browser, '@cancel-button');
            $browser->shot('Aqeel/TC.Material.015-step1');

            // Langkah 2: Tetap Mengedit → modal tertutup, input tetap.
            $browser->click('@cancel-keep')
                ->waitUntilMissing('#confirmCancelModal.show')
                ->waitUntilMissing('.modal-backdrop')
                ->assertPathIs("/admin/materials/{$material->id}/edit")
                ->assertInputValue('@title-input', 'Judul Belum Disimpan')
                ->assertInputValue('@description-input', 'Deskripsi belum disimpan.')
                ->shot('Aqeel/TC.Material.015-step2');

            // Tombol "Kembali ke Daftar" membuka modal yang sama.
            $this->openCancelModal($browser, '@back-button');
            $browser->shot('Aqeel/TC.Material.015-step1-kembali-ke-daftar')
                ->click('@cancel-keep')
                ->waitUntilMissing('#confirmCancelModal.show')
                ->waitUntilMissing('.modal-backdrop');

            // Langkah 3: klik Batal lagi → Ya, Batalkan Perubahan.
            $this->openCancelModal($browser, '@cancel-button');
            $browser->clickAndWaitForReload('@cancel-confirm')
                ->assertPathIs('/admin/materials')
                ->assertSeeIn("@material-row-{$material->id}", 'Judul Materi Lama')
                ->assertDontSee('Judul Belum Disimpan')
                ->shot('Aqeel/TC.Material.015-step3');
        });

        $this->assertMaterialUnchanged($material);
    }

    /**
     * Case ID  : TC.Material.016
     * Test Case: Admin mengosongkan field wajib atau memasukkan URL tidak valid saat mengubah materi.
     */
    public function test_tc_material_016_validasi_form_edit(): void
    {
        $admin = $this->makeAdmin();
        $material = $this->material($admin);

        $this->browse(function (Browser $browser) use ($admin, $material) {
            // Langkah 1: isi input tidak valid.
            $browser->loginAs($admin)
                ->visit("/admin/materials/{$material->id}/edit")
                ->disableHtmlValidation()
                ->clear('@title-input')
                ->clear('@description-input')
                ->type('@video-url-input', 'bukan-url')
                ->assertInputValue('@title-input', '')
                ->assertInputValue('@video-url-input', 'bukan-url')
                ->shot('Aqeel/TC.Material.016-step1');

            // Langkah 2a: klik Simpan Perubahan → modal dicegah, alert validasi client-side tampil.
            $browser->click('@save-button')
                ->waitFor('#clientValidationAlert.d-flex')
                ->pause(500)
                ->assertMissing('#confirmSaveModal.show')
                ->assertVisible('@client-alert')
                ->assertSeeIn('@client-alert', 'Judul materi wajib diisi.')
                ->assertSeeIn('@client-alert', 'Link video harus berupa URL yang valid (http/https).')
                ->assertSeeIn('@client-alert', 'Deskripsi materi wajib diisi.')
                ->assertAttributeContains('@title-input', 'class', 'is-invalid')
                ->assertAttributeContains('@video-url-input', 'class', 'is-invalid')
                ->assertAttributeContains('@description-input', 'class', 'is-invalid')
                ->shot('Aqeel/TC.Material.016-step2-client');

            // Langkah 2b: submit form langsung → validasi server (UpdateMaterialRequest).
            $browser->waitForReload(function (Browser $browser) {
                $browser->script("document.getElementById('editMaterialForm').submit();");
            })
                ->assertPathIs("/admin/materials/{$material->id}/edit")
                ->assertVisible('@server-alert')
                ->assertSeeIn('@server-alert', 'Judul materi wajib diisi.')
                ->assertSeeIn('@server-alert', 'Link video harus berupa URL yang valid (http/https).')
                ->assertSeeIn('@server-alert', 'Deskripsi materi wajib diisi.')
                ->assertSeeIn('#title-error', 'Judul materi wajib diisi.')
                ->assertSeeIn('#video-url-error', 'Link video harus berupa URL yang valid (http/https).')
                ->assertSeeIn('#description-error', 'Deskripsi materi wajib diisi.')
                ->assertDontSee('Materi pembelajaran berhasil diperbarui.')
                ->shot('Aqeel/TC.Material.016-step2-server');
        });

        $this->assertMaterialUnchanged($material);
    }

    /**
     * Case ID  : TC.Material.017
     * Test Case: Admin mencoba mengedit materi milik Admin lain (ditolak), sedangkan Super Admin
     *            mengedit materi milik Admin mana pun (diizinkan).
     */
    public function test_tc_material_017_otorisasi_edit_admin_lain_vs_super_admin(): void
    {
        $adminA = $this->makeAdmin(['name' => 'Admin A']);
        $adminB = $this->makeAdmin(['name' => 'Admin B']);
        $superAdmin = $this->makeSuperAdmin();
        $material = $this->material($adminA);

        $this->browse(function (Browser $browser) use ($adminB, $superAdmin, $material) {
            // Langkah 1: Admin B — tombol Edit tidak tampil, URL edit & request PUT ditolak.
            $browser->loginAs($adminB)
                ->visit('/admin/materials')
                ->assertSeeIn("@material-row-{$material->id}", 'Judul Materi Lama')
                ->assertPresent("@view-{$material->id}")
                ->assertMissing("@edit-{$material->id}")
                ->shot('Aqeel/TC.Material.017-step1-daftar');

            $browser->visit("/admin/materials/{$material->id}/edit")
                ->assertSee('403')
                ->assertDontSee('Ubah Materi Pembelajaran Video')
                ->shot('Aqeel/TC.Material.017-step1-url-edit');

            $browser->visit('/admin/materials');
            $this->submitForgedForm($browser, "/admin/materials/{$material->id}", 'PUT', [
                'title' => 'Diubah Admin B',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'description' => 'Diubah tanpa hak.',
            ]);
            $browser->assertPathIs("/admin/materials/{$material->id}")
                ->assertSee('403')
                ->shot('Aqeel/TC.Material.017-step1-request-put');

            $this->assertMaterialUnchanged($material);

            // Langkah 2: Super Admin mengedit materi Admin A.
            $browser->loginAs($superAdmin)
                ->visit('/admin/materials')
                ->clickAndWaitForReload("@edit-{$material->id}")
                ->assertPathIs("/admin/materials/{$material->id}/edit")
                ->type('@title-input', 'Diperbarui oleh Super Admin')
                ->click('@save-button')
                ->waitFor('#confirmSaveModal.show')
                ->clickAndWaitForReload('@save-confirm')
                ->assertPathIs('/admin/materials')
                ->assertSee('Materi pembelajaran berhasil diperbarui.')
                ->assertSeeIn("@material-row-{$material->id}", 'Diperbarui oleh Super Admin')
                ->shot('Aqeel/TC.Material.017-step2-super-admin');
        });

        $this->assertSame('Diperbarui oleh Super Admin', $material->fresh()->title);
        $this->assertSame($adminA->id, $material->fresh()->admin_id);
    }

    // --- Helper ---------------------------------------------------------------

    private function material(User $admin): LearningMaterial
    {
        return LearningMaterial::factory()->create([
            'admin_id' => $admin->id,
            'title' => 'Judul Materi Lama',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'description' => 'Deskripsi materi lama.',
            'thumbnail_url' => 'https://example.com/images/lama.jpg',
            'is_published' => true,
        ]);
    }

    private function assertMaterialUnchanged(LearningMaterial $material): void
    {
        $fresh = $material->fresh();

        $this->assertSame('Judul Materi Lama', $fresh->title);
        $this->assertSame('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $fresh->video_url);
        $this->assertSame('Deskripsi materi lama.', $fresh->description);
        $this->assertSame('https://example.com/images/lama.jpg', $fresh->thumbnail_url);
        $this->assertTrue($fresh->is_published);
    }

    private function openCancelModal(Browser $browser, string $trigger): void
    {
        $browser->click($trigger)
            ->waitFor('#confirmCancelModal.show')
            ->pause(500) // tunggu animasi modal selesai
            ->assertSeeIn('@cancel-modal', 'Batalkan Perubahan?')
            ->assertSeeIn('@cancel-modal', 'Seluruh perubahan yang belum disimpan akan hilang dan data materi tidak akan berubah.')
            ->assertSeeIn('@cancel-keep', 'Tetap Mengedit')
            ->assertSeeIn('@cancel-confirm', 'Ya, Batalkan Perubahan');
    }

    /**
     * Kirim request dengan method palsu (PUT/DELETE) lewat form buatan script, setara
     * memanipulasi request lewat Inspect Element. Token CSRF diambil dari halaman.
     */
    private function submitForgedForm(Browser $browser, string $action, string $method, array $fields = []): void
    {
        $fields = ['_method' => $method] + $fields;
        $json = json_encode($fields, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $browser->waitForReload(function (Browser $browser) use ($action, $json) {
            $browser->script(
                "var form = document.createElement('form'); form.method = 'POST'; form.action = '{$action}';"
                ."var fields = {$json}; fields._token = document.querySelector('meta[name=\"csrf-token\"]').content;"
                .'Object.keys(fields).forEach(function (name) { var input = document.createElement("input");'
                .'input.type = "hidden"; input.name = name; input.value = fields[name]; form.appendChild(input); });'
                .'document.body.appendChild(form); form.submit();'
            );
        });
    }
}
