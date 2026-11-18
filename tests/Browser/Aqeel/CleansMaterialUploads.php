<?php

namespace Tests\Browser\Aqeel;

use App\Models\LearningMaterial;
use Illuminate\Support\Facades\Storage;

/**
 * Helper khusus test E-Learning (Aqeel).
 *
 * Thumbnail yang diunggah lewat browser disimpan server ke disk "public" yang asli
 * (storage/app/public/materials). Trait ini mencatat isi folder sebelum test dan
 * menghapus file baru setelah test, tanpa bergantung pada database.
 */
trait CleansMaterialUploads
{
    /** @var list<string> */
    private array $materialFilesBefore = [];

    protected function setUpCleansMaterialUploads(): void
    {
        $this->materialFilesBefore = Storage::disk('public')->files('materials');
    }

    protected function tearDownCleansMaterialUploads(): void
    {
        $created = array_diff(Storage::disk('public')->files('materials'), $this->materialFilesBefore);

        if ($created !== []) {
            Storage::disk('public')->delete(array_values($created));
        }
    }

    /**
     * Simpan file thumbnail nyata di storage lokal dan pasang ke materi.
     */
    protected function giveStoredThumbnail(LearningMaterial $material): string
    {
        $path = 'materials/dusk-lama-'.$material->id.'-'.uniqid().'.png';
        Storage::disk('public')->put($path, file_get_contents($this->imageFile('png', 20)));

        $material->update(['thumbnail_url' => $path]);

        return $path;
    }
}
