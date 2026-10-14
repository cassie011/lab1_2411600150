<?php

namespace App\Observers;

use App\Models\AttendanceRecord;
use App\Services\AcademicAlertService;
use Illuminate\Validation\ValidationException;

class AttendanceRecordObserver
{
    /**
     * Runs BEFORE the record is saved.
     * Blocks attendance for future dates.
     */
    public function saving(AttendanceRecord $record): void
    {
        if ($record->date && $record->date->isFuture()) {
            throw ValidationException::withMessages([
                'date' => 'Attendance cannot be recorded for a future date.',
            ]);
        }
    }

    /**
     * After attendance is recorded, recalculate the attendance rate.
     */
    public function created(AttendanceRecord $record): void
    {
        AcademicAlertService::refresh($record->student, gpa: false);
    }

    public function updated(AttendanceRecord $record): void
    {
        AcademicAlertService::refresh($record->student, gpa: false);
    }

    public function deleted(AttendanceRecord $record): void
    {
        AcademicAlertService::refresh($record->student, gpa: false);
    }
}
