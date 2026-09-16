<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>

<div class="container-fluid">

    <!-- Dashboard Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">

        <h2>
            <?php echo e($greeting); ?>, <?php echo e(auth()->user()->name); ?>!
        </h2>

    </div>


    <!-- Academic Warning Alert -->
    <?php if($stats['at_risk_count'] > 0): ?>

        <div
            id="atRiskAlert"
            class="alert alert-warning alert-dismissible fade show"
            role="alert"
        >

            <strong>Academic Alert:</strong>

            <?php echo e($stats['at_risk_count']); ?> student(s) are currently at risk.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>

        </div>

    <?php endif; ?>


    <!-- Statistics Cards -->
    <div class="row mb-4">

        <!-- Total Students -->
        <div class="col-md-3 mb-3">

            <div class="card stat-card shadow-hover text-center">

                <div class="card-body">

                    <h5 class="card-title text-muted">
                        Students
                    </h5>

                    <h2 class="card-text fw-bold text-primary-custom">
                        <?php echo e($stats['total_students']); ?>

                    </h2>

                </div>

            </div>

        </div>


        <!-- Average GPA -->
        <div class="col-md-3 mb-3">

            <div class="card stat-card shadow-hover text-center">

                <div class="card-body">

                    <h5 class="card-title text-muted">
                        Average GPA
                    </h5>

                    <h2 class="card-text fw-bold text-primary-custom">
                        <?php echo e(number_format($stats['average_gpa'], 2)); ?>

                    </h2>

                </div>

            </div>

        </div>


        <!-- At Risk -->
        <div class="col-md-3 mb-3">

            <div class="card stat-card shadow-hover text-center">

                <div class="card-body">

                    <h5 class="card-title text-muted">
                        At Risk
                    </h5>

                    <h2 class="card-text fw-bold text-warning">
                        <?php echo e($stats['at_risk_count']); ?>

                    </h2>

                </div>

            </div>

        </div>


        <!-- Attendance -->
        <div class="col-md-3 mb-3">

            <div class="card stat-card shadow-hover text-center">

                <div class="card-body">

                    <h5 class="card-title text-muted">
                        Attendance
                    </h5>

                    <h2 class="card-text fw-bold text-success">
                        <?php echo e($stats['average_attendance']); ?>%
                    </h2>

                </div>

            </div>

        </div>

    </div>


    <!-- Charts -->
    <div class="row mb-4">

        <!-- Students by Program -->
        <div class="col-lg-4 col-md-6 mb-3">

            <div class="card shadow-hover h-100">

                <div class="card-header">

                    <h6 class="mb-0">
                        Enrolled Students by Program
                    </h6>

                </div>

                <div class="card-body">

                    <div class="chart-container">

                        <canvas
                            id="programValueChart"
                            aria-label="Enrolled students by program chart"
                            role="img"
                        ></canvas>

                    </div>

                </div>

            </div>

        </div>


        <!-- Academic Standing -->
        <div class="col-lg-4 col-md-6 mb-3">

            <div class="card shadow-hover h-100">

                <div class="card-header">

                    <h6 class="mb-0">
                        Academic Standing Distribution
                    </h6>

                </div>

                <div class="card-body">

                    <div class="chart-container">

                        <canvas
                            id="standingChart"
                            aria-label="Academic standing distribution chart"
                            role="img"
                        ></canvas>

                    </div>

                </div>

            </div>

        </div>


        <!-- Top Students -->
        <div class="col-lg-4 col-md-12 mb-3">

            <div class="card shadow-hover h-100">

                <div class="card-header">

                    <h6 class="mb-0">
                        Top Students by GPA
                    </h6>

                </div>

                <div class="card-body">

                    <div class="chart-container">

                        <canvas
                            id="topStudentsChart"
                            aria-label="Top students by GPA chart"
                            role="img"
                        ></canvas>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- Student Roster -->
    <div class="card mb-4">

        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">

            <h5 class="mb-0">
                Students List
            </h5>

            <a
                href="<?php echo e(route('students.create')); ?>"
                class="btn btn-primary btn-sm"
            >
                + Add Student
            </a>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-striped table-hover align-middle">

                    <thead>

                        <tr class="border-accent">

                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Program</th>
                            <th>Year</th>
                            <th>Units</th>
                            <th>GPA</th>
                            <th>Attendance</th>
                            <th>Status</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>

                                <td>
                                    <?php echo e($student->student_id); ?>

                                </td>

                                <td>
                                    <?php echo e($student->name); ?>

                                </td>

                                <td>
                                    <?php echo e($student->program); ?>

                                </td>

                                <td>
                                    <?php echo e($student->year_level); ?>

                                </td>

                                <td>
                                    <?php echo e($student->units); ?>

                                </td>

                                <td>
                                    <?php echo e(number_format($student->gpa, 2)); ?>

                                </td>

                                <td>
                                    <?php echo e($student->attendance_rate); ?>%
                                </td>

                                <td>

                                    <?php

                                        $badgeClass = match($student->academic_standing) {

                                            'Good Standing' => 'bg-success',

                                            'At Risk' => 'bg-warning text-dark',

                                            default => 'bg-danger',

                                        };

                                    ?>


                                    <span class="badge status-badge <?php echo e($badgeClass); ?>">

                                        <?php echo e($student->academic_standing); ?>


                                    </span>

                                </td>

                            </tr>


                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>

                                <td
                                    colspan="8"
                                    class="text-center text-muted"
                                >

                                    No students available.

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    <!-- Students by Program -->
    <div class="card mb-4">

        <div class="card-header">

            <h5 class="mb-0">
                Students by Program
            </h5>

        </div>


        <div class="card-body">

            <?php $__empty_1 = true; $__currentLoopData = $byProgram; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                <div class="d-flex justify-content-between border-bottom py-2">

                    <span>
                        <?php echo e($program); ?>

                    </span>

                    <strong>
                        <?php echo e($count); ?>

                    </strong>

                </div>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                <p class="text-muted mb-0">
                    No program data available.
                </p>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>


<?php $__env->startPush('scripts'); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<script>

    // ==========================================
    // Students by Program
    // ==========================================

    const programLabels = <?php echo json_encode($byProgram->keys(), 15, 512) ?>;

    const programData = <?php echo json_encode($byProgram->values(), 15, 512) ?>;


    new Chart(document.getElementById('programValueChart'), {

        type: 'bar',

        data: {

            labels: programLabels,

            datasets: [{

                label: 'Students',

                data: programData

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false

        }

    });


    // ==========================================
    // Academic Standing
    // ==========================================

    const standingData = {

        'Good Standing': 0,

        'At Risk': 0,

        'Probation': 0

    };


    <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

        standingData['<?php echo e($student->academic_standing); ?>']++;

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


    new Chart(document.getElementById('standingChart'), {

        type: 'doughnut',

        data: {

            labels: Object.keys(standingData),

            datasets: [{

                data: Object.values(standingData)

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false

        }

    });


    // ==========================================
    // Top Students by GPA
    // ==========================================

    const topStudentLabels = <?php echo json_encode($topStudents->pluck('name'), 15, 512) ?>;

    const topStudentGpa = <?php echo json_encode($topStudents->pluck('gpa'), 15, 512) ?>;


    new Chart(document.getElementById('topStudentsChart'), {

        type: 'bar',

        data: {

            labels: topStudentLabels,

            datasets: [{

                label: 'GPA',

                data: topStudentGpa

            }]

        },

        options: {

            indexAxis: 'y',

            responsive: true,

            maintainAspectRatio: false,

            scales: {

                x: {

                    min: 0,

                    max: 4

                }

            }

        }

    });

</script>

<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\lab5_2411600150\resources\views/dashboard.blade.php ENDPATH**/ ?>