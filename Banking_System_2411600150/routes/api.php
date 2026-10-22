<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// URL becomes /api/dashboard/stats (Laravel adds the "api" prefix).
// 'web' gives us the login session, 'auth' makes sure the user is logged in.
Route::middleware(['web', 'auth'])
    ->get('/dashboard/stats', [DashboardController::class, 'stats'])
    ->name('api.dashboard.stats');
