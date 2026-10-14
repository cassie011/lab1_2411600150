<?php

use App\Http\Controllers\Api\AcademicApiController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    // Admin / Teacher: dashboard. Student: own grades and attendance.
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Academic alerts (bell icon) - all roles
    Route::get('notifications/{id}/read', [NotificationController::class, 'read'])
        ->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])
        ->name('notifications.readAll');

    // ==========================================
    // Admin + Teacher only
    // ==========================================
    Route::middleware('can:manage-records')->group(function () {
        Route::resource('students', StudentController::class);

        // Lab 6: Grades (delete is admin only)
        Route::resource('grades', GradeController::class)->except('destroy');
        Route::delete('grades/{grade}', [GradeController::class, 'destroy'])
            ->middleware('can:admin')
            ->name('grades.destroy');

        // Lab 6: Attendance (take attendance for a whole class)
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::delete('attendance/{attendance}', [AttendanceController::class, 'destroy'])
            ->middleware('can:admin')
            ->name('attendance.destroy');

        // Lab 6: Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

        // Lab 6: JSON API for real-time dashboard polling (uses the login session)
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('dashboard/stats', [AcademicApiController::class, 'stats'])
                ->name('dashboard.stats');
            Route::get('students/at-risk', [AcademicApiController::class, 'atRisk'])
                ->name('students.atRisk');
            Route::get('activity/recent', [AcademicApiController::class, 'recentActivity'])
                ->name('activity.recent');
            Route::get('students/{student}/summary', [AcademicApiController::class, 'studentSummary'])
                ->name('students.summary');
        });
    });
});

require __DIR__ . '/auth.php';
