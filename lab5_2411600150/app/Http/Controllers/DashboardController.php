<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
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
        // Send Data to Dashboard
        // ==========================================

        return view('dashboard', compact(
            'stats',
            'byProgram',
            'topStudents',
            'students',
            'programs',
            'greeting'
        ));
    }
}

