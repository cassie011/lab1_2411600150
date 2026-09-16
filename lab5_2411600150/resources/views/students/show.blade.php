@extends('layouts.app')

@section('title', $student->name)

@section('content')

<div class="bg-white p-4 rounded shadow-sm">

    <h2>{{ $student->name }}</h2>

    <p class="text-muted">
        {{ $student->student_id }} — {{ $student->program }}
    </p>

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

@endsection