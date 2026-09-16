<x-guest-layout>

    <div class="login-page min-vh-100 d-flex justify-content-center align-items-center">

        <div class="container">

            <div class="d-flex justify-content-center align-items-center min-vh-100">

                <div class="card login-card shadow-lg" style="max-width: 400px; width: 100%;">

                    <!-- Login Header -->
                    <div class="card-header text-center py-4">

                        <h2 class="mb-0">
                            STUDENT PORTAL
                        </h2>

                        <p class="mb-0 small">
                            Login to your dashboard
                        </p>

                    </div>

                    <!-- Login Form -->
                    <div class="card-body p-4">

                        <!-- Session Status -->
                        <x-auth-session-status
                            class="mb-4"
                            :status="session('status')"
                        />

                        <!-- Validation Errors -->
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Email Field -->
                            <div class="mb-3">

                                <label for="email" class="form-label">
                                    Email
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        <i class="bi bi-person"></i>
                                    </span>

                                    <input
                                        type="email"
                                        class="form-control"
                                        id="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        placeholder="Enter email"
                                        required
                                        autofocus
                                        autocomplete="username"
                                    >

                                </div>

                                @error('email')
                                    <div class="text-danger small mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <!-- Password Field -->
                            <div class="mb-3">

                                <label for="password" class="form-label">
                                    Password
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>

                                    <input
                                        type="password"
                                        class="form-control"
                                        id="password"
                                        name="password"
                                        placeholder="Enter password"
                                        required
                                        autocomplete="current-password"
                                    >

                                </div>

                                @error('password')
                                    <div class="text-danger small mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <!-- Remember Me -->
                            <div class="mb-3 form-check">

                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    id="rememberMe"
                                    name="remember"
                                >

                                <label
                                    class="form-check-label"
                                    for="rememberMe"
                                >
                                    Remember Me
                                </label>

                            </div>

                            <!-- Login Button -->
                            <button
                                type="submit"
                                class="btn btn-primary w-100 py-2"
                            >
                                Login
                            </button>

                        </form>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Use your registered account to continue.
                            </small>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-guest-layout>