<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'score',
        'correct_count',
        'incorrect_count',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'correct_count' => 'integer',
            'incorrect_count' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'attempt_id');
    }

    /**
     * Hitung & simpan skor dari jawaban tersimpan: score = benar / total soal × 100.
     * Soal yang tidak dijawab dihitung salah.
     */
    public function calculateScore(): static
    {
        $total = Question::where('quiz_id', $this->quiz_id)->count();
        $correct = $this->answers()->where('is_correct', true)->count();

        $this->forceFill([
            'correct_count' => $correct,
            'incorrect_count' => max(0, $total - $correct),
            'score' => $total > 0 ? round($correct / $total * 100, 2) : 0,
            'completed_at' => $this->completed_at ?? now(),
        ])->save();

        return $this;
    }
}
