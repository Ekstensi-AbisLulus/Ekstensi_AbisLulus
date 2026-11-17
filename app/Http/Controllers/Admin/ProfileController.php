<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePasswordRequest;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

/**
 * PBI-07 Melengkapi profil admin & PBI-08 Mengedit profil admin (PRD §7 F3).
 */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('profile');
        $profile = $this->profileOf($user);

        $this->authorize('update', $profile);

        return view('admin.profile.edit', compact('user', 'profile'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user()->load('profile');
        $profile = $this->profileOf($user);

        $this->authorize('update', $profile);

        $data = $request->validated();
        $oldAvatar = $profile->avatar_path;
        $newAvatar = $request->hasFile('avatar')
            ? $request->file('avatar')->store('avatars', 'public')
            : null;

        try {
            DB::transaction(function () use ($user, $data, $newAvatar) {
                $user->update(['name' => $data['name']]);

                UserProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'phone' => $data['phone'],
                        'institution' => $data['institution'] ?? null,
                        'bio' => $data['bio'] ?? null,
                    ] + ($newAvatar ? ['avatar_path' => $newAvatar] : []),
                );
            });
        } catch (Throwable $e) {
            // Data gagal disimpan: buang foto yang baru diunggah agar tidak menjadi file yatim.
            if ($newAvatar) {
                Storage::disk('public')->delete($newAvatar);
            }

            throw $e;
        }

        if ($newAvatar && $oldAvatar) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return back()->with('success', 'Profil berhasil disimpan');
    }

    /**
     * PBI-08 — ubah password (password lama wajib & cocok, divalidasi di FormRequest).
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user()->load('profile');

        $this->authorize('update', $this->profileOf($user));

        $user->update(['password' => $request->validated('password')]);

        return back()->with('success', 'Password berhasil diubah');
    }

    /**
     * PBI-08 — hapus foto profil, kembali ke avatar default (inisial).
     */
    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user()->load('profile');
        $profile = $this->profileOf($user);

        $this->authorize('update', $profile);

        if (! $profile->avatar_path) {
            return back()->with('error', 'Belum ada foto profil yang bisa dihapus.');
        }

        $oldAvatar = $profile->avatar_path;
        $profile->update(['avatar_path' => null]);
        Storage::disk('public')->delete($oldAvatar);

        return back()->with('success', 'Foto profil berhasil dihapus');
    }

    /**
     * Profil milik user; objek baru (belum tersimpan) bila barisnya belum ada.
     */
    private function profileOf(User $user): UserProfile
    {
        return $user->profile ?? new UserProfile(['user_id' => $user->id]);
    }
}
