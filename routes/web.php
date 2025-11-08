<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\TwoFactorAuthenticationController;
use App\Http\Controllers\BrowserSessionsController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('/settings', SettingController::class)->names('settings');
});

// Two-Factor Authentication
Route::middleware(['auth'])->group(function () {
    // Custom 2FA routes with unique names to avoid conflicts with Fortify
    Route::get('/two-factor-authentication', [TwoFactorAuthenticationController::class, 'show'])->name('profile.two-factor.show');
    Route::post('/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])->name('profile.two-factor.enable');
    Route::post('/two-factor-authentication/confirm', [TwoFactorAuthenticationController::class, 'confirm'])->name('profile.two-factor.confirm');
    Route::delete('/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])->name('profile.two-factor.disable');
    
    // Recovery codes management
    Route::get('/two-factor-recovery-codes', [TwoFactorAuthenticationController::class, 'showRecoveryCodes'])->name('profile.two-factor.recovery-codes');
    Route::get('/two-factor-recovery-codes/confirm', [TwoFactorAuthenticationController::class, 'showRegenerateRecoveryCodes'])->name('profile.two-factor.recovery-codes.confirm');
    Route::post('/two-factor-recovery-codes', [TwoFactorAuthenticationController::class, 'regenerateRecoveryCodes'])->name('profile.two-factor.regenerate-recovery-codes');
});

// Browser Sessions Management
Route::middleware(['auth'])->group(function () {
    Route::delete('/other-browser-sessions', [BrowserSessionsController::class, 'destroy'])->name('profile.other-browser-sessions.destroy');
});

// Two-Factor Authentication Challenge
Route::middleware(['guest'])->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'create'])->name('two-factor.login');
    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])->name('two-factor.verify');
    Route::post('/two-factor-recovery', [TwoFactorChallengeController::class, 'recover'])->name('two-factor.recovery');
});

require __DIR__.'/auth.php';
