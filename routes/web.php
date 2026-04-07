<?php

use App\Livewire\MemberDetail;
use App\Livewire\MemberDirectory;
use App\Livewire\MemberProfile;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Member\LedgerController;
use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\Member\MemberDirectoryController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\CommitteeController;
use App\Http\Controllers\AffiliatedClubsController;
use App\Http\Controllers\FormerChairmanController;
use App\Http\Controllers\EmployeeDirectoryController;

// ─── Guest routes (no auth needed) ──────────────────────────────────────────
Route::middleware('guest.member')->group(function () {
    Route::get('/', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.post');
});

// ─── Authenticated routes ────────────────────────────────────────────────────
//Route::middleware('auth.member')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', MemberProfile::class)->name('profile');
    Route::get('/notice-board', [NoticeController::class, 'index'])->name('notice-board');
    Route::get('/ledger', [LedgerController::class, 'index'])->name('ledger');
    Route::get('/directory', [MemberDirectoryController::class, 'index'])->name('directory');
    Route::get('/directory/{id}', MemberDetail::class)->name('directory.show');
    Route::get('/facilities', fn() => view('pages.club-facilities'))->name('facilities');
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
//});
