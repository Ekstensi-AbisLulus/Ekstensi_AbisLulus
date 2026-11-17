<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'super_admin_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image_url',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Article $article): void {
            if (blank($article->slug)) {
                $article->slug = static::uniqueSlug($article->title, $article->getKey());
            }

            if ($article->status === ArticleStatus::Published && $article->published_at === null) {
                $article->published_at = now();
            }
        });
    }

    /**
     * Slug dari judul; ditambah akhiran -2, -3, ... bila sudah dipakai.
     */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($title), 200, '') ?: 'artikel';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'super_admin_id');
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', ArticleStatus::Published);
    }

    /**
     * URL tampilan sampul untuk path storage lokal maupun URL eksternal.
     */
    public function coverImageSrc(): ?string
    {
        if (blank($this->cover_image_url)) {
            return null;
        }

        if (str_starts_with($this->cover_image_url, 'http://')
            || str_starts_with($this->cover_image_url, 'https://')
            || str_starts_with($this->cover_image_url, '/')) {
            return $this->cover_image_url;
        }

        return Storage::disk('public')->url($this->cover_image_url);
    }

    /**
     * Hapus file sampul dari storage publik jika berupa path lokal.
     */
    public function deleteStoredCover(): void
    {
        if (blank($this->cover_image_url)
            || str_starts_with($this->cover_image_url, 'http://')
            || str_starts_with($this->cover_image_url, 'https://')
            || str_starts_with($this->cover_image_url, '/')) {
            return;
        }

        Storage::disk('public')->delete($this->cover_image_url);
    }
}
