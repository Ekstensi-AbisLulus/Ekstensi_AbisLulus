<?php

namespace App\Http\Requests\Admin;

use App\Models\LearningMaterial;
use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', LearningMaterial::class) ?? false;
    }

    /**
     * Validasi tambah materi pembelajaran video (PRD §6.1 & §7 PBI-10).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'video_url' => ['required', 'string', 'url:http,https', 'max:2048'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'thumbnail_url' => ['nullable', 'string', 'url:http,https', 'max:2048'],
            'is_published' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul materi wajib diisi.',
            'title.max' => 'Judul materi maksimal 200 karakter.',
            'description.required' => 'Deskripsi materi wajib diisi.',
            'video_url.required' => 'Link video wajib diisi.',
            'video_url.url' => 'Link video harus berupa URL yang valid (http/https).',
            'video_url.max' => 'Link video maksimal 2048 karakter.',
            'thumbnail.image' => 'File thumbnail harus berupa gambar.',
            'thumbnail.mimes' => 'Format gambar thumbnail harus JPG, JPEG, PNG, atau WebP.',
            'thumbnail.max' => 'Ukuran gambar thumbnail maksimal 2 MB.',
            'thumbnail_url.url' => 'URL thumbnail harus berupa URL yang valid (http/https).',
            'thumbnail_url.max' => 'URL thumbnail maksimal 2048 karakter.',
            'is_published.boolean' => 'Status publikasi tidak valid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $thumbnailUrl = trim((string) $this->input('thumbnail_url'));

        $this->merge([
            'title' => trim((string) $this->input('title')),
            'description' => trim((string) $this->input('description')),
            'video_url' => trim((string) $this->input('video_url')),
            'thumbnail_url' => $thumbnailUrl === '' ? null : $thumbnailUrl,
        ]);
    }
}
