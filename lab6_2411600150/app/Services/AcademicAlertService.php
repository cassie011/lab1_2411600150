<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Notifications\StudentAtRiskNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Recalculates a student's GPA / attendance rate and sends an
 * academic alert when the student falls below the thresholds.
 * Called by the Grade and AttendanceRecord observers.
 */
class AcademicAlertService
{
    public static function refresh(Student $student, bool $gpa = true, bool $attendance = true): Student
    {
        return DB::transaction(function () use ($student, $gpa, $attendance) {
            // Lock the row so two teachers cannot update the same student at once
            $student = Student::lockForUpdate()->findOrFail($student->id);

            if ($gpa) {
                $student->recalculateGpa();
            }

            if ($attendance) {
                $student->recalculateAttendance();
            }

            $student->save();

            self::checkAlert($student);

            return $student;
        });
    }

    /**
     * Sends a StudentAtRiskNotification once when the student drops below
     * the GPA or attendance threshold. The flag resets when the student
     * recovers, so teachers are not spammed with duplicate alerts.
     */
    public static function checkAlert(Student $student): void
    {
        if (! $student->needsAttention()) {
            if ($student->alert_notified_at) {
                $student->alert_notified_at = null;
                $student->save();
            }

            return;
        }

        // Already alerted for this period
        if ($student->alert_notified_at) {
            return;
        }

        $recipients = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_TEACHER])->get();

        // The student also gets the alert in their own portal
        if ($student->user) {
            $recipients->push($student->user);
        }

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new StudentAtRiskNotification($student));
        }

        $student->alert_notified_at = now();
        $student->save();
    }
}
