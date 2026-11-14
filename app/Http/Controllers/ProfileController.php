<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
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

        $profileData = collect($data)->except(['name', 'avatar'])->all();
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
}
