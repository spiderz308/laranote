<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
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

// Detect Vercel environment and use writable /tmp/storage
$storagePath = env('APP_STORAGE');
if ($storagePath || isset($_SERVER['LAMBDA_TASK_ROOT']) || isset($_SERVER['VERCEL'])) {
    $storagePath = $storagePath ?? '/tmp/storage';
    
    @mkdir($storagePath . '/logs', 0777, true);
    @mkdir($storagePath . '/framework/views', 0777, true);
    @mkdir($storagePath . '/framework/cache', 0777, true);
    @mkdir($storagePath . '/framework/sessions', 0777, true);
    
    $app->useStoragePath($storagePath);
}

return $app;
