<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\StudentResource;
use App\Models\AttendanceRecord;
use App\Models\Grade;
use App\Models\Student;
use App\Support\AcademicStats;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JSON endpoints used by the dashboard AJAX polling (every 30 seconds)
 * and by the grade form.
 */
class AcademicApiController extends Controller
{
    // GET /api/dashboard/stats
    public function stats(): JsonResponse
    {
        return response()->json([
            'data' => AcademicStats::summary(),
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    // GET /api/students/at-risk
    public function atRisk()
    {
        $students = Student::needingAttention()
            ->orderBy('gpa')
            ->orderBy('attendance_rate')
            ->get();

        return StudentResource::collection($students)
            ->additional(['generated_at' => now()->toIso8601String()]);
    }

    // GET /api/activity/recent
    // Latest grades and attendance records, newest first.
    public function recentActivity(Request $request)
    {
        $limit = min(max((int) $request->query('limit', 10), 1), 50);

        $grades = Grade::with(['student', 'user'])->recent()->take($limit)->get();
        $attendance = AttendanceRecord::with(['student', 'user'])->latest()->latest('id')->take($limit)->get();

        $activity = $grades->concat($attendance)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();

        return ActivityResource::collection($activity)
            ->additional(['generated_at' => now()->toIso8601String()]);
    }

    // GET /api/students/{student}/summary  (used by the grade form)
    public function studentSummary(Student $student)
    {
        return new StudentResource($student);
    }
}
