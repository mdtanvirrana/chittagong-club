<?php

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

test('sslcommerz callback routes bypass session and csrf middleware', function () {
    foreach ([
        'payments.sslcommerz.success',
        'payments.sslcommerz.fail',
        'payments.sslcommerz.cancel',
        'payments.sslcommerz.ipn',
    ] as $routeName) {
        $route = app('router')->getRoutes()->getByName($routeName);

        expect($route)->not->toBeNull()
            ->and($route->gatherMiddleware())->not->toContain(
                StartSession::class,
                ShareErrorsFromSession::class,
                ValidateCsrfToken::class,
            );
    }
});
