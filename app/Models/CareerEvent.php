<?php

namespace App\Models;

use App\Enums\EventCategory;
use App\Enums\EventPlatform;
use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'title',
        'description',
        'start_at',
        'end_at',
        'meeting_url',
        'platform',
        'capacity',
        'status',
        'category',
        'thumbnail_url',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'capacity' => 'integer',
            'platform' => EventPlatform::class,
            'status' => EventStatus::class,
            'category' => EventCategory::class,
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class, 'event_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_registrations', 'event_id', 'user_id')
            ->withPivot(['id', 'status', 'registered_at']);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', EventStatus::Published);
    }

    public function scopeUpcoming(Builder $query): void
    {
        $query->where('start_at', '>', now());
    }

    /**
     * Sisa kuota; null = tanpa batas. Pendaftaran yang dibatalkan tidak dihitung.
     */
    protected function remainingSeats(): Attribute
    {
        return Attribute::get(function (): ?int {
            if ($this->capacity === null) {
                return null;
            }

            $taken = $this->registrations()
                ->whereIn('status', [RegistrationStatus::Registered, RegistrationStatus::Attended])
                ->count();

            return max(0, $this->capacity - $taken);
        });
    }
}
