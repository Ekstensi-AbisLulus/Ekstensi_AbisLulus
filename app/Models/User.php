<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Default sama dengan default kolom di DB.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'user',
        'status' => 'active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    public function isUser(): bool
    {
        return $this->role === UserRole::User;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    /**
     * Nama route tujuan setelah login (PBI-02).
     */
    public function homeRoute(): string
    {
        return match ($this->role) {
            UserRole::SuperAdmin => 'super-admin.dashboard',
            UserRole::Admin => 'admin.dashboard',
            default => 'home',
        };
    }

    /**
     * Kirim email reset password berbahasa Indonesia (PBI-03).
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class, 'admin_id');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'created_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CareerEvent::class, 'admin_id');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(LearningReview::class);
    }

    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function registeredEvents(): BelongsToMany
    {
        return $this->belongsToMany(CareerEvent::class, 'event_registrations', 'user_id', 'event_id')
            ->withPivot(['id', 'status', 'registered_at']);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'super_admin_id');
    }

    public function heroSections(): HasMany
    {
        return $this->hasMany(HeroSection::class, 'super_admin_id');
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }
}
