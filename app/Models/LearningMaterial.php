<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class LearningMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'title',
        'description',
        'video_url',
        'thumbnail_url',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'material_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(LearningReview::class, 'material_id');
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * Rata-rata rating (1 desimal), null bila belum ada ulasan.
     * Di halaman daftar, pakai withAvg('reviews', 'rating') agar tidak N+1.
     */
    protected function averageRating(): Attribute
    {
        return Attribute::get(function (): ?float {
            if (array_key_exists('reviews_avg_rating', $this->attributes)) {
                $avg = $this->attributes['reviews_avg_rating'];
            } elseif ($this->relationLoaded('reviews')) {
                $avg = $this->reviews->avg('rating');
            } else {
                $avg = $this->reviews()->avg('rating');
            }

            return $avg === null ? null : round((float) $avg, 1);
        });
    }

    /**
     * Jumlah ulasan. Di halaman daftar, pakai withCount('reviews') agar tidak N+1.
     */
    protected function reviewsCount(): Attribute
    {
        return Attribute::get(function (): int {
            if (array_key_exists('reviews_count', $this->attributes)) {
                return (int) $this->attributes['reviews_count'];
            }

            return $this->relationLoaded('reviews') ? $this->reviews->count() : $this->reviews()->count();
        });
    }

    /**
     * URL tampilan thumbnail untuk path storage lokal maupun URL eksternal.
     */
    public function thumbnailSrc(): ?string
    {
        if (blank($this->thumbnail_url)) {
            return null;
        }

        if (str_starts_with($this->thumbnail_url, 'http://')
            || str_starts_with($this->thumbnail_url, 'https://')
            || str_starts_with($this->thumbnail_url, '/')) {
            return $this->thumbnail_url;
        }

        return Storage::disk('public')->url($this->thumbnail_url);
    }

    /**
     * Hapus file thumbnail dari storage lokal bila bukan URL eksternal.
     */
    public function deleteStoredThumbnail(): void
    {
        if (blank($this->thumbnail_url)) {
            return;
        }

        if (str_starts_with($this->thumbnail_url, 'http://')
            || str_starts_with($this->thumbnail_url, 'https://')
            || str_starts_with($this->thumbnail_url, '/')) {
            return;
        }

        if (Storage::disk('public')->exists($this->thumbnail_url)) {
            Storage::disk('public')->delete($this->thumbnail_url);
        }
    }

    /**
     * Konversi video_url (YouTube / Google Drive) menjadi URL embed (PRD §6.1).
     */
    public function videoEmbedUrl(): ?string
    {
        if (blank($this->video_url)) {
            return null;
        }

        $url = trim($this->video_url);

        // YouTube: youtube.com/watch?v=ID, youtu.be/ID, youtube.com/shorts/ID, youtube.com/embed/ID
        if (preg_match('~(?:youtube(?:-nocookie)?\.com/(?:watch\?.*v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~i', $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        // Google Drive: drive.google.com/file/d/ID/view -> /preview
        if (preg_match('~drive\.google\.com/file/d/([A-Za-z0-9_-]+)~i', $url, $matches)) {
            return 'https://drive.google.com/file/d/'.$matches[1].'/preview';
        }

        return $url;
    }
}
