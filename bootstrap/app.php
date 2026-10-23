<?php

use Illuminate\Auth\Middleware\Authenticate;
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
        // This is an API-only app. Never redirect unauthenticated requests to a
        // web "login" route (which does not exist here). Returning null from the
        // Authenticate middleware's redirect hook makes it throw a plain
        // AuthenticationException, which is rendered as a JSON 401 response by
        // the shouldRenderJsonWhen() handler below.
        Authenticate::redirectUsing(fn (Request $request) => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
        return $request->is('api/*') || $request->expectsJson();
    });
})->create();
