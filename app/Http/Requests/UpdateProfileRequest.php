<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isUser() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{10,15}$/'],
            'education_level' => ['required', Rule::in(['Pelajar SMA/SMK', 'Mahasiswa', 'Fresh Graduate'])],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'address' => ['nullable', 'string'],
            'institution' => ['nullable', 'string', 'max:150'],
            'graduation_year' => ['nullable', 'regex:/^\d{4}$/', 'integer', 'between:1990,'.(now()->year + 5)],
            'bio' => ['nullable', 'string', 'max:1000'],
            'linkedin_url' => ['nullable', 'url', 'max:2048'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'No. HP harus berisi 10–15 angka dan boleh diawali tanda +.',
            'education_level.in' => 'Pilih status pendidikan yang tersedia.',
            'graduation_year.between' => 'Tahun lulus harus antara 1990 dan '.(now()->year + 5).'.',
        ];
    }
}
