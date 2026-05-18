<?php

use App\Http\Controllers\Api\V1\AppConfigController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClubContentController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\MemberDirectoryController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Member\LedgerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationDeviceController;
use App\Http\Controllers\Payments\SSLCommerzPaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/app/config', [AppConfigController::class, 'show'])
        ->middleware('api.cache:public,300');

    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1');
    Route::post('/auth/password/forgot/send-code', [AuthController::class, 'sendForgotPasswordCode'])
        ->middleware('throttle:6,1');
    Route::post('/auth/password/forgot/verify', [AuthController::class, 'verifyForgotPasswordCode'])
        ->middleware('throttle:10,1');
    Route::post('/auth/password/forgot/reset', [AuthController::class, 'resetForgotPassword'])
        ->middleware('throttle:10,1');
    Route::post('/auth/password/initial/send-code', [AuthController::class, 'sendInitialPasswordCode'])
        ->middleware('throttle:6,1');
    Route::post('/auth/password/initial/verify', [AuthController::class, 'verifyInitialPasswordCode'])
        ->middleware('throttle:10,1');
    Route::post('/auth/password/initial/store', [AuthController::class, 'storeInitialPassword'])
        ->middleware('throttle:10,1');

    Route::middleware('api.cache:public,300')->group(function () {
        Route::get('/legal/{page}', [ClubContentController::class, 'legalPage']);
        Route::get('/contact', [ClubContentController::class, 'contact']);
        Route::get('/facilities', [ClubContentController::class, 'facilities']);
        Route::get('/club-info/{page}', [ClubContentController::class, 'clubInfoPage']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);

        Route::middleware('api.cache:private,60')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'show']);
            Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
            Route::get('/profile', [ProfileController::class, 'show']);
        });
        Route::post('/profile/password', [ProfileController::class, 'updatePassword']);

        Route::middleware('api.cache:private,20')->group(function () {
            Route::get('/ledger', [LedgerController::class, 'data']);
            Route::get('/ledger/summary', [LedgerController::class, 'summary']);
            Route::get('/ledger/insights', [LedgerController::class, 'insights']);
            Route::get('/ledger/history', [LedgerController::class, 'history']);
            Route::get('/ledger/payments', [LedgerController::class, 'payments']);
            Route::get('/ledger/month-details', [LedgerController::class, 'monthDetails']);
        });

        Route::middleware('api.cache:private,300')->group(function () {
            Route::get('/directory', [MemberDirectoryController::class, 'index']);
            Route::get('/directory/{id}', [MemberDirectoryController::class, 'show']);
            Route::get('/gallery', [ClubContentController::class, 'gallery']);
            Route::get('/gallery/{album}/photos', [ClubContentController::class, 'galleryPhotos']);
            Route::get('/committee', [ClubContentController::class, 'committee']);
            Route::get('/former-chairmen', [ClubContentController::class, 'formerChairmen']);
            Route::get('/employees', [ClubContentController::class, 'employees']);
            Route::get('/affiliated-clubs', [ClubContentController::class, 'affiliatedClubs']);
            Route::get('/circulars', [ClubContentController::class, 'circulars']);
            Route::get('/notices', [ClubContentController::class, 'notices']);
        });

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/devices', [NotificationDeviceController::class, 'store']);
        Route::delete('/notifications/devices', [NotificationDeviceController::class, 'destroy']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);

        Route::post('/payments/sslcommerz/initiate', [SSLCommerzPaymentController::class, 'initiate']);
        Route::get('/payments/sslcommerz/{transactionId}', [SSLCommerzPaymentController::class, 'show']);
    });
});
