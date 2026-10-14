<?php

namespace App\Observers;

use App\Models\Grade;
use App\Services\AcademicAlertService;
use Illuminate\Validation\ValidationException;

class GradeObserver
{
    /**
     * Runs BEFORE a grade is saved (create or update).
     * Last line of defense: rejects grades outside 0.00 - 4.00.
     */
    public function saving(Grade $grade): void
    {
        if ((float) $grade->grade < 0 || (float) $grade->grade > 4) {
            throw ValidationException::withMessages([
                'grade' => 'Grade must be between 0.00 and 4.00.',
            ]);
        }
    }

    /**
     * After a grade is added, recalculate the student's GPA.
     */
    public function created(Grade $grade): void
    {
        AcademicAlertService::refresh($grade->student, attendance: false);
    }

    /**
     * After a grade is corrected, recalculate the student's GPA.
     */
    public function updated(Grade $grade): void
    {
        AcademicAlertService::refresh($grade->student, attendance: false);
    }

    /**
     * After a grade is deleted, recalculate the student's GPA.
     */
    public function deleted(Grade $grade): void
    {
        AcademicAlertService::refresh($grade->student, attendance: false);
    }
}
