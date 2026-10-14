<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    // GET /attendance
    public function index(Request $request)
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $query = AttendanceRecord::with(['student', 'user']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('program')) {
            $query->whereHas('student', fn ($q) => $q->inProgram($request->program));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        return view('attendance.index', [
            'records' => $query->recent()->paginate(20)->withQueryString(),
            'students' => Student::orderBy('name')->get(['id', 'name', 'student_id']),
            'programs' => $this->programs(),
            'statuses' => AttendanceRecord::STATUSES,
        ]);
    }

    // GET /attendance/create?program=&year_level=&date=
    // Shows the class list. Existing records for that date are pre-filled,
    // so the same page is used to take AND correct attendance.
    public function create(Request $request)
    {
        $request->validate([
            'date' => ['nullable', 'date', 'before_or_equal:today'],
            'year_level' => ['nullable', 'integer', 'min:1', 'max:6'],
        ]);

        $date = $request->input('date', now()->toDateString());

        $students = collect();
        $existing = collect();

        if ($request->filled('program')) {
            $students = Student::inProgram($request->program)
                ->when($request->filled('year_level'), fn ($q) => $q->where('year_level', $request->year_level))
                ->orderBy('name')
                ->get();

            $existing = AttendanceRecord::whereIn('student_id', $students->pluck('id'))
                ->whereDate('date', $date)
                ->get()
                ->keyBy('student_id');
        }

        return view('attendance.create', [
            'programs' => $this->programs(),
            'statuses' => AttendanceRecord::STATUSES,
            'students' => $students,
            'existing' => $existing,
            'date' => $date,
        ]);
    }

    // POST /attendance  (saves the whole class at once)
    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
            'program' => ['required', 'string'],
            'year_level' => ['nullable', 'integer'],
            'attendance' => ['required', 'array', 'min:1'],
            'attendance.*.status' => ['required', Rule::in(array_keys(AttendanceRecord::STATUSES))],
            'attendance.*.remarks' => ['nullable', 'string', 'max:255'],
        ], [
            'date.before_or_equal' => 'Attendance cannot be recorded for a future date.',
        ]);

        $studentIds = array_keys($data['attendance']);

        $validIds = Student::whereIn('id', $studentIds)->pluck('id')->all();

        if (count($validIds) !== count($studentIds)) {
            return back()->withInput()->with('error', 'One or more students no longer exist.');
        }

        // One DB transaction for the whole class:
        // if any student fails, nobody's attendance is saved.
        $saved = DB::transaction(function () use ($data, $request) {
            $count = 0;

            foreach ($data['attendance'] as $studentId => $row) {
                $record = AttendanceRecord::where('student_id', $studentId)
                    ->whereDate('date', $data['date'])
                    ->first()
                    ?? new AttendanceRecord(['student_id' => $studentId, 'date' => $data['date']]);

                $record->fill([
                    'status' => $row['status'],
                    'remarks' => $row['remarks'] ?? null,
                ]);

                if (! $record->exists || $record->isDirty()) {
                    $record->user_id = $request->user()->id;
                    $record->save(); // observer recalculates the attendance rate
                    $count++;
                }
            }

            return $count;
        });

        return redirect()->route('attendance.create', [
            'program' => $data['program'],
            'year_level' => $data['year_level'] ?? null,
            'date' => $data['date'],
        ])->with('success', "Attendance saved for {$saved} student(s) on "
            . Carbon::parse($data['date'])->format('M d, Y') . '.');
    }

    // DELETE /attendance/{attendance}  (admin only)
    public function destroy(AttendanceRecord $attendance)
    {
        DB::transaction(fn () => $attendance->delete());

        return back()->with('success', 'Attendance record deleted. Attendance rate was recalculated.');
    }

    private function programs()
    {
        return Student::select('program')->distinct()->orderBy('program')->pluck('program');
    }
}
