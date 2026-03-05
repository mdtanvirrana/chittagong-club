<?php

use App\Livewire\MemberDetail;
use App\Livewire\MemberDirectory;
use App\Livewire\MemberProfile;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

// ─── Guest routes (no auth needed) ──────────────────────────────────────────
Route::middleware('guest.member')->group(function () {
    Route::get('/',       [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.post');
});

// ─── Authenticated routes ────────────────────────────────────────────────────
Route::middleware('auth.member')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard',    fn() => view('pages.dashboard'))->name('dashboard');
    Route::get('/profile', MemberProfile::class)->name('profile');
    Route::get('/notice-board', fn() => view('pages.notice-board'))->name('notice-board');
    Route::get('/ledger',       fn() => view('pages.ledger'))->name('ledger');
    Route::get('/directory',         MemberDirectory::class)->name('directory');
    Route::get('/directory/{id}',    MemberDetail::class)->name('directory.show');
    Route::get('/facilities',   fn() => view('pages.club-facilities'))->name('facilities');
    Route::get('/shop',         fn() => view('pages.club-shop'))->name('shop');
    Route::get('/executive',    fn() => view('pages.executive-committee'))->name('executive');
    Route::get('/contact',      fn() => view('pages.contact'))->name('contact');
});
