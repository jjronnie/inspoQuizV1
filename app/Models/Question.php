<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    // The fields that are mass assignable
    protected $fillable = [
        'quiz_id',
        'text',
        'points',
    ];

    /**
     * Get the quiz that owns the question.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the answers for the question.
     */
    public function answers(): HasMany
    {
        // Due to the cascade setting in the migration, deleting a Question
        // will automatically delete all associated Answers.
        return $this->hasMany(Answer::class);
    }
}
