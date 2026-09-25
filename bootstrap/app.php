<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The SPA authenticates with Sanctum's cookie session rather than a
        // bearer token, so a token never has to live in localStorage.
        $middleware->statefulApi();

        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'workspace' => \App\Http\Middleware\ResolveWorkspace::class,
            'capability' => \App\Http\Middleware\RequiresCapability::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
