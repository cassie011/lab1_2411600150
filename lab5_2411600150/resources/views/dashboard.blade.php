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

                    <h2 class="card-text fw-bold text-primary-custom">
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

                    <h2 class="card-text fw-bold text-primary-custom">
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

                    <h2 class="card-text fw-bold text-warning">
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

                    <h2 class="card-text fw-bold text-success">
                        {{ $stats['average_attendance'] }}%
                    </h2>

                </div>

            </div>

        </div>

    </div>


    <!-- Charts -->
    <div class="row mb-4">

        <!-- Students by Program -->
        <div class="col-lg-4 col-md-6 mb-3">

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
        <div class="col-lg-4 col-md-6 mb-3">

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
        <div class="col-lg-4 col-md-12 mb-3">

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

@endpush

