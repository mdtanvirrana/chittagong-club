<?php

use App\Livewire\MemberDetail;
use App\Livewire\MemberDirectory;
use App\Livewire\MemberProfile;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Member\LedgerController;
use App\Http\Controllers\Payments\SSLCommerzPaymentController;
use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\Member\MemberDirectoryController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\CommitteeController;
use App\Http\Controllers\AffiliatedClubsController;
use App\Http\Controllers\FormerChairmanController;
use App\Http\Controllers\EmployeeDirectoryController;
use App\Http\Controllers\CircularController;
use App\Http\Controllers\ClubFacilitiesController;

// ─── Guest routes (no auth needed) ──────────────────────────────────────────
Route::middleware('guest.member')->group(function () {
    Route::get('/', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.post');
});

// ─── Authenticated routes ────────────────────────────────────────────────────
Route::middleware('auth.member')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');
    Route::get('/circulars', [CircularController::class, 'index'])->name('circulars');
    Route::get('/profile', MemberProfile::class)->name('profile');
    Route::get('/notice-board', [NoticeController::class, 'index'])->name('notice-board');
    Route::get('/ledger', [LedgerController::class, 'index'])->name('ledger');
    Route::get('/ledger/data', [LedgerController::class, 'data'])->name('ledger.data');
    Route::get('/ledger/month-details', [LedgerController::class, 'monthDetails'])->name('ledger.month-details');
    Route::post('/ledger/payments/sslcommerz/initiate', [SSLCommerzPaymentController::class, 'initiate'])
        ->name('ledger.payments.sslcommerz.initiate');
    Route::get('/directory', [MemberDirectoryController::class, 'index'])->name('directory');
    Route::get('/directory/{id}', MemberDetail::class)->name('directory.show');
    Route::get('/facilities', [ClubFacilitiesController::class, 'index'])->name('facilities');
    Route::get('/shop', fn() => view('pages.club-shop'))->name('shop');
    Route::get('/executive', [CommitteeController::class, 'index'])->name('executive');
    Route::get('/contact', fn() => view('pages.contact'))->name('contact');
    Route::get('/affiliated-clubs', [AffiliatedClubsController::class, 'index'])->name(
        'affiliated-clubs'
    );
    Route::get('/former-chairman', [FormerChairmanController::class, 'index'])->name(
        'former-chairman'
    );
    Route::get('/employee-directory', [EmployeeDirectoryController::class, 'index'])->name(
        'employee-directory'
    );
    Route::get('/about',             fn() => view('pages.about'))->name('about');
    Route::get('/dress-code',        fn() => view('pages.dress-code'))->name('dress-code');
    Route::get('/general-rules',     fn() => view('pages.general-rules'))->name('general-rules');
    Route::get('/gallery',           fn() => view('pages.gallery'))->name('gallery');
});

Route::match(['get', 'post'], '/payments/sslcommerz/success', [SSLCommerzPaymentController::class, 'success'])
    ->name('payments.sslcommerz.success');
Route::match(['get', 'post'], '/payments/sslcommerz/fail', [SSLCommerzPaymentController::class, 'fail'])
    ->name('payments.sslcommerz.fail');
Route::match(['get', 'post'], '/payments/sslcommerz/cancel', [SSLCommerzPaymentController::class, 'cancel'])
    ->name('payments.sslcommerz.cancel');
Route::match(['get', 'post'], '/payments/sslcommerz/ipn', [SSLCommerzPaymentController::class, 'ipn'])
    ->name('payments.sslcommerz.ipn');
