{{-- Shared fields for Record Grade and Edit Grade --}}

<div class="row">

    <!-- Student (searchable) -->
    <div class="col-md-7 mb-3">

        <label for="student_id" class="form-label fw-semibold">Student</label>

        <input
            type="text"
            id="studentSearch"
            class="form-control form-control-sm mb-2"
            placeholder="Type to search by name or Student ID..."
            autocomplete="off"
        >

        <select
            name="student_id"
            id="student_id"
            class="form-select @error('student_id') is-invalid @enderror"
            required
        >
            <option value="">-- Select student --</option>

            @foreach ($students as $student)
                <option
                    value="{{ $student->id }}"
                    data-search="{{ strtolower($student->name . ' ' . $student->student_id) }}"
                    {{ old('student_id', $selectedStudent) == $student->id ? 'selected' : '' }}
                >
                    {{ $student->name }} ({{ $student->student_id }})
                </option>
            @endforeach
        </select>

        @error('student_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror

    </div>


    <!-- Current GPA (loaded with AJAX) -->
    <div class="col-md-5 mb-3">
        <label class="form-label fw-semibold">Current Standing</label>

        <div id="studentInfo" class="info-box p-3">
            <span class="text-muted">Select a student to see their GPA.</span>
        </div>
    </div>

</div>


<div class="row">

    <!-- Subject -->
    <div class="col-md-6 mb-3">
        <label for="subject" class="form-label fw-semibold">Subject</label>

        <input
            type="text"
            name="subject"
            id="subject"
            list="subjectList"
            class="form-control @error('subject') is-invalid @enderror"
            value="{{ old('subject', $grade->subject ?? '') }}"
            placeholder="e.g. Programming 2"
            maxlength="100"
            required
        >

        <datalist id="subjectList">
            @foreach ($subjects as $subject)
                <option value="{{ $subject }}">
            @endforeach
        </datalist>

        @error('subject')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <!-- Units -->
    <div class="col-md-2 mb-3">
        <label for="units" class="form-label fw-semibold">Units</label>

        <input
            type="number"
            name="units"
            id="units"
            min="1"
            max="6"
            class="form-control @error('units') is-invalid @enderror"
            value="{{ old('units', $grade->units ?? 3) }}"
            required
        >

        @error('units')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <!-- Term -->
    <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold d-block">Term</label>

        @foreach ($terms as $value => $label)
            <div class="form-check form-check-inline">
                <input
                    class="form-check-input"
                    type="radio"
                    name="term"
                    id="term_{{ $value }}"
                    value="{{ $value }}"
                    {{ old('term', $grade->term ?? 'prelim') == $value ? 'checked' : '' }}
                    required
                >
                <label class="form-check-label" for="term_{{ $value }}">{{ $label }}</label>
            </div>
        @endforeach

        @error('term')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

</div>


<div class="row">

    <!-- Grade -->
    <div class="col-md-4 mb-3">
        <label for="grade" class="form-label fw-semibold">Grade (0.00 – 4.00)</label>

        <input
            type="number"
            step="0.01"
            min="0"
            max="4"
            name="grade"
            id="grade"
            class="form-control @error('grade') is-invalid @enderror"
            value="{{ old('grade', isset($grade) ? number_format((float) $grade->grade, 2) : '') }}"
            required
        >

        <div class="form-text" id="gradeHelp">3.00 and above = Good Standing. Below 1.00 = Failed.</div>

        @error('grade')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <!-- Remarks -->
    <div class="col-md-8 mb-3">
        <label for="remarks" class="form-label fw-semibold">Remarks</label>

        <textarea
            name="remarks"
            id="remarks"
            rows="2"
            maxlength="500"
            class="form-control @error('remarks') is-invalid @enderror"
        >{{ old('remarks', $grade->remarks ?? '') }}</textarea>

        @error('remarks')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

</div>


@push('scripts')
<script>
    (function () {
        const select = document.getElementById('student_id');
        const search = document.getElementById('studentSearch');
        const info = document.getElementById('studentInfo');
        const summaryUrl = @json(route('api.students.summary', ['student' => '__ID__']));

        // Searchable dropdown: hide options that do not match
        search.addEventListener('input', function () {
            const term = this.value.trim().toLowerCase();
            let firstMatch = null;

            Array.from(select.options).forEach(function (option) {
                if (!option.value) return;
                const match = option.dataset.search.includes(term);
                option.hidden = !match;
                if (match && !firstMatch) firstMatch = option;
            });

            const current = select.selectedOptions[0];

            if (term && firstMatch && (!current || !current.value || current.hidden)) {
                select.value = firstMatch.value;
                loadSummary();
            }
        });

        // Show the student's current GPA, attendance and standing
        function loadSummary() {
            if (!select.value) {
                info.innerHTML = '<span class="text-muted">Select a student to see their GPA.</span>';
                return;
            }

            info.innerHTML = '<span class="text-muted"><span class="spinner-border spinner-border-sm me-1"></span> Loading...</span>';

            fetch(summaryUrl.replace('__ID__', select.value), { headers: { 'Accept': 'application/json' } })
                .then(function (response) {
                    if (!response.ok) throw new Error('Request failed');
                    return response.json();
                })
                .then(function (json) {
                    const s = json.data;
                    const dark = s.standing_color === 'warning' ? ' text-dark' : '';

                    info.innerHTML =
                        '<div class="d-flex justify-content-between align-items-center">' +
                            '<div><div class="fs-4 fw-bold text-primary-custom">GPA ' + s.gpa.toFixed(2) + '</div>' +
                            '<small class="text-muted">' + s.program + ' · Attendance ' + s.attendance_rate + '%</small></div>' +
                            '<span class="badge bg-' + s.standing_color + dark + '">' + s.academic_standing + '</span>' +
                        '</div>';
                })
                .catch(function () {
                    info.innerHTML = '<span class="text-danger">Could not load the student. Please try again.</span>';
                });
        }

        select.addEventListener('change', loadSummary);

        if (select.value) loadSummary();
    })();
</script>
@endpush
