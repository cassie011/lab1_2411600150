<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Grade;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * Sample grades and attendance for the seeded students.
 * Run alone with: php artisan db:seed --class=AcademicRecordSeeder
 */
class AcademicRecordSeeder extends Seeder
{
    const SUBJECTS = [
        'Programming 2' => 0.1,
        'Data Structures' => 0.0,
        'Purposive Communication' => -0.1,
    ];

    public function run(): void
    {
        if (Grade::exists() || AttendanceRecord::exists()) {
            $this->command?->warn('Grades or attendance already exist. AcademicRecordSeeder skipped.');

            return;
        }

        $teacher = User::where('role', User::ROLE_TEACHER)->first() ?? User::first();

        // Last 10 school days before today (Mon-Fri)
        $days = collect();
        $day = now()->subDay()->startOfDay();

        while ($days->count() < 10) {
            if (! $day->isWeekend()) {
                $days->prepend($day->copy());
            }

            $day->subDay();
        }

        // Observers are off here; GPA and attendance are recalculated at the end.
        Model::withoutEvents(function () use ($teacher, $days) {
            foreach (Student::orderBy('id')->get() as $i => $student) {
                $base = (float) $student->gpa;

                // Grades: Prelim and Midterm for 3 subjects
                foreach (self::SUBJECTS as $subject => $offset) {
                    foreach (['prelim' => -0.05, 'midterm' => 0.05] as $term => $termOffset) {
                        $grade = new Grade([
                            'student_id' => $student->id,
                            'user_id' => $teacher?->id,
                            'subject' => $subject,
                            'units' => 3,
                            'term' => $term,
                            'grade' => max(0, min(4, round($base + $offset + $termOffset, 2))),
                        ]);

                        // Prelim recorded 2 weeks ago, Midterm last week
                        $grade->created_at = now()->subDays($term === 'prelim' ? 14 : 7)->setTime(10, 0);
                        $grade->updated_at = $grade->created_at;
                        $grade->save();
                    }
                }

                // Attendance: number of absences based on the seeded rate
                $absences = (int) round((100 - $student->attendance_rate) / 10);

                foreach ($days as $d => $date) {
                    $status = 'present';

                    if ($d >= 10 - $absences) {
                        $status = 'absent';
                    } elseif ($d === 3 && $i % 2 === 0) {
                        $status = 'late';
                    } elseif ($d === 5 && $i % 3 === 0) {
                        $status = 'excused';
                    }

                    $record = new AttendanceRecord([
                        'student_id' => $student->id,
                        'user_id' => $teacher?->id,
                        'date' => $date->toDateString(),
                        'status' => $status,
                    ]);

                    $record->created_at = $date->copy()->setTime(8, 0);
                    $record->updated_at = $record->created_at;
                    $record->save();
                }
            }

            // Recalculate GPA and attendance rate from the records
            foreach (Student::all() as $student) {
                $student->recalculateGpa();
                $student->recalculateAttendance();
                $student->save();
            }
        });
    }
}
