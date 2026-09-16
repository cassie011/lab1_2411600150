

<?php $__env->startSection('title', 'Add Student'); ?>

<?php $__env->startSection('content'); ?>

<h2>Add Student</h2>

<form
    method="POST"
    action="<?php echo e(route('students.store')); ?>"
    class="bg-white p-4 rounded shadow-sm"
>
    <?php echo csrf_field(); ?>

    <?php echo $__env->make('students._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <button class="btn btn-primary mt-3">
        Save Student
    </button>
</form>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\lab1_2411600150\lab5_2411600150\resources\views/students/create.blade.php ENDPATH**/ ?>