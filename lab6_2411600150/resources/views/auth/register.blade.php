<x-guest-layout>

    <div class="login-page min-vh-100 d-flex justify-content-center align-items-center">

        <div class="container">

            <div class="d-flex justify-content-center align-items-center min-vh-100">

                <div class="card login-card shadow-lg" style="max-width: 440px; width: 100%;">

                    <!-- Register Header -->
                    <div class="card-header text-center py-4">

                        <h2 class="mb-0">
                            STUDENT PORTAL
                        </h2>

                        <p class="mb-0 small">
                            Create a new account
                        </p>

                    </div>

                    <!-- Register Form -->
                    <div class="card-body p-4">

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>

                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="name"
                                        name="name"
                                        value="{{ old('name') }}"
                                        placeholder="Enter full name"
                                        required
                                        autofocus
                                        autocomplete="name"
                                    >
                                </div>

                                @error('name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>

                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input
                                        type="email"
                                        class="form-control"
                                        id="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        placeholder="Enter email"
                                        required
                                        autocomplete="username"
                                    >
                                </div>

                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Role (Lab 6) -->
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>

                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                    <select class="form-select" id="role" name="role" required onchange="toggleStudentNumber()">
                                        <option value="student" {{ old('role', 'student') === 'student' ? 'selected' : '' }}>
                                            Student – view my grades and attendance
                                        </option>
                                        <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>
                                            Teacher – record grades and attendance
                                        </option>
                                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>
                                            Admin – all features, can delete records
                                        </option>
                                    </select>
                                </div>

                                @error('role')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Student ID (students only) -->
                            <div class="mb-3" id="studentNumberGroup">
                                <label for="student_number" class="form-label">Student ID</label>

                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="student_number"
                                        name="student_number"
                                        value="{{ old('student_number') }}"
                                        placeholder="e.g. 2411600001"
                                    >
                                </div>

                                <div class="form-text">Must match your record in the Students list.</div>

                                @error('student_number')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>

                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input
                                        type="password"
                                        class="form-control"
                                        id="password"
                                        name="password"
                                        placeholder="At least 8 characters"
                                        required
                                        autocomplete="new-password"
                                    >
                                </div>

                                @error('password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>

                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input
                                        type="password"
                                        class="form-control"
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        placeholder="Re-enter password"
                                        required
                                        autocomplete="new-password"
                                    >
                                </div>
                            </div>

                            <!-- Register Button -->
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                Register
                            </button>

                        </form>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Already have an account?
                                <a href="{{ route('login') }}" class="text-primary-custom fw-semibold">Login</a>
                            </small>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script>
        // Show the Student ID field only when "Student" is selected
        function toggleStudentNumber() {
            const isStudent = document.getElementById('role').value === 'student';
            document.getElementById('studentNumberGroup').style.display = isStudent ? '' : 'none';
            document.getElementById('student_number').required = isStudent;
        }

        toggleStudentNumber();
    </script>

</x-guest-layout>
