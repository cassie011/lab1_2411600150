@extends('layouts.app')

@section('title', 'My Records')

@section('content')

<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h2>
            {{ $greeting }}, {{ auth()->user()->name }}!
        </h2>
    </div>


    @if (! $student)

        <div class="alert alert-info">
            <i class="bi bi-info-circle me-1"></i>
            Your account is not linked to a student record yet. Please ask your teacher or the admin.
        </div>

    @else

        @if ($student->needsAttention())
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>Academic Alert:</strong>
                {{ implode('; ', $student->alert_reasons) }}. Please see your adviser.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif


        <!-- My Statistics -->
        <div class="row mb-4">

            <div class="col-md-3 col-6 mb-3">
                <div class="card stat-card shadow-hover text-center">
                    <div class="card-body">
                        <h5 class="card-title text-muted">My GPA</h5>
                        <h2 class="card-text fw-bold text-primary-custom">{{ number_format((float) $student->gpa, 2) }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-6 mb-3">
                <div class="card stat-card shadow-hover text-center">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Attendance</h5>
                        <h2 class="card-text fw-bold {{ $student->hasLowAttendance() ? 'text-danger' : 'text-success' }}">{{ $student->attendance_rate }}%</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-6 mb-3">
                <div class="card stat-card shadow-hover text-center">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Standing</h5>
                        <h2 class="card-text">
                            <span class="badge bg-{{ $student->standing_color }} {{ $student->standing_color === 'warning' ? 'text-dark' : '' }} fs-6">
                                {{ $student->academic_standing }}
                            </span>
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-6 mb-3">
                <div class="card stat-card shadow-hover text-center">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Absences</h5>
                        <h2 class="card-text fw-bold text-warning">{{ $attendanceCounts['absent'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>

        </div>


        <!-- My Profile -->
        <div class="bg-white p-4 rounded shadow-sm mb-4">
            <h5 class="mb-3 text-primary-custom">My Profile</h5>

            <table class="table mb-0">
                <tr>
                    <th style="width: 200px;">Student ID</th>
                    <td>{{ $student->student_id }}</td>
                </tr>
                <tr>
                    <th>Program</th>
                    <td>{{ $student->program }}</td>
                </tr>
                <tr>
                    <th>Year Level</th>
                    <td>{{ $student->year_level }}</td>
                </tr>
                <tr>
                    <th>Units</th>
                    <td>{{ $student->units }}</td>
                </tr>
            </table>
        </div>


        <div class="row">

            <!-- My Grades -->
            <div class="col-lg-7 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-journal-text me-2"></i>
                            My Grades
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th class="text-center">Units</th>
                                        @foreach ($terms as $label)
                                            <th class="text-center">{{ $label }}</th>
                                        @endforeach
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($grades as $subject => $subjectGrades)
                                        @php $byTerm = $subjectGrades->keyBy('term'); @endphp
                                        <tr>
                                            <td>{{ $subject }}</td>
                                            <td class="text-center">{{ $subjectGrades->first()->units }}</td>
                                            @foreach ($terms as $termKey => $label)
                                                <td class="text-center">
                                                    @if (isset($byTerm[$termKey]))
                                                        <span class="badge status-badge bg-{{ $byTerm[$termKey]->grade_color }} {{ $byTerm[$termKey]->grade_color === 'warning' ? 'text-dark' : '' }}">
                                                            {{ number_format((float) $byTerm[$termKey]->grade, 2) }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 2 + count($terms) }}" class="text-center text-muted py-4">No grades yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <!-- My Attendance -->
            <div class="col-lg-5 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-check me-2"></i>
                            My Attendance
                        </h5>

                        <small class="text-muted">
                            P {{ $attendanceCounts['present'] ?? 0 }} ·
                            L {{ $attendanceCounts['late'] ?? 0 }} ·
                            A {{ $attendanceCounts['absent'] ?? 0 }} ·
                            E {{ $attendanceCounts['excused'] ?? 0 }}
                        </small>
                    </div>

                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse ($attendance as $record)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $record->date->format('D, M d, Y') }}</span>
                                    <span class="badge bg-{{ $record->status_color }} {{ $record->status_color === 'warning' ? 'text-dark' : '' }}">
                                        {{ $record->status_label }}
                                    </span>
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted py-4">No attendance records yet.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

        </div>

    @endif

</div>

@endsection
