<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'name',
        'program',
        'year_level',
        'units',
        'gpa',
        'attendance_rate',
    ];

    protected function casts(): array
    {
        return [
            'alert_notified_at' => 'datetime',
        ];
    }

    // GPA thresholds
    const GPA_GOOD_STANDING = 3.0;
    const GPA_AT_RISK = 2.0;

    // Attendance threshold (Lab 6)
    const ATTENDANCE_THRESHOLD = 80;

    // ==========================================
    // Relationships (Lab 6)
    // ==========================================

    /**
     * The student's login account (role = student).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function latestGrade(): HasOne
    {
        return $this->hasOne(Grade::class)->latestOfMany();
    }

    public function latestAttendance(): HasOne
    {
        return $this->hasOne(AttendanceRecord::class)->latestOfMany('date');
    }

    // ==========================================
    // Lab 5 accessors
    // ==========================================

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
     * Bootstrap color for the standing badge.
     */
    public function getStandingColorAttribute(): string
    {
        return match ($this->academic_standing) {
            'Good Standing' => 'success',
            'At Risk' => 'warning',
            default => 'danger',
        };
    }

    /**
     * Calculates quality points.
     */
    public function getQualityPointsAttribute(): float
    {
        return round($this->units * $this->gpa, 2);
    }

    // ==========================================
    // Lab 6 helpers
    // ==========================================

    public function hasLowGpa(): bool
    {
        return (float) $this->gpa < self::GPA_GOOD_STANDING;
    }

    public function hasLowAttendance(): bool
    {
        return (int) $this->attendance_rate < self::ATTENDANCE_THRESHOLD;
    }

    /**
     * True when GPA or attendance is below the threshold.
     */
    public function needsAttention(): bool
    {
        return $this->hasLowGpa() || $this->hasLowAttendance();
    }

    /**
     * Human readable reasons, e.g. ["GPA 2.40 is below 3.00"].
     */
    public function getAlertReasonsAttribute(): array
    {
        $reasons = [];

        if ($this->hasLowGpa()) {
            $reasons[] = 'GPA ' . number_format((float) $this->gpa, 2)
                . ' is below ' . number_format(self::GPA_GOOD_STANDING, 2);
        }

        if ($this->hasLowAttendance()) {
            $reasons[] = 'Attendance ' . $this->attendance_rate
                . '% is below ' . self::ATTENDANCE_THRESHOLD . '%';
        }

        return $reasons;
    }

    /**
     * GPA = units-weighted average of all recorded grades.
     */
    public function recalculateGpa(): void
    {
        $grades = $this->grades()->get(['grade', 'units']);
        $totalUnits = $grades->sum('units');

        if ($totalUnits > 0) {
            $weighted = $grades->sum(fn ($g) => (float) $g->grade * $g->units);
            $this->gpa = round($weighted / $totalUnits, 2);
        }
    }

    /**
     * Attendance rate = (present + late) / (days recorded - excused) x 100
     */
    public function recalculateAttendance(): void
    {
        $counts = $this->attendanceRecords()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $attended = ($counts['present'] ?? 0) + ($counts['late'] ?? 0);
        $countedDays = $counts->sum() - ($counts['excused'] ?? 0);

        if ($countedDays > 0) {
            $this->attendance_rate = (int) round($attended / $countedDays * 100);
        }
    }

    // ==========================================
    // Query Scopes
    // ==========================================

    /**
     * Gets students who are below Good Standing.
     */
    public function scopeAtRisk($query)
    {
        return $query->where('gpa', '<', self::GPA_GOOD_STANDING);
    }

    /**
     * GPA below good standing OR attendance below threshold.
     */
    public function scopeNeedingAttention($query)
    {
        return $query->where(function ($q) {
            $q->where('gpa', '<', self::GPA_GOOD_STANDING)
                ->orWhere('attendance_rate', '<', self::ATTENDANCE_THRESHOLD);
        });
    }

    public function scopeInProgram($query, string $program)
    {
        return $query->where('program', $program);
    }
}
