<?php

namespace App\Support;

use App\Models\AttendanceRecord;
use App\Models\Grade;
use App\Models\Student;

/**
 * Statistics shared by the dashboard (first page load)
 * and the /api/dashboard/stats endpoint (AJAX polling).
 */
class AcademicStats
{
    public static function summary(): array
    {
        $today = now()->toDateString();

        $todayAttendance = AttendanceRecord::whereDate('date', $today)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total_students' => Student::count(),
            'average_gpa' => round((float) Student::avg('gpa'), 2),
            'at_risk_count' => Student::atRisk()->count(),
            'average_attendance' => round((float) Student::avg('attendance_rate'), 1),
            'needs_attention_count' => Student::needingAttention()->count(),
            'present_today' => (int) (($todayAttendance['present'] ?? 0) + ($todayAttendance['late'] ?? 0)),
            'absent_today' => (int) ($todayAttendance['absent'] ?? 0),
            'grades_today' => Grade::whereDate('created_at', $today)->count(),
        ];
    }
}
