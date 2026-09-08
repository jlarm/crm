<?php

declare(strict_types=1);

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RecordLastLogin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (): string => route('login'));

        /*
         * The application runs behind Cloudflare and nginx, which terminate TLS
         * and forward over HTTP. Without trusting the forwarded headers Laravel
         * sees the request as http:// while signed URLs were generated as
         * https://, so signature validation fails.
         *
         * That breaks every unsubscribe link and every tracked click with a 403
         * — silently, and in a way that pushes recipients to report spam rather
         * than opt out.
         */
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            HandleInertiaRequests::class,
            /**
             * Capped deliberately. This middleware emits one "Link" header listing every
             * preloaded Vite asset, and an unbounded list overflows nginx's fastcgi
             * buffers on the origin, which answers with a 502 instead of the page.
             */
            AddLinkHeadersForPreloadedAssets::using(5),
            RecordLastLogin::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
            // RFC 8058 one-click unsubscribe is POSTed by the mailbox provider,
            // which has no session or token. The route is signed instead.
            'unsubscribe/*',
        ]);

        $middleware->alias([
            'auth' => Authenticate::class,
            'role' => Spatie\Permission\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->report(function (Throwable $e): void {
            ErrorTracker::capture($e, request());
        });
    })
    ->create();
