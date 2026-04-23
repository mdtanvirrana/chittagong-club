<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\CircularController as AdminCircularController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\NoticeController as AdminNoticeController;
use App\Http\Controllers\Admin\PictureUploadController as AdminPictureUploadController;
use App\Http\Controllers\AffiliatedClubsController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CircularController;
use App\Http\Controllers\ClubFacilitiesController;
use App\Http\Controllers\CommitteeController;
use App\Http\Controllers\EmployeeDirectoryController;
use App\Http\Controllers\FormerChairmanController;
use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\Member\LedgerController;
use App\Http\Controllers\Member\MemberDetailController;
use App\Http\Controllers\Member\MemberDirectoryController;
use App\Http\Controllers\Member\MemberProfileController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\Payments\SSLCommerzPaymentController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

// ─── Guest routes (no auth needed) ──────────────────────────────────────────
Route::middleware('guest.member')->group(function () {
    Route::get('/', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.post');
    Route::get('/password/setup', [AuthController::class, 'showInitialPasswordSetup'])->name('password.initial.create');
    Route::post('/password/setup', [AuthController::class, 'storeInitialPassword'])->name('password.initial.store');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.forgot');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendCode'])->name('password.forgot.send');
    Route::get('/forgot-password/otp', [ForgotPasswordController::class, 'showVerify'])->name('password.forgot.verify');
    Route::post('/forgot-password/otp', [ForgotPasswordController::class, 'verifyCode'])->name('password.forgot.verify.store');
    Route::post('/forgot-password/otp/resend', [ForgotPasswordController::class, 'resendCode'])->name('password.forgot.verify.resend');
    Route::get('/forgot-password/reset', [ForgotPasswordController::class, 'showReset'])->name('password.forgot.reset');
    Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'updatePassword'])->name('password.forgot.update');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest.admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth.admin')->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
        Route::get('/pictures/upload', [AdminPictureUploadController::class, 'create'])->name('pictures.create');
        Route::get('/pictures', [AdminPictureUploadController::class, 'index'])->name('pictures.index');
        Route::post('/pictures', [AdminPictureUploadController::class, 'store'])->name('pictures.store');
        Route::delete('/pictures', [AdminPictureUploadController::class, 'destroy'])->name('pictures.destroy');

        Route::get('/notices', [AdminNoticeController::class, 'index'])->name('notices.index');
        Route::get('/notices/create', [AdminNoticeController::class, 'create'])->name('notices.create');
        Route::post('/notices', [AdminNoticeController::class, 'store'])->name('notices.store');
        Route::get('/notices/{notice}/edit', [AdminNoticeController::class, 'edit'])->name('notices.edit');
        Route::put('/notices/{notice}', [AdminNoticeController::class, 'update'])->name('notices.update');
        Route::patch('/notices/{notice}/online', [AdminNoticeController::class, 'toggleOnline'])->name('notices.online');
        Route::patch('/notices/{notice}/active', [AdminNoticeController::class, 'toggleActive'])->name('notices.active');

        Route::get('/contacts', [AdminContactController::class, 'index'])->name('contacts.index');
        Route::get('/contacts/create', [AdminContactController::class, 'create'])->name('contacts.create');
        Route::post('/contacts', [AdminContactController::class, 'store'])->name('contacts.store');
        Route::get('/contacts/{contact}/edit', [AdminContactController::class, 'edit'])->name('contacts.edit');
        Route::put('/contacts/{contact}', [AdminContactController::class, 'update'])->name('contacts.update');
        Route::delete('/contacts/{contact}', [AdminContactController::class, 'destroy'])->name('contacts.destroy');

        Route::get('/circulars', [AdminCircularController::class, 'index'])->name('circulars.index');
        Route::get('/circulars/create', [AdminCircularController::class, 'create'])->name('circulars.create');
        Route::post('/circulars', [AdminCircularController::class, 'store'])->name('circulars.store');
        Route::get('/circulars/{circular}/edit', [AdminCircularController::class, 'edit'])->name('circulars.edit');
        Route::put('/circulars/{circular}', [AdminCircularController::class, 'update'])->name('circulars.update');
        Route::patch('/circulars/{circular}/online', [AdminCircularController::class, 'toggleOnline'])->name('circulars.online');
        Route::patch('/circulars/{circular}/active', [AdminCircularController::class, 'toggleActive'])->name('circulars.active');
    });
});

// ─── Authenticated routes ────────────────────────────────────────────────────
Route::middleware('auth.member')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');
    Route::get('/circulars', [CircularController::class, 'index'])->name('circulars');
    Route::get('/profile', [MemberProfileController::class, 'index'])->name('profile');
    Route::post('/profile/password', [MemberProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/notice-board', [NoticeController::class, 'index'])->name('notice-board');
    Route::get('/ledger', [LedgerController::class, 'index'])->name('ledger');
    Route::get('/ledger/data', [LedgerController::class, 'data'])->name('ledger.data');
    Route::get('/ledger/month-details', [LedgerController::class, 'monthDetails'])->name('ledger.month-details');
    Route::post('/ledger/payments/sslcommerz/initiate', [SSLCommerzPaymentController::class, 'initiate'])
        ->name('ledger.payments.sslcommerz.initiate');
    Route::get('/directory', [MemberDirectoryController::class, 'index'])->name('directory');
    Route::get('/directory/{id}', [MemberDetailController::class, 'show'])->name('directory.show');
    Route::get('/facilities', [ClubFacilitiesController::class, 'index'])->name('facilities');
    Route::get('/shop', fn () => view('pages.club-shop'))->name('shop');
    Route::get('/executive', [CommitteeController::class, 'index'])->name('executive');
    Route::get('/contact', [ContactController::class, 'index'])->name('contact');
    Route::get('/affiliated-clubs', [AffiliatedClubsController::class, 'index'])->name(
        'affiliated-clubs'
    );
    Route::get('/former-chairman', [FormerChairmanController::class, 'index'])->name(
        'former-chairman'
    );
    Route::get('/employee-directory', [EmployeeDirectoryController::class, 'index'])->name(
        'employee-directory'
    );
    Route::get('/about', fn () => view('pages.about'))->name('about');
    Route::get('/dress-code', fn () => view('pages.dress-code'))->name('dress-code');
    Route::get('/general-rules', fn () => view('pages.general-rules'))->name('general-rules');
    Route::get('/gallery', fn () => view('pages.gallery'))->name('gallery');
});

Route::controller(SSLCommerzPaymentController::class)
    ->withoutMiddleware([
        StartSession::class,
        ShareErrorsFromSession::class,
        ValidateCsrfToken::class,
    ])
    ->group(function () {
        Route::match(['get', 'post'], '/payments/sslcommerz/success', 'success')
            ->name('payments.sslcommerz.success');
        Route::match(['get', 'post'], '/payments/sslcommerz/fail', 'fail')
            ->name('payments.sslcommerz.fail');
        Route::match(['get', 'post'], '/payments/sslcommerz/cancel', 'cancel')
            ->name('payments.sslcommerz.cancel');
        Route::match(['get', 'post'], '/payments/sslcommerz/ipn', 'ipn')
            ->name('payments.sslcommerz.ipn');
    });
