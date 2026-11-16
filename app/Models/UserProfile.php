<?php

namespace App\Models;

use App\Enums\EducationLevel;
use App\Enums\Gender;
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
}
