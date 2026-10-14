<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Grade;
use App\Models\Student;
use App\Support\AcademicStats;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ==========================================
        // Time-Based Greeting
        // ==========================================

        $hour = now()->hour;

        if ($hour < 12) {
            $greeting = 'Good Morning';
        } elseif ($hour < 18) {
            $greeting = 'Good Afternoon';
        } else {
            $greeting = 'Good Evening';
        }

        // Students see their own portal (Lab 6)
        if ($request->user()->isStudent()) {
            return $this->studentPortal($request, $greeting);
        }

        // Get all programs for the dropdown
        $programs = Student::select('program')
            ->distinct()
            ->orderBy('program')
            ->pluck('program');

        // Start the student query
        $query = Student::query();

        // Program filter
        if ($request->filled('program')) {
            $query->where('program', $request->program);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Get filtered students
        $students = $query->get();

        // ==========================================
        // Dashboard Statistics
        // ==========================================

        $stats = [
            'total_students' => $students->count(),

            'average_gpa' => round(
                $students->avg('gpa') ?? 0,
                2
            ),

            'at_risk_count' => $students
                ->where('gpa', '<', 3.0)
                ->count(),

            'average_attendance' => round(
                $students->avg('attendance_rate') ?? 0,
                1
            ),
        ];

        // ==========================================
        // Program Counts
        // ==========================================

        $byProgram = $students
            ->groupBy('program')
            ->map(function ($group) {
                return $group->count();
            });

        // ==========================================
        // Top Students
        // ==========================================

        $topStudents = $students
            ->sortByDesc('gpa')
            ->take(5);

        // ==========================================
        // Lab 6: Live academic overview - first load
        // values. The page then refreshes these every
        // 30 seconds through the /api endpoints.
        // ==========================================

        $liveStats = AcademicStats::summary();

        $attentionStudents = Student::needingAttention()
            ->orderBy('gpa')
            ->orderBy('attendance_rate')
            ->take(5)
            ->get();

        $recentActivity = Grade::with(['student', 'user'])->recent()->take(5)->get()
            ->concat(AttendanceRecord::with(['student', 'user'])->latest()->latest('id')->take(5)->get())
            ->sortByDesc('created_at')
            ->take(5);

        // ==========================================
        // Send Data to Dashboard
        // ==========================================

        return view('dashboard', compact(
            'stats',
            'byProgram',
            'topStudents',
            'students',
            'programs',
            'greeting',
            'liveStats',
            'attentionStudents',
            'recentActivity'
        ));
    }

    /**
     * Student portal: the logged in student's own grades and attendance.
     */
    private function studentPortal(Request $request, string $greeting)
    {
        $student = $request->user()->student;

        $grades = collect();
        $attendance = collect();
        $attendanceCounts = collect();

        if ($student) {
            $grades = $student->grades()->with('user')
                ->orderBy('subject')
                ->get()
                ->groupBy('subject');

            $attendance = $student->attendanceRecords()->recent()->take(15)->get();

            $attendanceCounts = $student->attendanceRecords()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');
        }

        return view('portal.dashboard', [
            'greeting' => $greeting,
            'student' => $student,
            'grades' => $grades,
            'attendance' => $attendance,
            'attendanceCounts' => $attendanceCounts,
            'terms' => Grade::TERMS,
        ]);
    }
}
