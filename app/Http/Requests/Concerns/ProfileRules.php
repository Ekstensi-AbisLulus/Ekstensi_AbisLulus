<?php

namespace App\Http\Requests\Concerns;

use App\Rules\PhoneNumber;

/**
 * Aturan validasi profil yang dipakai bersama oleh profil user (PBI-04/05)
 * dan profil admin (PBI-07/08) agar No. HP & foto divalidasi dengan cara yang sama.
 */
trait ProfileRules
{
    /**
     * @return list<mixed>
     */
    protected function phoneRules(bool $required = true): array
    {
        return [$required ? 'required' : 'nullable', 'string', new PhoneNumber];
    }

    /**
     * Foto profil opsional: jpg/jpeg/png, maks. 2 MB.
     *
     * @return list<string>
     */
    protected function avatarRules(): array
    {
        return ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'];
    }

    /**
     * @return array<string, string>
     */
    protected function profileMessages(): array
    {
        return [
            'phone.required' => 'No. HP/WhatsApp wajib diisi.',
            'avatar.image' => 'Foto profil harus berupa gambar.',
            'avatar.mimes' => 'Foto profil harus berformat JPG, JPEG, atau PNG.',
            'avatar.max' => 'Ukuran foto profil maksimal 2 MB.',
            'avatar.uploaded' => 'Foto profil gagal diunggah. Pastikan ukurannya maksimal 2 MB.',
        ];
    }

    /**
     * Ubah password: password lama wajib & cocok, password baru min. 8 karakter & dikonfirmasi.
     *
     * @return array<string, list<string>>
     */
    protected function passwordUpdateRules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function passwordUpdateMessages(): array
    {
        return [
            'current_password.required' => 'Password lama wajib diisi.',
            'current_password.current_password' => 'Password lama tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.different' => 'Password baru harus berbeda dari password lama.',
        ];
    }

    /**
     * Rapikan No. HP sebelum divalidasi: buang spasi, tanda hubung, titik, dan kurung.
     */
    protected function normalizePhone(): void
    {
        if (is_string($this->input('phone'))) {
            $this->merge(['phone' => preg_replace('/[\s\-.()]/', '', $this->input('phone'))]);
        }
    }
}
