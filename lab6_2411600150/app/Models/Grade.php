<?php

namespace App\Models;

use App\Observers\GradeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([GradeObserver::class])]
class Grade extends Model
{
    const TERMS = [
        'prelim' => 'Prelim',
        'midterm' => 'Midterm',
        'finals' => 'Finals',
    ];

    // Grades below this are failing (4.00 scale, same as GPA)
    const PASSING_GRADE = 1.0;

    protected $fillable = [
        'student_id',
        'user_id',
        'subject',
        'units',
        'term',
        'grade',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'units' => 'integer',
        ];
    }

    // ==========================================
    // Relationships
    // ==========================================

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Teacher who recorded the grade.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==========================================
    // Query Scopes
    // ==========================================

    public function scopeTerm($query, string $term)
    {
        return $query->where('term', $term);
    }

    public function scopeFailing($query)
    {
        return $query->where('grade', '<', self::PASSING_GRADE);
    }

    public function scopeRecent($query)
    {
        return $query->latest()->latest('id');
    }

    // ==========================================
    // Display Helpers
    // ==========================================

    public function getTermLabelAttribute(): string
    {
        return self::TERMS[$this->term] ?? ucfirst($this->term);
    }

    public function isPassing(): bool
    {
        return (float) $this->grade >= self::PASSING_GRADE;
    }

    public function getResultAttribute(): string
    {
        return $this->isPassing() ? 'Passed' : 'Failed';
    }

    /**
     * Bootstrap badge color based on the grade.
     */
    public function getGradeColorAttribute(): string
    {
        $grade = (float) $this->grade;

        if ($grade >= Student::GPA_GOOD_STANDING) {
            return 'success';
        }

        if ($grade >= self::PASSING_GRADE) {
            return 'warning';
        }

        return 'danger';
    }
}
