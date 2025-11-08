<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Override PHP upload limits if possible (only works for some settings)
@ini_set('max_execution_time', '300');
@ini_set('memory_limit', '512M');
// Note: upload_max_filesize and post_max_size cannot be changed via ini_set()
// They must be set in php.ini or .htaccess

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt.auth' => \App\Http\Middleware\JwtAuthentication::class,
            'force.https' => \App\Http\Middleware\ForceHttps::class,
            'api.version' => \App\Http\Middleware\ApiVersionCheck::class,
            'admin.only' => \App\Http\Middleware\AdminOnly::class,
        ]);

        // Force HTTPS for all routes in production
        $middleware->web(append: [
            \App\Http\Middleware\ForceHttps::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\ForceHttps::class,
            \App\Http\Middleware\ApiVersionCheck::class,
        ]);

        // Disable ValidatePathEncoding middleware that causes issues
        $middleware->remove(\Illuminate\Http\Middleware\ValidatePathEncoding::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
