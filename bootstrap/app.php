<?php

use App\Http\Middleware\AuthenticateReviewer;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\WidgetCors;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->redirectUsersTo('/app');

        // The widget API resolves its site and reviewer first: the CORS answer and the
        // rate limit keys depend on them.
        $middleware->prependToPriorityList(before: ThrottleRequests::class, prepend: WidgetCors::class);
        $middleware->prependToPriorityList(before: ThrottleRequests::class, prepend: AuthenticateReviewer::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
