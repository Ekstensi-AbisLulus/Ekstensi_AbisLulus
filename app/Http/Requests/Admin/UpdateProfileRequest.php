<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ProfileRules;
use Illuminate\Foundation\Http\FormRequest;

/**
 * PBI-07/08 — simpan profil admin (nama, No. HP, institusi/komunitas, bio, foto).
 */
class UpdateProfileRequest extends FormRequest
{
    use ProfileRules;

    /**
     * Otorisasi dilakukan lewat UserProfilePolicy di controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizePhone();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => $this->phoneRules(),
            'institution' => ['nullable', 'string', 'max:150'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => $this->avatarRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'phone' => 'No. HP/WhatsApp',
            'institution' => 'institusi/komunitas',
            'bio' => 'bio',
            'avatar' => 'foto profil',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->profileMessages() + [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 100 karakter.',
            'institution.max' => 'Institusi/komunitas maksimal 150 karakter.',
            'bio.max' => 'Bio maksimal 1000 karakter.',
        ];
    }
}
