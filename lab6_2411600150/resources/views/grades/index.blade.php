@extends('layouts.app')

@section('title', 'Grades')

@section('content')

<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

        <div>
            <h2 class="mb-1">Grades</h2>

            <p class="text-muted mb-0">
                Every recorded grade. GPA is recalculated automatically.
            </p>
        </div>

        <a href="{{ route('grades.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            Record Grade
        </a>

    </div>


    <!-- Filters -->
    <div class="card shadow-hover mb-4">

        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-funnel me-2"></i>
                Grade Filters
            </h5>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('grades.index') }}">

                <div class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label for="student_id" class="form-label fw-semibold">Student</label>

                        <select name="student_id" id="student_id" class="form-select">
                            <option value="">All Students</option>

                            @foreach ($students as $student)
                                <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="program" class="form-label fw-semibold">Program</label>

                        <select name="program" id="program" class="form-select">
                            <option value="">All Programs</option>

                            @foreach ($programs as $program)
                                <option value="{{ $program }}" {{ request('program') == $program ? 'selected' : '' }}>
                                    {{ $program }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="subject" class="form-label fw-semibold">Subject</label>

                        <select name="subject" id="subject" class="form-select">
                            <option value="">All Subjects</option>

                            @foreach ($subjects as $subject)
                                <option value="{{ $subject }}" {{ request('subject') == $subject ? 'selected' : '' }}>
                                    {{ $subject }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="term" class="form-label fw-semibold">Term</label>

                        <select name="term" id="term" class="form-select">
                            <option value="">All Terms</option>

                            @foreach ($terms as $value => $label)
                                <option value="{{ $value }}" {{ request('term') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="result" value="failed" id="failedOnly" {{ request('result') === 'failed' ? 'checked' : '' }}>
                            <label class="form-check-label" for="failedOnly">Failed grades only</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-search me-1"></i>
                                Apply
                            </button>

                            <a href="{{ route('grades.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- Grades Table -->
    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Grade List</h5>

            <span class="badge bg-secondary">
                {{ $grades->total() }} grade(s)
            </span>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-striped table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Date Recorded</th>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>Term</th>
                            <th class="text-center">Units</th>
                            <th class="text-center">Grade</th>
                            <th>Recorded By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($grades as $grade)

                            <tr>
                                <td>{{ $grade->created_at->format('M d, Y h:i A') }}</td>

                                <td>
                                    <a href="{{ route('students.show', $grade->student_id) }}" class="text-decoration-none fw-semibold">
                                        {{ $grade->student?->name }}
                                    </a>
                                    <small class="text-muted d-block">{{ $grade->student?->student_id }}</small>
                                </td>

                                <td>{{ $grade->subject }}</td>

                                <td>{{ $grade->term_label }}</td>

                                <td class="text-center">{{ $grade->units }}</td>

                                <td class="text-center">
                                    <span class="badge status-badge bg-{{ $grade->grade_color }} {{ $grade->grade_color === 'warning' ? 'text-dark' : '' }}">
                                        {{ number_format((float) $grade->grade, 2) }}
                                    </span>
                                </td>

                                <td>{{ $grade->user?->name ?? '—' }}</td>

                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('grades.show', $grade) }}" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="{{ route('grades.edit', $grade) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        @can('admin')
                                            <form
                                                method="POST"
                                                action="{{ route('grades.destroy', $grade) }}"
                                                onsubmit="return confirm('Delete this grade? The GPA will be recalculated.');"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-search fs-3 d-block mb-2"></i>
                                    No grades found matching your filters.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{ $grades->links() }}

        </div>

    </div>

</div>

@endsection
