<?php

namespace App\Http\Requests\SuperAdmin;

use App\Models\HeroSection;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreHeroSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', HeroSection::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'subtitle' => ['nullable', 'string'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'target_url' => [
                'nullable',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }

                    $isInternalPath = str_starts_with($value, '/') && ! str_starts_with($value, '//');
                    $scheme = parse_url($value, PHP_URL_SCHEME);
                    $isValidUrl = filter_var($value, FILTER_VALIDATE_URL) !== false
                        && is_string($scheme)
                        && in_array(strtolower($scheme), ['http', 'https'], true);

                    if (! $isInternalPath && ! $isValidUrl) {
                        $fail('Target URL harus berupa URL yang valid.');
                    }
                },
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul hero wajib diisi.',
            'title.max' => 'Judul hero maksimal 200 karakter.',
            'image.required' => 'Gambar hero wajib diunggah.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WebP.',
            'image.max' => 'Ukuran gambar maksimal 3 MB.',
            'target_url.max' => 'Target URL maksimal 2048 karakter.',
            'is_active.boolean' => 'Status aktif tidak valid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $subtitle = trim((string) $this->input('subtitle'));
        $targetUrl = trim((string) $this->input('target_url'));

        $this->merge([
            'title' => trim((string) $this->input('title')),
            'subtitle' => $subtitle === '' ? null : $subtitle,
            'target_url' => $targetUrl === '' ? null : $targetUrl,
        ]);
    }
}
