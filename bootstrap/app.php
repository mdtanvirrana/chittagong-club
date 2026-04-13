<?php

use App\Http\Middleware\{CheckMemberAuth, RedirectIfAuthenticated};
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.member' => CheckMemberAuth::class,
            'guest.member' => RedirectIfAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : null;

            if ($statusCode === 419) {
                return redirect()
                    ->route('login')
                    ->withInput($request->only('member_id'))
                    ->with('session_expired', 'Please sign in again.');
            }

            return null;
        });
    })->create();
