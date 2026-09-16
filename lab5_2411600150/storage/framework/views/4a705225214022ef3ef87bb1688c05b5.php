<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Student Portal')); ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <!-- Custom Lab 4 Theme -->
    <link rel="stylesheet" href="<?php echo e(asset('css/theme.css')); ?>">

    <!-- Laravel Scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body>
    <div class="login-page min-vh-100 d-flex justify-content-center align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-6 col-lg-4">

                    <div class="card login-card shadow-lg">
                        <div class="card-header text-center py-4">
                            <h2 class="mb-1">STUDENT PORTAL</h2>
                            <p class="mb-0">Login to your dashboard</p>
                        </div>

                        <div class="card-body p-4">
                            <?php echo e($slot); ?>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>
</body>
</html><?php /**PATH C:\xampp\htdocs\lab1_2411600150\lab5_2411600150\resources\views/layouts/guest.blade.php ENDPATH**/ ?>