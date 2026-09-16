<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Student Portal')); ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
        rel="stylesheet"
    >

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <!-- Custom Lab 4 CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('css/theme.css')); ?>">

    <!-- Laravel Vite -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <style>
        /* ================================
           MOBILE NAVIGATION
        ================================= */

        .mobile-header {
            display: none;
        }

        .sidebar-overlay {
            display: none;
        }

        @media (max-width: 768px) {

            /* Hide desktop sidebar */
            .sidebar {
                position: fixed !important;
                top: 0;
                left: 0;
                width: 260px !important;
                height: 100vh !important;
                min-height: 100vh !important;
                z-index: 1050;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                overflow-y: auto;
            }

            /* Show sidebar when menu is open */
            .sidebar.mobile-open {
                transform: translateX(0);
            }

            /* Dark background behind drawer */
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.4);
                z-index: 1040;
            }

            .sidebar-overlay.show {
                display: block;
            }

            /* Mobile top bar */
            .mobile-header {
                display: flex;
                height: 60px;
                background: #ffffff;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                align-items: center;
                justify-content: space-between;
                padding: 0 15px;
                position: sticky;
                top: 0;
                z-index: 1030;
            }

            /* Hamburger button */
            .hamburger-btn {
                border: none;
                background: transparent;
                color: var(--theme-primary);
                font-size: 1.7rem;
                padding: 5px 8px;
                border-radius: 8px;
                cursor: pointer;
            }

            .hamburger-btn:hover {
                background-color: var(--theme-light-accent);
            }

            /* Mobile title */
            .mobile-title {
                color: var(--theme-primary);
                font-weight: 700;
                font-size: 1rem;
            }

            /* Main content */
            .main-content {
                width: 100%;
            }

            /* Hide desktop header on mobile */
            .desktop-header {
                display: none;
            }

            /* Smaller page padding */
            main.p-4 {
                padding: 1rem !important;
            }

            /* Make tables scroll horizontally */
            .table-responsive {
                overflow-x: auto;
            }
        }

        @media (min-width: 769px) {
            .main-content {
                min-width: 0;
            }
        }
    </style>
</head>

<body>

    <!-- Mobile Overlay -->
    <div
        id="sidebarOverlay"
        class="sidebar-overlay"
        onclick="closeMobileMenu()"
    ></div>


    <div class="d-flex min-vh-100">

        <!-- ================= SIDEBAR ================= -->
        <aside
            id="sidebar"
            class="sidebar p-3 d-flex flex-column"
            style="width: 250px; min-height: 100vh;"
        >

            <!-- Close button for mobile -->
            <div class="d-flex justify-content-end d-md-none mb-2">
                <button
                    type="button"
                    class="btn btn-sm btn-light"
                    onclick="closeMobileMenu()"
                    aria-label="Close menu"
                >
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>


            <!-- Logo -->
            <div class="text-center mb-4">

                <h4 class="fw-bold text-primary-custom mb-1">
                    STUDENT PORTAL
                </h4>

                <small class="text-muted">
                    Management System
                </small>

            </div>


            <!-- User -->
            <div class="text-center mb-4">

                <i class="bi bi-person-circle fs-1 text-primary-custom"></i>

                <div class="fw-semibold mt-2">
                    <?php echo e(auth()->user()->name); ?>

                </div>

                <small class="text-muted d-block text-truncate">
                    <?php echo e(auth()->user()->email); ?>

                </small>

            </div>


            <!-- Navigation -->
            <nav class="nav flex-column">

                <!-- Dashboard -->
                <a
                    href="<?php echo e(route('dashboard')); ?>"
                    class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>"
                    onclick="closeMobileMenu()"
                >
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>


                <!-- Students -->
                <a
                    href="<?php echo e(route('students.index')); ?>"
                    class="nav-link <?php echo e(request()->routeIs('students.*') ? 'active' : ''); ?>"
                    onclick="closeMobileMenu()"
                >
                    <i class="bi bi-people me-2"></i>
                    Students
                </a>

            </nav>


            <!-- Logout -->
            <div class="mt-auto pt-3">

                <form
                    method="POST"
                    action="<?php echo e(route('logout')); ?>"
                >

                    <?php echo csrf_field(); ?>

                    <button
                        type="submit"
                        class="nav-link text-danger border-0 bg-transparent w-100 text-start"
                    >
                        <i class="bi bi-box-arrow-right me-2"></i>
                        Logout
                    </button>

                </form>

            </div>

        </aside>


        <!-- ================= MAIN AREA ================= -->
        <div class="flex-grow-1 main-content">


            <!-- ================= MOBILE HEADER ================= -->
            <div class="mobile-header">

                <button
                    type="button"
                    class="hamburger-btn"
                    onclick="toggleMobileMenu()"
                    aria-label="Open navigation menu"
                    aria-expanded="false"
                >
                    <i class="bi bi-list"></i>
                </button>


                <div class="mobile-title">
                    <?php if(request()->routeIs('dashboard')): ?>
                        Dashboard
                    <?php elseif(request()->routeIs('students.index')): ?>
                        Students
                    <?php elseif(request()->routeIs('students.create')): ?>
                        Add Student
                    <?php elseif(request()->routeIs('students.show')): ?>
                        Student Details
                    <?php elseif(request()->routeIs('students.edit')): ?>
                        Edit Student
                    <?php else: ?>
                        Student Portal
                    <?php endif; ?>
                </div>


                <i class="bi bi-person-circle text-primary-custom fs-5"></i>

            </div>


            <!-- ================= DESKTOP HEADER ================= -->
            <header class="bg-white shadow-sm p-3 desktop-header">

                <div class="d-flex justify-content-between align-items-center">

                    <h5 class="mb-0 text-primary-custom">

                        <?php if(request()->routeIs('dashboard')): ?>
                            Dashboard

                        <?php elseif(request()->routeIs('students.index')): ?>
                            Students

                        <?php elseif(request()->routeIs('students.create')): ?>
                            Add Student

                        <?php elseif(request()->routeIs('students.show')): ?>
                            Student Details

                        <?php elseif(request()->routeIs('students.edit')): ?>
                            Edit Student

                        <?php else: ?>
                            Student Portal
                        <?php endif; ?>

                    </h5>


                    <div class="text-muted">

                        <i class="bi bi-person-circle me-1"></i>

                        <?php echo e(auth()->user()->name); ?>


                    </div>

                </div>

            </header>


            <!-- ================= ALERTS ================= -->

            <?php if(session('success')): ?>

                <div class="container-fluid mt-3">

                    <div
                        class="alert alert-success alert-dismissible fade show"
                        role="alert"
                    >

                        <i class="bi bi-check-circle me-2"></i>

                        <?php echo e(session('success')); ?>


                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                        ></button>

                    </div>

                </div>

            <?php endif; ?>


            <?php if(session('error')): ?>

                <div class="container-fluid mt-3">

                    <div
                        class="alert alert-danger alert-dismissible fade show"
                        role="alert"
                    >

                        <i class="bi bi-exclamation-circle me-2"></i>

                        <?php echo e(session('error')); ?>


                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                        ></button>

                    </div>

                </div>

            <?php endif; ?>


            <!-- ================= PAGE CONTENT ================= -->

            <main class="p-4">

                <?php echo $__env->yieldContent('content'); ?>

            </main>

        </div>

    </div>


    <!-- Bootstrap JS -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>


    <!-- Mobile Menu -->
    <script>

        function toggleMobileMenu() {

            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const button = document.querySelector('.hamburger-btn');

            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('show');

            const isOpen = sidebar.classList.contains('mobile-open');

            button.setAttribute('aria-expanded', isOpen);

        }


        function closeMobileMenu() {

            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const button = document.querySelector('.hamburger-btn');

            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('show');

            if (button) {
                button.setAttribute('aria-expanded', 'false');
            }

        }


        /* Close menu when pressing Escape */
        document.addEventListener('keydown', function (event) {

            if (event.key === 'Escape') {

                closeMobileMenu();

            }

        });


        /* Automatically close menu when resizing to desktop */
        window.addEventListener('resize', function () {

            if (window.innerWidth > 768) {

                closeMobileMenu();

            }

        });

    </script>


    <?php echo $__env->yieldPushContent('scripts'); ?>

</body>

</html><?php /**PATH C:\xampp\htdocs\lab5_2411600150\resources\views/layouts/app.blade.php ENDPATH**/ ?>