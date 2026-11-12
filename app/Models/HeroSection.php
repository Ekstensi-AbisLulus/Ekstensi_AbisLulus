<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class HeroSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'super_admin_id',
        'title',
        'subtitle',
        'image_url',
        'target_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'super_admin_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * URL tampilan gambar untuk path storage lokal maupun URL eksternal.
     */
    public function imageSrc(): string
    {
        if (str_starts_with($this->image_url, 'http://')
            || str_starts_with($this->image_url, 'https://')
            || str_starts_with($this->image_url, '/')) {
            return $this->image_url;
        }

        return Storage::disk('public')->url($this->image_url);
    }
}
