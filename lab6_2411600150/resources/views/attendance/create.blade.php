@extends('layouts.app')

@section('title', 'Take Attendance')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1">Take Attendance</h2>
            <p class="text-muted mb-0">
                Pick a class and date. Saved records for that date load automatically, so you can also correct them here.
            </p>
        </div>

        <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-list-check me-1"></i>
            Attendance Records
        </a>
    </div>


    <!-- Class Picker -->
    <div class="card shadow-hover mb-4">

        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-people me-2"></i>
                Select Class
            </h5>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('attendance.create') }}">

                <div class="row g-3 align-items-end">

                    <div class="col-md-4">
                        <label for="program" class="form-label fw-semibold">Program</label>

                        <select name="program" id="program" class="form-select" required>
                            <option value="">-- Select program --</option>

                            @foreach ($programs as $program)
                                <option value="{{ $program }}" {{ request('program') == $program ? 'selected' : '' }}>
                                    {{ $program }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="year_level" class="form-label fw-semibold">Year Level</label>

                        <select name="year_level" id="year_level" class="form-select">
                            <option value="">All</option>

                            @for ($y = 1; $y <= 4; $y++)
                                <option value="{{ $y }}" {{ request('year_level') == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="date" class="form-label fw-semibold">Date</label>

                        <input
                            type="date"
                            name="date"
                            id="date"
                            class="form-control @error('date') is-invalid @enderror"
                            value="{{ $date }}"
                            max="{{ now()->toDateString() }}"
                            required
                        >

                        @error('date')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>
                            Load Class
                        </button>
                    </div>

                </div>

            </form>

        </div>

    </div>


    @if (request()->filled('program'))

        <!-- Class List -->
        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf

            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="program" value="{{ request('program') }}">
            <input type="hidden" name="year_level" value="{{ request('year_level') }}">

            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <h5 class="mb-0">
                        {{ request('program') }}
                        @if (request('year_level')) — Year {{ request('year_level') }} @endif
                        <small class="text-muted fs-6">· {{ \Illuminate\Support\Carbon::parse($date)->format('l, M d, Y') }}</small>
                    </h5>

                    @if ($students->isNotEmpty())
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="markAll('present')">
                                All Present
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAll('absent')">
                                All Absent
                            </button>
                        </div>
                    @endif

                </div>

                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
                    @endif

                    <div class="table-responsive">

                        <table class="table table-hover align-middle attendance-table">

                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th class="text-center">Current Rate</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($students as $student)

                                    @php
                                        $current = old("attendance.{$student->id}.status", $existing[$student->id]->status ?? 'present');
                                    @endphp

                                    <tr>
                                        <td>{{ $student->student_id }}</td>

                                        <td>
                                            {{ $student->name }}
                                            @if (isset($existing[$student->id]))
                                                <span class="badge bg-light text-muted border ms-1">saved</span>
                                            @endif
                                        </td>

                                        <td class="text-center {{ $student->hasLowAttendance() ? 'value-bad' : '' }}">
                                            {{ $student->attendance_rate }}%
                                        </td>

                                        <td>
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Status for {{ $student->name }}">
                                                @foreach ($statuses as $value => $label)
                                                    @php
                                                        $color = match ($value) {
                                                            'present' => 'success',
                                                            'late' => 'warning',
                                                            'excused' => 'info',
                                                            default => 'danger',
                                                        };
                                                    @endphp

                                                    <input
                                                        type="radio"
                                                        class="btn-check status-radio"
                                                        name="attendance[{{ $student->id }}][status]"
                                                        id="status_{{ $student->id }}_{{ $value }}"
                                                        value="{{ $value }}"
                                                        autocomplete="off"
                                                        {{ $current === $value ? 'checked' : '' }}
                                                    >
                                                    <label class="btn btn-outline-{{ $color }}" for="status_{{ $student->id }}_{{ $value }}">
                                                        {{ $label }}
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>

                                        <td>
                                            <input
                                                type="text"
                                                name="attendance[{{ $student->id }}][remarks]"
                                                class="form-control form-control-sm"
                                                maxlength="255"
                                                value="{{ old("attendance.{$student->id}.remarks", $existing[$student->id]->remarks ?? '') }}"
                                                placeholder="Optional"
                                            >
                                        </td>
                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No students in this class.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                    @if ($students->isNotEmpty())
                        <button type="submit" class="btn btn-primary mt-2">
                            <i class="bi bi-check2-circle me-1"></i>
                            Save Attendance
                        </button>
                    @endif

                </div>

            </div>

        </form>

    @endif

</div>

@endsection


@push('scripts')
<script>
    // Set the same status for every student in the list
    function markAll(status) {
        document.querySelectorAll('.status-radio[value="' + status + '"]').forEach(function (radio) {
            radio.checked = true;
        });
    }
</script>
@endpush
