@if (!isset($student))

    <div class="mb-3">
        <label class="form-label">Student ID</label>

        <input
            type="text"
            name="student_id"
            class="form-control"
            value="{{ old('student_id') }}"
            required
        >

        @error('student_id')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

@endif

<div class="mb-3">
    <label class="form-label">Name</label>

    <input
        type="text"
        name="name"
        class="form-control"
        value="{{ old('name', $student->name ?? '') }}"
        required
    >

    @error('name')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Program</label>

    <input
        type="text"
        name="program"
        class="form-control"
        value="{{ old('program', $student->program ?? '') }}"
        required
    >

    @error('program')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="row">

    <div class="col mb-3">
        <label class="form-label">Year Level</label>

        <input
            type="number"
            name="year_level"
            class="form-control"
            min="1"
            max="6"
            value="{{ old('year_level', $student->year_level ?? 1) }}"
            required
        >
    </div>

    <div class="col mb-3">
        <label class="form-label">Units</label>

        <input
            type="number"
            name="units"
            class="form-control"
            min="0"
            value="{{ old('units', $student->units ?? 0) }}"
            required
        >
    </div>

    <div class="col mb-3">
        <label class="form-label">GPA</label>

        <input
            type="number"
            step="0.01"
            name="gpa"
            class="form-control"
            min="0"
            max="4"
            value="{{ old('gpa', $student->gpa ?? '') }}"
            required
        >

        @error('gpa')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <div class="col mb-3">
        <label class="form-label">Attendance %</label>

        <input
            type="number"
            name="attendance_rate"
            class="form-control"
            min="0"
            max="100"
            value="{{ old('attendance_rate', $student->attendance_rate ?? '') }}"
            required
        >
    </div>

</div>