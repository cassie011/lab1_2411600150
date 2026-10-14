@extends('layouts.app')

@section('title', $student->name)

@section('content')

<div class="bg-white p-4 rounded shadow-sm mb-4">

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">

        <div>
            <h2>{{ $student->name }}</h2>

            <p class="text-muted">
                {{ $student->student_id }} — {{ $student->program }}
                @if ($student->user)
                    <span class="badge bg-light text-primary-custom border ms-1">
                        <i class="bi bi-person-check me-1"></i>Has portal account
                    </span>
                @endif
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('grades.create', ['student_id' => $student->id]) }}" class="btn btn-primary">
                <i class="bi bi-journal-plus me-1"></i>
                Record Grade
            </a>

            <a href="{{ route('attendance.create', ['program' => $student->program, 'year_level' => $student->year_level]) }}" class="btn btn-outline-secondary">
                <i class="bi bi-calendar-check me-1"></i>
                Take Attendance
            </a>
        </div>

    </div>

    @if ($student->needsAttention())
        <div class="alert alert-warning py-2">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <strong>Needs attention:</strong> {{ implode('; ', $student->alert_reasons) }}.
        </div>
    @endif

    <table class="table w-50">

        <tr>
            <th>Year Level</th>
            <td>{{ $student->year_level }}</td>
        </tr>

        <tr>
            <th>Units</th>
            <td>{{ $student->units }}</td>
        </tr>

        <tr>
            <th>GPA</th>
            <td>{{ number_format($student->gpa, 2) }}</td>
        </tr>

        <tr>
            <th>Quality Points</th>
            <td>{{ $student->quality_points }}</td>
        </tr>

        <tr>
            <th>Attendance</th>
            <td>{{ $student->attendance_rate }}%</td>
        </tr>

        <tr>
            <th>Standing</th>
            <td>{{ $student->academic_standing }}</td>
        </tr>

    </table>

    <a
        href="{{ route('students.edit', $student) }}"
        class="btn btn-secondary"
    >
        Edit
    </a>

    <a
        href="{{ route('students.index') }}"
        class="btn btn-outline-secondary"
    >
        Back to list
    </a>

</div>


<!-- Grades (Lab 6) -->
<div class="card mb-4">

    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-journal-text me-2"></i>
            Grades
        </h5>

        <span class="badge bg-secondary">{{ $grades->count() }} grade(s)</span>
    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-striped table-hover align-middle">

                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Term</th>
                        <th class="text-center">Units</th>
                        <th class="text-center">Grade</th>
                        <th>Result</th>
                        <th>Recorded By</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($grades as $grade)

                        <tr>
                            <td>{{ $grade->subject }}</td>
                            <td>{{ $grade->term_label }}</td>
                            <td class="text-center">{{ $grade->units }}</td>
                            <td class="text-center">
                                <span class="badge status-badge bg-{{ $grade->grade_color }} {{ $grade->grade_color === 'warning' ? 'text-dark' : '' }}">
                                    {{ number_format((float) $grade->grade, 2) }}
                                </span>
                            </td>
                            <td class="{{ $grade->isPassing() ? 'value-good' : 'value-bad' }}">{{ $grade->result }}</td>
                            <td>{{ $grade->user?->name ?? '—' }}</td>
                            <td>
                                <a href="{{ route('grades.edit', $grade) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No grades recorded yet.</td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


<!-- Attendance History (Lab 6) -->
<div class="card mb-4">

    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">

        <h5 class="mb-0">
            <i class="bi bi-clock-history me-2"></i>
            Attendance History
        </h5>

        <form method="GET" action="{{ route('students.show', $student) }}" class="d-flex gap-2 align-items-center flex-wrap">

            <input type="date" name="date_from" class="form-control form-control-sm w-auto" value="{{ request('date_from') }}" aria-label="Date from">

            <span class="text-muted small">to</span>

            <input type="date" name="date_to" class="form-control form-control-sm w-auto" value="{{ request('date_to') }}" aria-label="Date to">

            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-funnel"></i>
            </button>

            <a href="{{ route('students.show', $student) }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>

        </form>

    </div>

    <div class="card-body">

        @if ($errors->any())
            <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
        @endif

        <div class="table-responsive">

            <table class="table table-striped table-hover align-middle">

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-end">Running Rate</th>
                        <th>Remarks</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($history as $record)

                        <tr>
                            <td>{{ $record->date->format('D, M d, Y') }}</td>
                            <td>
                                <span class="badge status-badge bg-{{ $record->status_color }} {{ $record->status_color === 'warning' ? 'text-dark' : '' }}">
                                    {{ $record->status_label }}
                                </span>
                            </td>
                            <td class="text-end fw-semibold {{ ($record->running_rate ?? 100) < \App\Models\Student::ATTENDANCE_THRESHOLD ? 'value-bad' : '' }}">
                                {{ $record->running_rate !== null ? $record->running_rate . '%' : '—' }}
                            </td>
                            <td>{{ $record->remarks ?: '—' }}</td>
                            <td>{{ $record->user?->name ?? '—' }}</td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No attendance records for this period.</td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
