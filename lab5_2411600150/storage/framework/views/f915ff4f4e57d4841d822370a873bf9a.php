<?php if(!isset($student)): ?>

    <div class="mb-3">
        <label class="form-label">Student ID</label>

        <input
            type="text"
            name="student_id"
            class="form-control"
            value="<?php echo e(old('student_id')); ?>"
            required
        >

        <?php $__errorArgs = ['student_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="text-danger"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

<?php endif; ?>

<div class="mb-3">
    <label class="form-label">Name</label>

    <input
        type="text"
        name="name"
        class="form-control"
        value="<?php echo e(old('name', $student->name ?? '')); ?>"
        required
    >

    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <div class="text-danger"><?php echo e($message); ?></div>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>

<div class="mb-3">
    <label class="form-label">Program</label>

    <input
        type="text"
        name="program"
        class="form-control"
        value="<?php echo e(old('program', $student->program ?? '')); ?>"
        required
    >

    <?php $__errorArgs = ['program'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <div class="text-danger"><?php echo e($message); ?></div>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
            value="<?php echo e(old('year_level', $student->year_level ?? 1)); ?>"
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
            value="<?php echo e(old('units', $student->units ?? 0)); ?>"
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
            value="<?php echo e(old('gpa', $student->gpa ?? '')); ?>"
            required
        >

        <?php $__errorArgs = ['gpa'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="text-danger"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="col mb-3">
        <label class="form-label">Attendance %</label>

        <input
            type="number"
            name="attendance_rate"
            class="form-control"
            min="0"
            max="100"
            value="<?php echo e(old('attendance_rate', $student->attendance_rate ?? '')); ?>"
            required
        >
    </div>

</div><?php /**PATH C:\xampp\htdocs\lab1_2411600150\lab5_2411600150\resources\views/students/_form.blade.php ENDPATH**/ ?>