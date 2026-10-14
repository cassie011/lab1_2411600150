<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pagination links use Bootstrap 5 (matches the portal layout)
        Paginator::useBootstrapFive();

        // Admin only: delete grades and attendance records
        Gate::define('admin', fn (User $user) => $user->isAdmin());

        // Admin + Teacher: manage students, grades, attendance, reports
        Gate::define('manage-records', fn (User $user) => $user->canManageRecords());

        // Student: view own grades and attendance only
        Gate::define('student', fn (User $user) => $user->isStudent());

        // Grade and AttendanceRecord observers are registered on the models
        // with the #[ObservedBy] attribute (Laravel 10+).
    }
}
