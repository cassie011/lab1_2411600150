<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'student_id',
        'name',
        'program',
        'year_level',
        'units',
        'gpa',
        'attendance_rate',
    ];

    // GPA thresholds
    const GPA_GOOD_STANDING = 3.0;
    const GPA_AT_RISK = 2.0;

    /**
     * Determines the student's academic standing.
     */
    public function getAcademicStandingAttribute(): string
    {
        if ($this->gpa >= self::GPA_GOOD_STANDING) {
            return 'Good Standing';
        }

        if ($this->gpa >= self::GPA_AT_RISK) {
            return 'At Risk';
        }

        return 'Probation';
    }

    /**
     * Calculates quality points.
     */
    public function getQualityPointsAttribute(): float
    {
        return round($this->units * $this->gpa, 2);
    }

    /**
     * Gets students who are below Good Standing.
     */
    public function scopeAtRisk($query)
    {
        return $query->where('gpa', '<', self::GPA_GOOD_STANDING);
    }
}