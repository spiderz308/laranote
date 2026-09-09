<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Add this line to dynamically detect Vercel's environment context
if (isset($_SERVER['LAMBDA_TASK_ROOT'])) {
    $storagePath = '/tmp/storage';
    
    // Create required directories inside the writable /tmp block if they don't exist
    @mkdir($storagePath . '/logs', 0777, true);
    @mkdir($storagePath . '/framework/views', 0777, true);
    @mkdir($storagePath . '/framework/cache', 0777, true);
    @mkdir($storagePath . '/framework/sessions', 0777, true);
    
    // Bind the new path globally to the engine framework
    app()->useStoragePath($storagePath);
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
