@extends('layouts.app')

@section('title', 'Grade Details')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

        <div>
            <h2 class="mb-1">{{ $grade->subject }} — {{ $grade->term_label }}</h2>
            <p class="text-muted mb-0">Recorded {{ $grade->created_at->format('F d, Y h:i A') }}</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('grades.edit', $grade) }}" class="btn btn-secondary">
                Edit
            </a>

            <a href="{{ route('grades.index') }}" class="btn btn-outline-secondary">
                Back to list
            </a>

            @can('admin')
                <form
                    method="POST"
                    action="{{ route('grades.destroy', $grade) }}"
                    onsubmit="return confirm('Delete this grade? The GPA will be recalculated.');"
                >
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>
                        Delete
                    </button>
                </form>
            @endcan
        </div>

    </div>


    <div class="row">

        <!-- Grade Details -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-hover h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Grade Details</h5>
                </div>

                <div class="card-body">
                    <table class="table mb-0">
                        <tr>
                            <th style="width: 170px;">Subject</th>
                            <td>{{ $grade->subject }}</td>
                        </tr>
                        <tr>
                            <th>Term</th>
                            <td>{{ $grade->term_label }}</td>
                        </tr>
                        <tr>
                            <th>Units</th>
                            <td>{{ $grade->units }}</td>
                        </tr>
                        <tr>
                            <th>Grade</th>
                            <td>
                                <span class="badge bg-{{ $grade->grade_color }} {{ $grade->grade_color === 'warning' ? 'text-dark' : '' }} fs-6">
                                    {{ number_format((float) $grade->grade, 2) }}
                                </span>
                                <span class="ms-2 {{ $grade->isPassing() ? 'value-good' : 'value-bad' }}">{{ $grade->result }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Remarks</th>
                            <td>{{ $grade->remarks ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td>{{ $grade->updated_at->format('M d, Y h:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>


        <div class="col-lg-6">

            <!-- Student Information -->
            <div class="card shadow-hover mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-vcard me-2"></i>Student Information</h5>
                </div>

                <div class="card-body">
                    <h5 class="mb-1">
                        <a href="{{ route('students.show', $grade->student) }}" class="text-decoration-none">
                            {{ $grade->student->name }}
                        </a>
                    </h5>

                    <p class="text-muted mb-3">
                        {{ $grade->student->student_id }} — {{ $grade->student->program }}, Year {{ $grade->student->year_level }}
                    </p>

                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>Current GPA</span>
                        <strong>{{ number_format((float) $grade->student->gpa, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>Attendance</span>
                        <strong>{{ $grade->student->attendance_rate }}%</strong>
                    </div>

                    <div class="d-flex justify-content-between py-2">
                        <span>Standing</span>
                        <span class="badge bg-{{ $grade->student->standing_color }} {{ $grade->student->standing_color === 'warning' ? 'text-dark' : '' }}">
                            {{ $grade->student->academic_standing }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Teacher Information -->
            <div class="card shadow-hover mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Recorded By</h5>
                </div>

                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-person-circle fs-1 text-primary-custom"></i>

                    <div>
                        @if ($grade->user)
                            <div class="fw-semibold">{{ $grade->user->name }}</div>
                            <small class="text-muted d-block">{{ $grade->user->email }}</small>
                            <span class="badge bg-light text-primary-custom border text-capitalize">{{ $grade->user->role }}</span>
                        @else
                            <span class="text-muted">User no longer exists</span>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

@endsection
