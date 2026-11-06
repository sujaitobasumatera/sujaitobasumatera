<?php

// Auto-restore .env file from tracked .env.sujai if it gets deleted by Rumah Web/GitHub deployments
if (! file_exists(__DIR__.'/../.env')) {
    if (file_exists(__DIR__.'/../.env.sujai')) {
        @copy(__DIR__.'/../.env.sujai', __DIR__.'/../.env');
    }
}

// Auto-create essential storage subdirectories if they are missing (prevents 'valid cache path' errors on cPanel/Rumah Web)
$storagePaths = [
    __DIR__.'/../storage/framework/views',
    __DIR__.'/../storage/framework/cache/data',
    __DIR__.'/../storage/framework/sessions',
    __DIR__.'/../storage/logs',
    __DIR__.'/../storage/app/public',
    // Public 'public' disk root — uploads are written here and served directly at /storage
    __DIR__.'/../public/storage',
];
foreach ($storagePaths as $path) {
    if (! file_exists($path)) {
        @mkdir($path, 0755, true);
    }
}

use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\LocaleCurrencyMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            LocaleCurrencyMiddleware::class,
        ]);
        $middleware->append(CheckMaintenanceMode::class);
        $middleware->append(SecurityHeaders::class);
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
