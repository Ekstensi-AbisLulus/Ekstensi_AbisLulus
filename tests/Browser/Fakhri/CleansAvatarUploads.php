<?php

namespace Tests\Browser\Fakhri;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Helper khusus test profil admin (Fakhri).
 *
 * Upload foto lewat browser disimpan server ke disk "public" yang asli
 * (storage/app/public/avatars). Trait ini mencatat isi folder avatars sebelum
 * test dan menghapus file baru setelah test, tanpa bergantung pada database
 * (yang sudah di-rollback oleh DatabaseMigrations saat tearDown).
 */
trait CleansAvatarUploads
{
    /** @var list<string> */
    private array $avatarFilesBefore = [];

    protected function setUpCleansAvatarUploads(): void
    {
        $this->avatarFilesBefore = Storage::disk('public')->files('avatars');
    }

    protected function tearDownCleansAvatarUploads(): void
    {
        $created = array_diff(Storage::disk('public')->files('avatars'), $this->avatarFilesBefore);

        if ($created !== []) {
            Storage::disk('public')->delete(array_values($created));
        }
    }

    /**
     * Simpan foto profil "lama" yang nyata di disk public dan pasang ke profil user.
     */
    protected function giveAvatar(User $user): string
    {
        $path = 'avatars/dusk-lama-'.$user->id.'-'.uniqid().'.png';
        Storage::disk('public')->put($path, file_get_contents($this->imageFile('png', 20)));

        $user->profile()->update(['avatar_path' => $path]);

        return $path;
    }
}
