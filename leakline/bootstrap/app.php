<?php

use App\Http\Middleware\RoleMiddleware;
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
        // Where to redirect AUTHENTICATED users
        $middleware->redirectUsersTo(function () {
            return route('dashboard');
        });

        // Where to redirect GUEST users
        $middleware->redirectGuestsTo(function () {
            return route('login');
        });

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'setLocale' =>\App\Http\Middleware\SetLocale::class,
        ]);
        // Set application locale from session (i18n)
        $middleware->appendToGroup('web', \App\Http\Middleware\SetLocale::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
