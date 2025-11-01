<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
     use HasFactory;
      protected $fillable = [
        'title',
        'slug',
        'description',
        'time_limit_minutes',
        'is_published',
        'created_by',
    ];

     public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the questions for the quiz.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Cast is_published to a boolean.
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }
}
