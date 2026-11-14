<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = request()->user()->load('profile');

        return view('profile.edit', [
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $user->update(['name' => $data['name']]);

        if (filled($data['password'] ?? null) && Hash::check($data['current_password'], $user->password)) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $profileData = collect($data)->except(['name', 'avatar', 'current_password', 'password'])->all();
        if ($request->hasFile('avatar')) {
            $oldAvatar = $user->profile?->avatar_path;
            $profileData['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
            if ($oldAvatar) {
                Storage::disk('public')->delete($oldAvatar);
            }
        }

        $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil disimpan');
    }

    public function destroyAvatar(): RedirectResponse
    {
        $user = request()->user();
        $profile = $user->profile;

        if ($profile?->avatar_path) {
            Storage::disk('public')->delete($profile->avatar_path);
            $profile->update(['avatar_path' => null]);
        }

        return redirect()->route('profile.edit')->with('success', 'Foto profil berhasil dihapus');
    }
}
