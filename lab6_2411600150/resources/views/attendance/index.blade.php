@extends('layouts.app')

@section('title', 'Attendance Records')

@section('content')

<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

        <div>
            <h2 class="mb-1">Attendance Records</h2>
            <p class="text-muted mb-0">Every attendance entry and who recorded it</p>
        </div>

        <a href="{{ route('attendance.create') }}" class="btn btn-primary">
            <i class="bi bi-calendar-check me-1"></i>
            Take Attendance
        </a>

    </div>


    <!-- Filters -->
    <div class="card shadow-hover mb-4">

        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-funnel me-2"></i>
                Attendance Filters
            </h5>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('attendance.index') }}">

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
                            <option value="">All</option>

                            @foreach ($programs as $program)
                                <option value="{{ $program }}" {{ request('program') == $program ? 'selected' : '' }}>
                                    {{ $program }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="status" class="form-label fw-semibold">Status</label>

                        <select name="status" id="status" class="form-select">
                            <option value="">All</option>

                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="date_from" class="form-label fw-semibold">From</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-2">
                        <label for="date_to" class="form-label fw-semibold">To</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-md-1">
                        <div class="d-flex gap-1">
                            <button type="submit" class="btn btn-primary flex-grow-1" title="Apply">
                                <i class="bi bi-search"></i>
                            </button>

                            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </div>

                </div>

                @if ($errors->any())
                    <div class="text-danger small mt-2">{{ $errors->first() }}</div>
                @endif

            </form>

        </div>

    </div>


    <!-- Records Table -->
    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Attendance List</h5>

            <span class="badge bg-secondary">
                {{ $records->total() }} record(s)
            </span>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-striped table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Program</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Recorded By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($records as $record)

                            <tr>
                                <td>{{ $record->date->format('M d, Y') }}</td>

                                <td>
                                    <a href="{{ route('students.show', $record->student_id) }}" class="text-decoration-none fw-semibold">
                                        {{ $record->student?->name }}
                                    </a>
                                    <small class="text-muted d-block">{{ $record->student?->student_id }}</small>
                                </td>

                                <td>{{ $record->student?->program }}</td>

                                <td>
                                    <span class="badge status-badge bg-{{ $record->status_color }} {{ $record->status_color === 'warning' ? 'text-dark' : '' }}">
                                        {{ $record->status_label }}
                                    </span>
                                </td>

                                <td>{{ $record->remarks ?: '—' }}</td>

                                <td>{{ $record->user?->name ?? '—' }}</td>

                                <td>
                                    <div class="d-flex gap-1">
                                        <a
                                            href="{{ route('attendance.create', ['program' => $record->student?->program, 'date' => $record->date->toDateString()]) }}"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Edit this day"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        @can('admin')
                                            <form
                                                method="POST"
                                                action="{{ route('attendance.destroy', $record) }}"
                                                onsubmit="return confirm('Delete this attendance record? The attendance rate will be recalculated.');"
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
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-search fs-3 d-block mb-2"></i>
                                    No attendance records found matching your filters.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{ $records->links() }}

        </div>

    </div>

</div>

@endsection
