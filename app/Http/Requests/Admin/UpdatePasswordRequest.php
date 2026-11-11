<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ProfileRules;
use Illuminate\Foundation\Http\FormRequest;

/**
 * PBI-08 — ubah password admin dari halaman profil.
 */
class UpdatePasswordRequest extends FormRequest
{
    use ProfileRules;

    /**
     * Error disimpan di bag terpisah agar hanya tampil di card "Ubah Password".
     *
     * @var string
     */
    protected $errorBag = 'updatePassword';

    /**
     * Otorisasi dilakukan lewat UserProfilePolicy di controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->passwordUpdateRules();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->passwordUpdateMessages();
    }
}
