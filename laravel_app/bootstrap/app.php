<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            // Pembatasan role: contoh ->middleware('role:owner') atau ->middleware('role:admin,owner')
            'role'           => \App\Http\Middleware\EnsureUserRole::class,
            // Blokir write operation (POST/PUT/PATCH/DELETE) untuk akun Owner (read-only)
            'owner.readonly' => \App\Http\Middleware\ReadOnlyOwner::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
