<?php

namespace Tests\Browser\Oktavia;

use App\Models\Article;
use App\Models\HeroSection;
use Illuminate\Support\Facades\Storage;

/**
 * Helper khusus test Landing Page (Oktavia).
 *
 * Gambar hero & sampul artikel yang diunggah lewat browser disimpan server ke disk "public" asli
 * (storage/app/public/hero dan storage/app/public/articles). Trait ini mencatat isi folder sebelum
 * test dan menghapus file baru setelah test, tanpa bergantung pada database.
 */
trait CleansLandingUploads
{
    /** @var array<string, list<string>> */
    private array $landingFilesBefore = [];

    protected function setUpCleansLandingUploads(): void
    {
        foreach (['hero', 'articles'] as $directory) {
            $this->landingFilesBefore[$directory] = Storage::disk('public')->files($directory);
        }
    }

    protected function tearDownCleansLandingUploads(): void
    {
        foreach ($this->landingFilesBefore as $directory => $before) {
            $created = array_diff(Storage::disk('public')->files($directory), $before);

            if ($created !== []) {
                Storage::disk('public')->delete(array_values($created));
            }
        }
    }

    /**
     * Simpan gambar hero nyata di storage lokal dan pasang ke hero.
     */
    protected function giveStoredHeroImage(HeroSection $hero): string
    {
        $path = 'hero/dusk-lama-'.$hero->id.'-'.uniqid().'.png';
        Storage::disk('public')->put($path, file_get_contents($this->imageFile('png', 20)));

        $hero->update(['image_url' => $path]);

        return $path;
    }

    /**
     * Simpan sampul nyata di storage lokal dan pasang ke artikel.
     */
    protected function giveStoredCover(Article $article): string
    {
        $path = 'articles/dusk-lama-'.$article->id.'-'.uniqid().'.png';
        Storage::disk('public')->put($path, file_get_contents($this->imageFile('png', 20)));

        $article->update(['cover_image_url' => $path]);

        return $path;
    }
}
