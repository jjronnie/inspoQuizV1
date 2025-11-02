<?php

namespace App\Models;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
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

    protected static function boot()
    {
        parent::boot();

        // --- Create Hook: Generate unique slug if not present ---
        static::creating(function ($quiz) {
            if (empty($quiz->slug)) {
                $quiz->slug = self::generateUniqueSlug($quiz->title);
            }
        });

        // --- Update Hook: Re-generate unique slug if the title changes ---
        static::updating(function ($quiz) {
            // Only update the slug if the title has been changed
            if ($quiz->isDirty('title')) {
                $quiz->slug = self::generateUniqueSlug($quiz->title);
            }
        });
    }

    protected static function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        // Check if a record with the generated slug already exists
        while (static::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }

}
