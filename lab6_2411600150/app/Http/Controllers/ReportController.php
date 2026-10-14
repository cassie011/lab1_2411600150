<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    const REPORTS = [
        'attendance' => 'Attendance Summary',
        'grades' => 'Grade Report',
        'at_risk' => 'At-Risk Students',
    ];

    // GET /reports?report=attendance|grades|at_risk&export=csv
    public function index(Request $request)
    {
        $request->validate([
            'report' => ['nullable', 'in:' . implode(',', array_keys(self::REPORTS))],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'term' => ['nullable', 'in:' . implode(',', array_keys(Grade::TERMS))],
        ]);

        $report = $request->input('report', 'attendance');
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $program = $request->input('program');
        $term = $request->input('term');

        $data = match ($report) {
            'grades' => $this->grades($program, $term),
            'at_risk' => $this->atRisk($program),
            default => $this->attendance($dateFrom, $dateTo, $program),
        };

        if ($request->input('export') === 'csv') {
            return $this->exportCsv($report, $data, $dateFrom, $dateTo);
        }

        return view('reports.index', [
            'reports' => self::REPORTS,
            'report' => $report,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'program' => $program,
            'term' => $term,
            'terms' => Grade::TERMS,
            'programs' => Student::select('program')->distinct()->orderBy('program')->pluck('program'),
            'data' => $data,
        ]);
    }

    /**
     * Present / late / absent / excused per student within a date range.
     */
    private function attendance(string $from, string $to, ?string $program): array
    {
        $students = Student::when($program, fn ($q) => $q->inProgram($program))
            ->orderBy('name')
            ->get();

        $counts = AttendanceRecord::whereIn('student_id', $students->pluck('id'))
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->selectRaw('student_id, status, COUNT(*) as total')
            ->groupBy('student_id', 'status')
            ->get()
            ->groupBy('student_id');

        $rows = $students->map(function ($student) use ($counts) {
            $c = ($counts[$student->id] ?? collect())->pluck('total', 'status');

            $present = (int) ($c['present'] ?? 0);
            $late = (int) ($c['late'] ?? 0);
            $absent = (int) ($c['absent'] ?? 0);
            $excused = (int) ($c['excused'] ?? 0);
            $counted = $present + $late + $absent;

            return [
                'student' => $student,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'excused' => $excused,
                'rate' => $counted > 0 ? round(($present + $late) / $counted * 100) : null,
            ];
        });

        return ['rows' => $rows];
    }

    /**
     * All grades, optionally per term, grouped by student.
     */
    private function grades(?string $program, ?string $term): array
    {
        $grades = Grade::with('student')
            ->whereHas('student', fn ($q) => $program ? $q->inProgram($program) : $q)
            ->when($term, fn ($q) => $q->term($term))
            ->get()
            ->sortBy(fn ($g) => $g->student->name . $g->subject . $g->term);

        return [
            'rows' => $grades,
            'average' => round((float) $grades->avg('grade'), 2),
            'failed' => $grades->filter(fn ($g) => ! $g->isPassing())->count(),
        ];
    }

    private function atRisk(?string $program): array
    {
        return [
            'rows' => Student::needingAttention()
                ->when($program, fn ($q) => $q->inProgram($program))
                ->orderBy('gpa')
                ->orderBy('attendance_rate')
                ->get(),
        ];
    }

    private function exportCsv(string $report, array $data, string $from, string $to): StreamedResponse
    {
        $filename = "{$report}_report_" . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($report, $data, $from, $to) {
            $out = fopen('php://output', 'w');

            if ($report === 'attendance') {
                fputcsv($out, ["Attendance Summary ({$from} to {$to})"]);
                fputcsv($out, ['Student ID', 'Name', 'Program', 'Present', 'Late', 'Absent', 'Excused', 'Rate %']);

                foreach ($data['rows'] as $r) {
                    fputcsv($out, [
                        $r['student']->student_id, $r['student']->name, $r['student']->program,
                        $r['present'], $r['late'], $r['absent'], $r['excused'], $r['rate'] ?? '',
                    ]);
                }
            } elseif ($report === 'grades') {
                fputcsv($out, ['Grade Report (' . now()->format('Y-m-d H:i') . ')']);
                fputcsv($out, ['Student ID', 'Name', 'Subject', 'Units', 'Term', 'Grade', 'Result']);

                foreach ($data['rows'] as $g) {
                    fputcsv($out, [
                        $g->student->student_id, $g->student->name, $g->subject, $g->units,
                        $g->term_label, number_format((float) $g->grade, 2), $g->result,
                    ]);
                }
            } else {
                fputcsv($out, ['At-Risk Students (' . now()->format('Y-m-d H:i') . ')']);
                fputcsv($out, ['Student ID', 'Name', 'Program', 'Year', 'GPA', 'Attendance %', 'Standing', 'Reasons']);

                foreach ($data['rows'] as $s) {
                    fputcsv($out, [
                        $s->student_id, $s->name, $s->program, $s->year_level,
                        number_format((float) $s->gpa, 2), $s->attendance_rate,
                        $s->academic_standing, implode('; ', $s->alert_reasons),
                    ]);
                }
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
