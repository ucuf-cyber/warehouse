<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Vercel terminates TLS at its edge and forwards to the function over
        // plain HTTP, so Laravel only learns the request was HTTPS via the
        // X-Forwarded-Proto header. Without trusting the proxy, url()/asset()
        // generate http:// links, which the browser blocks as mixed content
        // on an https:// page. The app is never reachable except through
        // Vercel's edge, so trusting all proxies here is safe.
        $middleware->trustProxies(at: '*');

        $middleware->statefulApi();

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return \App\Exceptions\ApiExceptionRenderer::render($e, $request);
            }

            // Non-API requests fall through to the default handler, which
            // picks up resources/views/errors/{status}.blade.php automatically.
            return null;
        });
    })->create();
