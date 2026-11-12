<?php

namespace App\Http\Requests\SuperAdmin;

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Article::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'content' => ['required', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'status' => ['required', Rule::enum(ArticleStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul artikel wajib diisi.',
            'title.max' => 'Judul artikel maksimal 200 karakter.',
            'excerpt.max' => 'Ringkasan maksimal 300 karakter.',
            'content.required' => 'Konten artikel wajib diisi.',
            'cover_image.image' => 'File sampul harus berupa gambar.',
            'cover_image.mimes' => 'Format sampul harus JPG, JPEG, PNG, atau WebP.',
            'cover_image.max' => 'Ukuran sampul maksimal 3 MB.',
            'cover.image' => 'File sampul harus berupa gambar.',
            'cover.mimes' => 'Format sampul harus JPG, JPEG, PNG, atau WebP.',
            'cover.max' => 'Ukuran sampul maksimal 3 MB.',
            'status.required' => 'Status publikasi wajib dipilih.',
            'status.'.Rule::enum(ArticleStatus::class)::class => 'Status publikasi tidak valid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $excerpt = trim((string) $this->input('excerpt'));

        $this->merge([
            'title' => trim((string) $this->input('title')),
            'excerpt' => $excerpt === '' ? null : $excerpt,
            'content' => trim((string) $this->input('content')),
        ]);
    }
}
