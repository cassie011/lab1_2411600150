@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="container-fluid">

    <!-- Dashboard Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">

        <h2>
            {{ $greeting }}, {{ auth()->user()->name }}!
        </h2>

    </div>


    <!-- Academic Warning Alert -->
    @if ($stats['at_risk_count'] > 0)

        <div
            id="atRiskAlert"
            class="alert alert-warning alert-dismissible fade show"
            role="alert"
        >

            <strong>Academic Alert:</strong>

            {{ $stats['at_risk_count'] }} student(s) are currently at risk.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>

        </div>

    @endif


    <!-- Statistics Cards -->
    <div class="row mb-4">

        <!-- Total Students -->
        <div class="col-md-3 mb-3">

            <div class="card stat-card shadow-hover text-center">

                <div class="card-body">

                    <h5 class="card-title text-muted">
                        Students
                    </h5>

                    <h2 class="card-text fw-bold text-primary-custom" id="statTotalStudents" data-value="{{ $stats['total_students'] }}">
                        {{ $stats['total_students'] }}
                    </h2>

                </div>

            </div>

        </div>


        <!-- Average GPA -->
        <div class="col-md-3 mb-3">

            <div class="card stat-card shadow-hover text-center">

                <div class="card-body">

                    <h5 class="card-title text-muted">
                        Average GPA
                    </h5>

                    <h2 class="card-text fw-bold text-primary-custom" id="statAverageGpa" data-value="{{ $stats['average_gpa'] }}" data-decimals="2">
                        {{ number_format($stats['average_gpa'], 2) }}
                    </h2>

                </div>

            </div>

        </div>


        <!-- At Risk -->
        <div class="col-md-3 mb-3">

            <div class="card stat-card shadow-hover text-center">

                <div class="card-body">

                    <h5 class="card-title text-muted">
                        At Risk
                    </h5>

                    <h2 class="card-text fw-bold text-warning" id="statAtRisk" data-value="{{ $stats['at_risk_count'] }}">
                        {{ $stats['at_risk_count'] }}
                    </h2>

                </div>

            </div>

        </div>


        <!-- Attendance -->
        <div class="col-md-3 mb-3">

            <div class="card stat-card shadow-hover text-center">

                <div class="card-body">

                    <h5 class="card-title text-muted">
                        Attendance
                    </h5>

                    <h2 class="card-text fw-bold text-success" id="statAverageAttendance" data-value="{{ $stats['average_attendance'] }}" data-decimals="1" data-suffix="%">
                        {{ $stats['average_attendance'] }}%
                    </h2>

                </div>

            </div>

        </div>

    </div>


    <!-- Charts -->
    <div class="row mb-4">

        <!-- Students by Program -->
        <div class="col-lg-3 col-md-6 mb-3">

            <div class="card shadow-hover h-100">

                <div class="card-header">

                    <h6 class="mb-0">
                        Enrolled Students by Program
                    </h6>

                </div>

                <div class="card-body">

                    <div class="chart-container">

                        <canvas
                            id="programValueChart"
                            aria-label="Enrolled students by program chart"
                            role="img"
                        ></canvas>

                    </div>

                </div>

            </div>

        </div>


        <!-- Academic Standing -->
        <div class="col-lg-3 col-md-6 mb-3">

            <div class="card shadow-hover h-100">

                <div class="card-header">

                    <h6 class="mb-0">
                        Academic Standing Distribution
                    </h6>

                </div>

                <div class="card-body">

                    <div class="chart-container">

                        <canvas
                            id="standingChart"
                            aria-label="Academic standing distribution chart"
                            role="img"
                        ></canvas>

                    </div>

                </div>

            </div>

        </div>


        <!-- Top Students -->
        <div class="col-lg-3 col-md-6 mb-3">

            <div class="card shadow-hover h-100">

                <div class="card-header">

                    <h6 class="mb-0">
                        Top Students by GPA
                    </h6>

                </div>

                <div class="card-body">

                    <div class="chart-container">

                        <canvas
                            id="topStudentsChart"
                            aria-label="Top students by GPA chart"
                            role="img"
                        ></canvas>

                    </div>

                </div>

            </div>

        </div>

        <!-- Attendance Rate -->
        <div class="col-lg-3 col-md-6 mb-3">

            <div class="card shadow-hover h-100">

                <div class="card-header">

                    <h6 class="mb-0">
                        Attendance Rate by Student
                    </h6>

                </div>

                <div class="card-body">

                    <div class="chart-container">

                        <canvas
                            id="attendanceChart"
                            aria-label="Attendance rate by student chart"
                            role="img"
                        ></canvas>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- ==========================================
         Lab 6: Real-Time Academic Tracking (AJAX polling)
    =========================================== -->
    <div
        id="academicLive"
        data-stats-url="{{ route('api.dashboard.stats') }}"
        data-at-risk-url="{{ route('api.students.atRisk') }}"
        data-recent-url="{{ route('api.activity.recent', ['limit' => 6]) }}"
        data-interval="30000"
    >

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-2 mb-3 border-bottom">

            <h4 class="mb-0">
                <i class="bi bi-activity me-2 text-primary-custom"></i>
                Today's Activity
            </h4>

            <small class="text-muted">
                <span id="liveDot" class="live-dot"></span>
                Last Updated: <span id="lastUpdated">{{ now()->format('h:i:s A') }}</span>
                <span id="liveError" class="text-danger ms-2 d-none">Connection problem. Retrying...</span>
            </small>

        </div>


        <!-- Today Statistics Cards -->
        <div class="row mb-4">

            <div class="col-md-3 col-6 mb-3">
                <div class="card stat-card shadow-hover text-center border-success-custom">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Present Today</h5>
                        <h2 class="card-text fw-bold text-success" id="statPresentToday" data-value="{{ $liveStats['present_today'] }}">
                            {{ $liveStats['present_today'] }}
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-6 mb-3">
                <div class="card stat-card shadow-hover text-center border-accent">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Absent Today</h5>
                        <h2 class="card-text fw-bold text-danger" id="statAbsentToday" data-value="{{ $liveStats['absent_today'] }}">
                            {{ $liveStats['absent_today'] }}
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-6 mb-3">
                <div class="card stat-card shadow-hover text-center border-primary-custom">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Grades Recorded</h5>
                        <h2 class="card-text fw-bold text-primary-custom" id="statGradesToday" data-value="{{ $liveStats['grades_today'] }}">
                            {{ $liveStats['grades_today'] }}
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-6 mb-3">
                <div class="card stat-card shadow-hover text-center border-accent">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Need Attention</h5>
                        <h2 class="card-text fw-bold text-warning" id="statNeedsAttention" data-value="{{ $liveStats['needs_attention_count'] }}">
                            {{ $liveStats['needs_attention_count'] }}
                        </h2>
                    </div>
                </div>
            </div>

        </div>


        <div class="row mb-4">

            <!-- Academic Alerts -->
            <div class="col-lg-5 mb-3">
                <div class="card shadow-hover h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="bi bi-exclamation-triangle text-warning me-1"></i>
                            Students Needing Attention
                        </h6>

                        <a href="{{ route('reports.index', ['report' => 'at_risk']) }}" class="small text-decoration-none">
                            View report
                        </a>
                    </div>

                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush" id="atRiskList">
                            @forelse ($attentionStudents as $s)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <a href="{{ route('students.show', $s) }}" class="text-decoration-none fw-semibold">{{ $s->name }}</a>
                                        <small class="text-muted d-block">{{ implode('; ', $s->alert_reasons) }}</small>
                                    </div>
                                    <span class="badge bg-{{ $s->standing_color }} {{ $s->standing_color === 'warning' ? 'text-dark' : '' }}">
                                        {{ number_format((float) $s->gpa, 2) }} · {{ $s->attendance_rate }}%
                                    </span>
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted py-4">
                                    All students are in good standing.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>


            <!-- Recent Activity -->
            <div class="col-lg-7 mb-3">
                <div class="card shadow-hover h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="bi bi-clock-history me-1"></i>
                            Recent Grades & Attendance
                        </h6>

                        <a href="{{ route('grades.index') }}" class="small text-decoration-none">
                            View grades
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Student</th>
                                        <th>Type</th>
                                        <th>Details</th>
                                        <th>By</th>
                                    </tr>
                                </thead>

                                <tbody id="recentActivityBody">
                                    @forelse ($recentActivity as $item)
                                        @php $isGrade = $item instanceof \App\Models\Grade; @endphp
                                        <tr>
                                            <td><small>{{ $item->created_at->diffForHumans() }}</small></td>
                                            <td>
                                                <a href="{{ route('students.show', $item->student_id) }}" class="text-decoration-none">
                                                    {{ $item->student?->name }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge status-badge bg-{{ $isGrade ? $item->grade_color : $item->status_color }} {{ ($isGrade ? $item->grade_color : $item->status_color) === 'warning' ? 'text-dark' : '' }}">
                                                    {{ $isGrade ? 'Grade' : 'Attendance' }}
                                                </span>
                                            </td>
                                            <td><small>
                                                @if ($isGrade)
                                                    {{ $item->subject }} ({{ $item->term_label }}): {{ number_format((float) $item->grade, 2) }}
                                                @else
                                                    {{ $item->status_label }} on {{ $item->date->format('M d') }}
                                                @endif
                                            </small></td>
                                            <td><small>{{ $item->user?->name ?? '—' }}</small></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">No activity yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>


    <!-- Student Roster -->
    <div class="card mb-4">

        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">

            <h5 class="mb-0">
                Students List
            </h5>

            <a
                href="{{ route('students.create') }}"
                class="btn btn-primary btn-sm"
            >
                + Add Student
            </a>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-striped table-hover align-middle">

                    <thead>

                        <tr class="border-accent">

                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Program</th>
                            <th>Year</th>
                            <th>Units</th>
                            <th>GPA</th>
                            <th>Attendance</th>
                            <th>Status</th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse ($students as $student)

                            <tr>

                                <td>
                                    {{ $student->student_id }}
                                </td>

                                <td>
                                    {{ $student->name }}
                                </td>

                                <td>
                                    {{ $student->program }}
                                </td>

                                <td>
                                    {{ $student->year_level }}
                                </td>

                                <td>
                                    {{ $student->units }}
                                </td>

                                <td>
                                    {{ number_format($student->gpa, 2) }}
                                </td>

                                <td>
                                    {{ $student->attendance_rate }}%
                                </td>

                                <td>

                                    @php

                                        $badgeClass = match($student->academic_standing) {

                                            'Good Standing' => 'bg-success',

                                            'At Risk' => 'bg-warning text-dark',

                                            default => 'bg-danger',

                                        };

                                    @endphp


                                    <span class="badge status-badge {{ $badgeClass }}">

                                        {{ $student->academic_standing }}

                                    </span>

                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td
                                    colspan="8"
                                    class="text-center text-muted"
                                >

                                    No students available.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    <!-- Students by Program -->
    <div class="card mb-4">

        <div class="card-header">

            <h5 class="mb-0">
                Students by Program
            </h5>

        </div>


        <div class="card-body">

            @forelse ($byProgram as $program => $count)

                <div class="d-flex justify-content-between border-bottom py-2">

                    <span>
                        {{ $program }}
                    </span>

                    <strong>
                        {{ $count }}
                    </strong>

                </div>

            @empty

                <p class="text-muted mb-0">
                    No program data available.
                </p>

            @endforelse

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<script>

    // ==========================================
    // Students by Program
    // ==========================================

    const programLabels = @json($byProgram->keys());

    const programData = @json($byProgram->values());


    new Chart(document.getElementById('programValueChart'), {

        type: 'bar',

        data: {

            labels: programLabels,

            datasets: [{

                label: 'Students',

                data: programData

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false

        }

    });


    // ==========================================
    // Academic Standing
    // ==========================================

    const standingData = {

        'Good Standing': 0,

        'At Risk': 0,

        'Probation': 0

    };


    @foreach ($students as $student)

        standingData['{{ $student->academic_standing }}']++;

    @endforeach


    new Chart(document.getElementById('standingChart'), {

        type: 'doughnut',

        data: {

            labels: Object.keys(standingData),

            datasets: [{

                data: Object.values(standingData)

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false

        }

    });


    // ==========================================
    // Top Students by GPA
    // ==========================================

    const topStudentLabels = @json($topStudents->pluck('name'));

    const topStudentGpa = @json($topStudents->pluck('gpa'));


    new Chart(document.getElementById('topStudentsChart'), {

        type: 'bar',

        data: {

            labels: topStudentLabels,

            datasets: [{

                label: 'GPA',

                data: topStudentGpa

            }]

        },

        options: {

            indexAxis: 'y',

            responsive: true,

            maintainAspectRatio: false,

            scales: {

                x: {

                    min: 0,

                    max: 4

                }

            }

        }

    });

</script>

<script>

    // ==========================================
    // Attendance Rate by Student
    // ==========================================

    const attendanceLabels = @json($students->pluck('name')->values());

    const attendanceData = @json($students->pluck('attendance_rate')->values());


    new Chart(document.getElementById('attendanceChart'), {

        type: 'bar',

        data: {

            labels: attendanceLabels,

            datasets: [{

                label: 'Attendance %',

                data: attendanceData

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            scales: {

                y: {

                    min: 0,

                    max: 100

                }

            }

        }

    });

</script>

<!-- Lab 6: real-time academic polling -->
<script src="{{ asset('js/academic-dashboard.js') }}"></script>

@endpush
