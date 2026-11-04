<?php

namespace Tests\Feature;

use App\Models\Pembelajaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembelajaranTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_and_edit_forms_load(): void
    {
        $this->get(route('pembelajaran.create'))->assertOk();

        $pembelajaran = Pembelajaran::create([
            'judul' => 'Persiapan Karier',
            'kategori' => 'Soft Skill',
            'deskripsi' => 'Materi persiapan wawancara.',
            'link_video' => 'https://example.com/video',
        ]);

        $this->get(route('pembelajaran.edit', $pembelajaran))
            ->assertOk()
            ->assertSee('Persiapan Karier');
    }

    public function test_index_displays_saved_pembelajaran(): void
    {
        Pembelajaran::create([
            'judul' => 'Persiapan Karier',
            'kategori' => 'Soft Skill',
            'deskripsi' => 'Materi persiapan wawancara.',
            'link_video' => 'https://example.com/video',
        ]);

        $this->get(route('pembelajaran.index'))
            ->assertOk()
            ->assertSee('Persiapan Karier')
            ->assertSee('Soft Skill')
            ->assertSee('Materi persiapan wawancara.');
    }

    public function test_pembelajaran_can_be_created_updated_and_deleted(): void
    {
        $payload = [
            'judul' => 'Persiapan Karier',
            'kategori' => 'Soft Skill',
            'deskripsi' => 'Materi persiapan wawancara.',
            'link_video' => 'https://example.com/video',
        ];

        $this->post(route('pembelajaran.store'), $payload)
            ->assertRedirect(route('pembelajaran.index'));

        $pembelajaran = Pembelajaran::firstOrFail();
        $this->assertDatabaseHas('pembelajarans', ['judul' => 'Persiapan Karier']);

        $this->put(route('pembelajaran.update', $pembelajaran), [
            ...$payload,
            'judul' => 'Wawancara Kerja',
        ])->assertRedirect(route('pembelajaran.index'));

        $this->assertDatabaseHas('pembelajarans', ['judul' => 'Wawancara Kerja']);

        $this->delete(route('pembelajaran.destroy', $pembelajaran))
            ->assertRedirect(route('pembelajaran.index'));

        $this->assertDatabaseMissing('pembelajarans', ['id' => $pembelajaran->id]);
    }
}
