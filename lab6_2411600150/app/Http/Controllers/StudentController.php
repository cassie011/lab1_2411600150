<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    // GET /students
    public function index(Request $request)
{
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

    $students = $query
        ->orderBy('name')
        ->get();

    // Get available programs
    $programs = Student::select('program')
        ->distinct()
        ->orderBy('program')
        ->pluck('program');

    return view('students.index', compact(
        'students',
        'programs'
    ));
}

    // GET /students/create
    public function create()
    {
        return view('students.create');
    }

    // POST /students
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'      => 'required|string|unique:students,student_id',
            'name'            => 'required|string|max:255',
            'program'         => 'required|string|max:255',
            'year_level'      => 'required|integer|min:1|max:6',
            'units'           => 'required|integer|min:0',
            'gpa'             => 'required|numeric|min:0|max:4',
            'attendance_rate' => 'required|integer|min:0|max:100',
        ]);

        Student::create($validated);

        return redirect()->route('students.index')
            ->with('success', 'Student added successfully.');
    }

    // GET /students/{student}
    public function show(Request $request, Student $student)
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        // Lab 6: grades of this student
        $grades = $student->grades()->with('user')
            ->orderBy('subject')
            ->orderByRaw("CASE term WHEN 'prelim' THEN 1 WHEN 'midterm' THEN 2 ELSE 3 END")
            ->get();

        // Lab 6: attendance history with a running attendance rate
        // (rate after each day, oldest to newest)
        $all = $student->attendanceRecords()->with('user')
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $attended = 0;
        $counted = 0;

        foreach ($all as $record) {
            if ($record->status !== 'excused') {
                $counted++;
            }

            if (in_array($record->status, ['present', 'late'])) {
                $attended++;
            }

            $record->running_rate = $counted > 0 ? round($attended / $counted * 100) : null;
        }

        $history = $all->filter(function ($record) use ($request) {
            if ($request->filled('date_from') && $record->date->lt($request->date_from)) {
                return false;
            }

            if ($request->filled('date_to') && $record->date->gt($request->date_to)) {
                return false;
            }

            return true;
        })->reverse(); // newest first

        return view('students.show', compact('student', 'grades', 'history'));
    }

    // GET /students/{student}/edit
    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    // PUT/PATCH /students/{student}
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'program'         => 'required|string|max:255',
            'year_level'      => 'required|integer|min:1|max:6',
            'units'           => 'required|integer|min:0',
            'gpa'             => 'required|numeric|min:0|max:4',
            'attendance_rate' => 'required|integer|min:0|max:100',
        ]);

        $student->update($validated);

        return redirect()->route('students.index')
            ->with('success', 'Student updated successfully.');
    }

    // DELETE /students/{student}
    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Student removed.');
    }
}