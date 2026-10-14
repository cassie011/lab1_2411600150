@extends('layouts.app')

@section('title', 'Academic Reports')

@section('content')

<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

        <div>
            <h2 class="mb-1">Academic Reports</h2>
            <p class="text-muted mb-0">Generated {{ now()->format('M d, Y h:i A') }}</p>
        </div>

        <a
            href="{{ route('reports.index', array_merge(request()->query(), ['report' => $report, 'export' => 'csv'])) }}"
            class="btn btn-primary"
        >
            <i class="bi bi-download me-1"></i>
            Export CSV
        </a>

    </div>


    <!-- Report Tabs -->
    <ul class="nav nav-pills report-tabs mb-4 gap-2">
        @foreach ($reports as $key => $label)
            <li class="nav-item">
                <a class="nav-link {{ $report === $key ? 'active' : '' }}" href="{{ route('reports.index', ['report' => $key]) }}">
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>


    <!-- Report Filters -->
    <div class="card shadow-hover mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.index') }}">
                <input type="hidden" name="report" value="{{ $report }}">

                <div class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label for="program" class="form-label fw-semibold">Program</label>
                        <select name="program" id="program" class="form-select">
                            <option value="">All Programs</option>
                            @foreach ($programs as $p)
                                <option value="{{ $p }}" {{ $program == $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if ($report === 'attendance')
                        <div class="col-md-3">
                            <label for="date_from" class="form-label fw-semibold">From</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $dateFrom }}">
                        </div>

                        <div class="col-md-3">
                            <label for="date_to" class="form-label fw-semibold">To</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}">
                        </div>
                    @elseif ($report === 'grades')
                        <div class="col-md-3">
                            <label for="term" class="form-label fw-semibold">Term</label>
                            <select name="term" id="term" class="form-select">
                                <option value="">All Terms</option>
                                @foreach ($terms as $value => $label)
                                    <option value="{{ $value }}" {{ $term == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i>
                            Generate
                        </button>
                    </div>

                </div>

                @if ($errors->any())
                    <div class="text-danger small mt-2">{{ $errors->first() }}</div>
                @endif
            </form>
        </div>
    </div>


    @if ($report === 'attendance')

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Attendance Summary ({{ $dateFrom }} to {{ $dateTo }})</h5>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Program</th>
                                <th class="text-center">Present</th>
                                <th class="text-center">Late</th>
                                <th class="text-center">Absent</th>
                                <th class="text-center">Excused</th>
                                <th class="text-end">Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['rows'] as $r)
                                <tr>
                                    <td>{{ $r['student']->student_id }}</td>
                                    <td><a href="{{ route('students.show', $r['student']) }}" class="text-decoration-none fw-semibold">{{ $r['student']->name }}</a></td>
                                    <td>{{ $r['student']->program }}</td>
                                    <td class="text-center">{{ $r['present'] }}</td>
                                    <td class="text-center">{{ $r['late'] }}</td>
                                    <td class="text-center {{ $r['absent'] > 0 ? 'value-bad' : '' }}">{{ $r['absent'] }}</td>
                                    <td class="text-center">{{ $r['excused'] }}</td>
                                    <td class="text-end fw-semibold {{ ($r['rate'] ?? 100) < \App\Models\Student::ATTENDANCE_THRESHOLD ? 'value-bad' : '' }}">
                                        {{ $r['rate'] !== null ? $r['rate'] . '%' : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No students found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @elseif ($report === 'grades')

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card stat-card shadow-hover text-center">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Grades</h5>
                        <h2 class="card-text fw-bold text-primary-custom">{{ $data['rows']->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card shadow-hover text-center">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Average Grade</h5>
                        <h2 class="card-text fw-bold text-success">{{ number_format($data['average'], 2) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card shadow-hover text-center">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Failed</h5>
                        <h2 class="card-text fw-bold text-danger">{{ $data['failed'] }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Grade Report</h5>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Subject</th>
                                <th>Term</th>
                                <th class="text-center">Units</th>
                                <th class="text-center">Grade</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['rows'] as $g)
                                <tr>
                                    <td>{{ $g->student->name }} <small class="text-muted">{{ $g->student->student_id }}</small></td>
                                    <td>{{ $g->subject }}</td>
                                    <td>{{ $g->term_label }}</td>
                                    <td class="text-center">{{ $g->units }}</td>
                                    <td class="text-center">
                                        <span class="badge status-badge bg-{{ $g->grade_color }} {{ $g->grade_color === 'warning' ? 'text-dark' : '' }}">
                                            {{ number_format((float) $g->grade, 2) }}
                                        </span>
                                    </td>
                                    <td class="{{ $g->isPassing() ? 'value-good' : 'value-bad' }}">{{ $g->result }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No grades found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @else

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">At-Risk Students</h5>
                <span class="badge bg-warning text-dark">{{ $data['rows']->count() }} student(s)</span>
            </div>

            <div class="card-body">
                <p class="text-muted small">
                    GPA below {{ number_format(\App\Models\Student::GPA_GOOD_STANDING, 2) }}
                    or attendance below {{ \App\Models\Student::ATTENDANCE_THRESHOLD }}%.
                </p>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Program</th>
                                <th class="text-center">GPA</th>
                                <th class="text-center">Attendance</th>
                                <th>Standing</th>
                                <th>Reasons</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['rows'] as $s)
                                <tr>
                                    <td>{{ $s->student_id }}</td>
                                    <td><a href="{{ route('students.show', $s) }}" class="text-decoration-none fw-semibold">{{ $s->name }}</a></td>
                                    <td>{{ $s->program }}</td>
                                    <td class="text-center {{ $s->hasLowGpa() ? 'value-bad' : '' }}">{{ number_format((float) $s->gpa, 2) }}</td>
                                    <td class="text-center {{ $s->hasLowAttendance() ? 'value-bad' : '' }}">{{ $s->attendance_rate }}%</td>
                                    <td>
                                        <span class="badge status-badge bg-{{ $s->standing_color }} {{ $s->standing_color === 'warning' ? 'text-dark' : '' }}">
                                            {{ $s->academic_standing }}
                                        </span>
                                    </td>
                                    <td><small>{{ implode('; ', $s->alert_reasons) }}</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No students need attention right now.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @endif

</div>

@endsection
