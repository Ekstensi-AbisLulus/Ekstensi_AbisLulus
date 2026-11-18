<?php

namespace Tests\Browser\Aqeel;

use App\Models\LearningMaterial;
use App\Models\LearningReview;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Concerns\CreatesTestUsers;
use Tests\Browser\Concerns\MakesUploadFiles;
use Tests\DuskTestCase;

/**
 * PBI-13 — Hapus Materi Pembelajaran Video (TC.Material.018–020).
 */
#[Group('aqeel')]
class MaterialDeleteTest extends DuskTestCase
{
    use CleansMaterialUploads, CreatesTestUsers, DatabaseMigrations, MakesUploadFiles;

    /**
     * Case ID  : TC.Material.018
     * Test Case: Admin menekan tombol Batal pada modal konfirmasi hapus materi.
     */
    public function test_tc_material_018_batal_hapus_materi(): void
    {
        $admin = $this->makeAdmin();
        $material = $this->material($admin, 'Materi Tidak Jadi Dihapus');
        $id = $material->id;

        $this->browse(function (Browser $browser) use ($admin, $id) {
            // Dari halaman Daftar Materi: buka modal lalu Batal.
            $browser->loginAs($admin)
                ->visit('/admin/materials')
                ->click("@delete-{$id}")
                ->waitFor("#deleteMaterialModal-{$id}.show")
                ->pause(500) // tunggu animasi modal selesai
                ->assertSeeIn("@delete-modal-{$id}", 'Konfirmasi Hapus Materi')
                ->assertSeeIn("@delete-modal-{$id}", 'Apakah Anda yakin ingin menghapus materi pembelajaran "Materi Tidak Jadi Dihapus"?')
                ->assertSeeIn("@delete-modal-{$id}", 'Tindakan ini tidak dapat dibatalkan.')
                ->shot('Aqeel/TC.Material.018-step1-daftar');

            $browser->click("@delete-cancel-{$id}")
                ->waitUntilMissing("#deleteMaterialModal-{$id}.show")
                ->waitUntilMissing('.modal-backdrop')
                ->assertPathIs('/admin/materials')
                ->assertSeeIn("@material-row-{$id}", 'Materi Tidak Jadi Dihapus')
                ->shot('Aqeel/TC.Material.018-step2-batal');

            // Dari halaman Detail Materi: buka modal lalu tombol tutup (X).
            $browser->visit("/admin/materials/{$id}")
                ->click('@delete-button')
                ->waitFor('#deleteMaterialModal.show')
                ->pause(500) // tunggu animasi modal selesai
                ->assertSeeIn('@delete-modal', 'Konfirmasi Hapus Materi')
                ->assertSeeIn('@delete-modal', 'Apakah Anda yakin ingin menghapus materi pembelajaran "Materi Tidak Jadi Dihapus"?')
                ->shot('Aqeel/TC.Material.018-step1-detail');

            $browser->click('@delete-close')
                ->waitUntilMissing('#deleteMaterialModal.show')
                ->waitUntilMissing('.modal-backdrop')
                ->assertPathIs("/admin/materials/{$id}")
                ->assertSee('Materi Tidak Jadi Dihapus')
                ->shot('Aqeel/TC.Material.018-step2-tutup-x');
        });

        $this->assertDatabaseHas('learning_materials', ['id' => $id, 'title' => 'Materi Tidak Jadi Dihapus']);
    }

    /**
     * Case ID  : TC.Material.019
     * Test Case: Admin menghapus materi pembelajaran miliknya sendiri melalui modal konfirmasi hapus.
     */
    public function test_tc_material_019_hapus_materi_dengan_cascade(): void
    {
        $admin = $this->makeAdmin();
        $material = $this->material($admin, 'Materi Akan Dihapus');
        $thumbnail = $this->giveStoredThumbnail($material);
        $id = $material->id;

        foreach ([5, 4] as $rating) {
            LearningReview::factory()->create([
                'material_id' => $id,
                'user_id' => $this->makeUser([], null)->id,
                'rating' => $rating,
            ]);
        }
        $quiz = Quiz::factory()->create(['created_by' => $admin->id, 'material_id' => $id, 'title' => 'Kuis Materi Dihapus']);

        $this->assertTrue(Storage::disk('public')->exists($thumbnail));
        $this->assertSame(2, LearningReview::where('material_id', $id)->count());

        $this->browse(function (Browser $browser) use ($admin, $id) {
            // Langkah 1: klik Hapus Materi → modal.
            $browser->loginAs($admin)
                ->visit('/admin/materials')
                ->click("@delete-{$id}")
                ->waitFor("#deleteMaterialModal-{$id}.show")
                ->pause(500) // tunggu animasi modal selesai
                ->assertSeeIn("@delete-modal-{$id}", 'Konfirmasi Hapus Materi')
                ->shot('Aqeel/TC.Material.019-step1');

            // Langkah 2: Ya, Hapus Materi.
            $browser->clickAndWaitForReload("@delete-confirm-{$id}")
                ->assertPathIs('/admin/materials')
                ->assertSee('Materi pembelajaran berhasil dihapus.')
                ->assertMissing("@material-row-{$id}")
                ->assertDontSee('Materi Akan Dihapus')
                ->shot('Aqeel/TC.Material.019-step2');
        });

        $this->assertDatabaseMissing('learning_materials', ['id' => $id]);
        $this->assertSame(0, LearningReview::where('material_id', $id)->count(), 'Ulasan terkait harus ikut terhapus.');
        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'title' => 'Kuis Materi Dihapus', 'material_id' => null]);
        $this->assertFalse(Storage::disk('public')->exists($thumbnail), 'File thumbnail harus terhapus dari storage.');
    }

    /**
     * Case ID  : TC.Material.020
     * Test Case: Admin mencoba menghapus materi milik Admin lain (ditolak), sedangkan Super Admin
     *            menghapus materi milik Admin mana pun (diizinkan).
     */
    public function test_tc_material_020_otorisasi_hapus_admin_lain_vs_super_admin(): void
    {
        $adminA = $this->makeAdmin(['name' => 'Admin A']);
        $adminB = $this->makeAdmin(['name' => 'Admin B']);
        $superAdmin = $this->makeSuperAdmin();
        $material = $this->material($adminA, 'Materi Milik Admin A');
        $id = $material->id;

        $this->browse(function (Browser $browser) use ($adminB, $superAdmin, $id) {
            // Langkah 1: Admin B — tombol Hapus tidak tampil, request DELETE ditolak.
            $browser->loginAs($adminB)
                ->visit('/admin/materials')
                ->assertSeeIn("@material-row-{$id}", 'Materi Milik Admin A')
                ->assertMissing("@delete-{$id}")
                ->shot('Aqeel/TC.Material.020-step1-daftar')
                ->visit("/admin/materials/{$id}")
                ->assertSee('Materi Milik Admin A')
                ->assertMissing('@delete-button')
                ->shot('Aqeel/TC.Material.020-step1-detail');

            $this->submitForgedForm($browser, "/admin/materials/{$id}", 'DELETE');
            $browser->assertPathIs("/admin/materials/{$id}")
                ->assertSee('403')
                ->shot('Aqeel/TC.Material.020-step1-request-delete');

            $this->assertDatabaseHas('learning_materials', ['id' => $id]);

            // Langkah 2: Super Admin menghapus materi Admin A.
            $browser->loginAs($superAdmin)
                ->visit('/admin/materials')
                ->click("@delete-{$id}")
                ->waitFor("#deleteMaterialModal-{$id}.show")
                ->pause(500) // tunggu animasi modal selesai
                ->clickAndWaitForReload("@delete-confirm-{$id}")
                ->assertPathIs('/admin/materials')
                ->assertSee('Materi pembelajaran berhasil dihapus.')
                ->assertDontSee('Materi Milik Admin A')
                ->shot('Aqeel/TC.Material.020-step2-super-admin');
        });

        $this->assertDatabaseMissing('learning_materials', ['id' => $id]);
    }

    // --- Helper ---------------------------------------------------------------

    private function material(User $admin, string $title): LearningMaterial
    {
        return LearningMaterial::factory()->create([
            'admin_id' => $admin->id,
            'title' => $title,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'thumbnail_url' => null,
            'is_published' => true,
        ]);
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
