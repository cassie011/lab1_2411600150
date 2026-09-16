

<?php $__env->startSection('title', 'Students'); ?>

<?php $__env->startSection('content'); ?>

<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="mb-1">
                Students
            </h2>

            <p class="text-muted mb-0">
                Manage and search student records
            </p>
        </div>

        <a
            href="<?php echo e(route('students.create')); ?>"
            class="btn btn-primary"
        >
            <i class="bi bi-person-plus me-1"></i>
            Add Student
        </a>

    </div>


    <!-- Student Filters -->
    <div class="card shadow-hover mb-4">

        <div class="card-header">

            <h5 class="mb-0">
                <i class="bi bi-funnel me-2"></i>
                Student Filters
            </h5>

        </div>

        <div class="card-body">

            <form
                method="GET"
                action="<?php echo e(route('students.index')); ?>"
            >

                <div class="row g-3 align-items-end">

                    <!-- Program -->
                    <div class="col-md-4">

                        <label
                            for="program"
                            class="form-label fw-semibold"
                        >
                            Program
                        </label>

                        <select
                            name="program"
                            id="program"
                            class="form-select"
                        >

                            <option value="">
                                All Programs
                            </option>

                            <?php $__currentLoopData = $programs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <option
                                    value="<?php echo e($program); ?>"
                                    <?php echo e(request('program') == $program ? 'selected' : ''); ?>

                                >
                                    <?php echo e($program); ?>

                                </option>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </select>

                    </div>


                    <!-- Search -->
                    <div class="col-md-5">

                        <label
                            for="search"
                            class="form-label fw-semibold"
                        >
                            Search Student
                        </label>

                        <input
                            type="text"
                            name="search"
                            id="search"
                            class="form-control"
                            placeholder="Search by Student ID or Name"
                            value="<?php echo e(request('search')); ?>"
                        >

                    </div>


                    <!-- Buttons -->
                    <div class="col-md-3">

                        <div class="d-flex gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary flex-grow-1"
                            >
                                <i class="bi bi-search me-1"></i>
                                Apply
                            </button>

                            <a
                                href="<?php echo e(route('students.index')); ?>"
                                class="btn btn-outline-secondary"
                                title="Reset Filters"
                            >
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- Student Table -->
    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                Student List
            </h5>

            <span class="badge bg-secondary">
                <?php echo e($students->count()); ?> student(s)
            </span>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-striped table-hover align-middle">

                    <thead>

                        <tr>

                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Program</th>
                            <th>Year</th>
                            <th>Units</th>
                            <th>GPA</th>
                            <th>Attendance</th>
                            <th>Status</th>
                            <th>Actions</th>

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
                                        $badgeClass = match ($student->academic_standing) {
                                            'Good Standing' => 'bg-success',
                                            'At Risk' => 'bg-warning text-dark',
                                            default => 'bg-danger',
                                        };
                                    ?>

                                    <span class="badge status-badge <?php echo e($badgeClass); ?>">
                                        <?php echo e($student->academic_standing); ?>

                                    </span>

                                </td>


                                <!-- Actions -->
                                <td>

                                    <div class="d-flex gap-1">

                                        <a
                                            href="<?php echo e(route('students.show', $student)); ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="View"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a
                                            href="<?php echo e(route('students.edit', $student)); ?>"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Edit"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form
                                            method="POST"
                                            action="<?php echo e(route('students.destroy', $student)); ?>"
                                            onsubmit="return confirm('Are you sure you want to delete this student?');"
                                        >
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>

                                <td
                                    colspan="9"
                                    class="text-center text-muted py-4"
                                >

                                    <i class="bi bi-search fs-3 d-block mb-2"></i>

                                    No students found matching your filters.

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\lab1_2411600150\lab5_2411600150\resources\views/students/index.blade.php ENDPATH**/ ?>