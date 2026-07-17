<?php

use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrackWebVisit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
        $middleware->web(append: [TrackWebVisit::class]);
        $middleware->trustHosts(
            at: fn () => array_filter([
                ($host = parse_url((string) config('app.url'), PHP_URL_HOST))
                    ? '^'.preg_quote($host, '/').'$'
                    : null,
            ]),
            subdomains: false,
        );
        // Route admin memiliki concealment 404 sendiri; fallback auth umum kembali ke homepage.
        $middleware->redirectGuestsTo(fn () => route('home'));
        // Admin yang sudah login diarahkan ke dashboard bila membuka halaman guest.
        $middleware->redirectUsersTo('/admin/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
