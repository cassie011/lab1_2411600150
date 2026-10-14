<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeRequest;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradeController extends Controller
{
    // GET /grades
    public function index(Request $request)
    {
        $query = Grade::with(['student', 'user']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('subject')) {
            $query->where('subject', $request->subject);
        }

        if ($request->filled('term')) {
            $query->term($request->term);
        }

        if ($request->filled('program')) {
            $query->whereHas('student', fn ($q) => $q->inProgram($request->program));
        }

        if ($request->input('result') === 'failed') {
            $query->failing();
        }

        $grades = $query->recent()->paginate(20)->withQueryString();

        return view('grades.index', [
            'grades' => $grades,
            'students' => Student::orderBy('name')->get(['id', 'name', 'student_id']),
            'subjects' => $this->subjects(),
            'programs' => Student::select('program')->distinct()->orderBy('program')->pluck('program'),
            'terms' => Grade::TERMS,
        ]);
    }

    // GET /grades/create
    public function create(Request $request)
    {
        return view('grades.create', [
            'students' => Student::orderBy('name')->get(),
            'subjects' => $this->subjects(),
            'terms' => Grade::TERMS,
            'selectedStudent' => $request->query('student_id'),
        ]);
    }

    // POST /grades
    public function store(GradeRequest $request)
    {
        $data = $request->validated();
        $data['subject'] = trim($data['subject']);

        // All-or-nothing: if recalculating the GPA fails,
        // the grade is not saved either.
        $grade = DB::transaction(fn () => Grade::create($data + [
            'user_id' => $request->user()->id,
        ]));

        return redirect()->route('grades.index')
            ->with('success', "Grade recorded for {$grade->student->name} ({$grade->subject}, {$grade->term_label}). GPA is now "
                . number_format((float) $grade->student->fresh()->gpa, 2) . '.');
    }

    // GET /grades/{grade}
    public function show(Grade $grade)
    {
        $grade->load(['student', 'user']);

        return view('grades.show', compact('grade'));
    }

    // GET /grades/{grade}/edit
    public function edit(Grade $grade)
    {
        return view('grades.edit', [
            'grade' => $grade,
            'students' => Student::orderBy('name')->get(),
            'subjects' => $this->subjects(),
            'terms' => Grade::TERMS,
            'selectedStudent' => $grade->student_id,
        ]);
    }

    // PUT /grades/{grade}  (grade correction)
    public function update(GradeRequest $request, Grade $grade)
    {
        $data = $request->validated();
        $data['subject'] = trim($data['subject']);

        DB::transaction(fn () => $grade->update($data));

        return redirect()->route('grades.show', $grade)
            ->with('success', 'Grade updated. GPA was recalculated.');
    }

    // DELETE /grades/{grade}  (admin only)
    public function destroy(Grade $grade)
    {
        DB::transaction(fn () => $grade->delete());

        return redirect()->route('grades.index')
            ->with('success', 'Grade deleted. GPA was recalculated.');
    }

    private function subjects()
    {
        return Grade::select('subject')->distinct()->orderBy('subject')->pluck('subject');
    }
}
