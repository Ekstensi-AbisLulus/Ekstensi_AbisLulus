<?php

namespace App\Models;

use App\Enums\EducationLevel;
use App\Enums\Gender;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'phone',
        'education_level',
        'institution',
        'graduation_year',
        'bio',
        'birth_date',
        'gender',
        'address',
        'linkedin_url',
        'avatar_path',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'graduation_year' => 'integer',
            'gender' => Gender::class,
            'education_level' => EducationLevel::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * URL publik foto profil (disk `public` via symlink public/storage), null bila belum ada foto.
     * Pakai asset() agar mengikuti host & port yang sedang dibuka, bukan APP_URL di .env.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->avatar_path
            ? asset('storage/'.$this->avatar_path)
            : null);
    }

    /**
     * Profil dianggap lengkap bila No. HP sudah diisi (PBI-07).
     */
    public function isComplete(): bool
    {
        return filled($this->phone);
    }
}
