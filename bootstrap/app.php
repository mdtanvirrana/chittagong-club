<?php

use App\Http\Middleware\CheckAdminAuth;
use App\Http\Middleware\CheckMemberAuth;
use App\Http\Middleware\RedirectIfAdminAuthenticated;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.member' => CheckMemberAuth::class,
            'guest.member' => RedirectIfAuthenticated::class,
            'auth.admin' => CheckAdminAuth::class,
            'guest.admin' => RedirectIfAdminAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : null;

            if ($statusCode === 419) {
                $isAdminRoute = $request->routeIs('admin.*') || $request->is('admin') || $request->is('admin/*');

                return redirect()
                    ->route($isAdminRoute ? 'admin.login' : 'login')
                    ->withInput($request->only($isAdminRoute ? 'login' : 'member_id'))
                    ->with('session_expired', 'Please sign in again.');
            }

            return null;
        });
    })->create();
