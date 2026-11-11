<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
