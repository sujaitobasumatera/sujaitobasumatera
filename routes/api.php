<?php

use App\Http\Controllers\Api\PublicApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/auth/login', [PublicApiController::class, 'login']);
    Route::get('/blogs', [PublicApiController::class, 'getBlogs']);
    Route::get('/packages', [PublicApiController::class, 'getPackages']);
    Route::get('/outbound/services', [PublicApiController::class, 'getOutboundServices']);
    Route::post('/bookings', [PublicApiController::class, 'submitBooking'])->middleware('throttle:5,1');
    Route::get('/clients', [PublicApiController::class, 'getClients']);
    Route::get('/gallery', [PublicApiController::class, 'getGallery']);
    Route::get('/cities', [PublicApiController::class, 'getCities']);
    Route::get('/package-tiers', [PublicApiController::class, 'getPackageTiers']);
    Route::get('/stats', [PublicApiController::class, 'getStats']);
});

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Any authenticated token may read its own identity.
    Route::get('/auth/me', [PublicApiController::class, 'getMe']);

    // SECURITY: dashboard revenue, customer PII, and the settings blob are admin-only.
    // auth:sanctum alone let any 'user'-role token read them — add the same role boundary
    // the web admin panel uses so a non-admin token is rejected (403).
    Route::middleware('role:superadmin,admin_umum,admin_tour')->group(function () {
        Route::get('/dashboard', [PublicApiController::class, 'getDashboard']);
        // Behind auth+role: customer PII (names, prices, dates).
        Route::get('/bookings', [PublicApiController::class, 'getBookings']);
        Route::get('/settings', [PublicApiController::class, 'getSettings']);
    });
});
