<?php

use Illuminate\Support\Facades\Route;
use Modules\User\App\Http\Controllers\Auth\ConfirmPasswordController;
use Modules\User\App\Http\Controllers\Auth\ForgotPasswordController;
use Modules\User\App\Http\Controllers\Auth\LoginController;
use Modules\User\App\Http\Controllers\Auth\OtpVerificationController;
use Modules\User\App\Http\Controllers\Auth\PasswordController;
use Modules\User\App\Http\Controllers\Auth\RegisterController;
use Modules\User\App\Http\Controllers\Auth\ResetPasswordController;
use Modules\User\App\Http\Controllers\ProfileController;

Route::middleware('web')->group(function () {
    Route::get('/u/{username}', [ProfileController::class, 'show'])
        ->name('profile.show');

    Route::middleware('guest')->group(function () {
        Route::get('/register', [RegisterController::class, 'create'])->name('register');
        Route::post('/register', [RegisterController::class, 'store']);

        Route::get('/login', [LoginController::class, 'create'])->name('login');
        Route::post('/login', [LoginController::class, 'store']);

        Route::get('/verify-otp', [OtpVerificationController::class, 'show'])->name('otp.verify');
        Route::post('/verify-otp', [OtpVerificationController::class, 'verify']);
        Route::post('/resend-otp', [OtpVerificationController::class, 'resend'])->name('otp.resend')->middleware('throttle:3,60');

        Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
        Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');

        Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
        Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.store');

    });

    Route::middleware(['auth'])->group(function () {
        Route::get('/logout/confirm',
            function () {
                return view('user::auth.logout-confirm');
            }
        )->name('logout.confirm');

        Route::post('/logout', [
            LoginController::class,
            'destroy',
        ])->name('logout');
    });

    Route::middleware(['auth', 'check.verified'])->group(function () {
        Route::get('/confirm-password', [ConfirmPasswordController::class, 'show'])->name('password.confirm');
        Route::post('/confirm-password', [ConfirmPasswordController::class, 'store']);
        Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
    });

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/profile/edit', [ProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::post('/profile/update', [ProfileController::class, 'update'])
            ->name('profile.update');

        Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])
            ->name('profile.avatar.upload');

        Route::post('/profile/cover', [ProfileController::class, 'uploadCover'])
            ->name('profile.cover.upload');

        Route::post('/profile/social', [ProfileController::class, 'updateSocialLinks'])
            ->name('profile.social.update');

        Route::get('/profile', function () {
            return redirect()->route('profile.show', [
                'username' => auth()->user()->ensureUsername(),
            ]);
        })->name('profile.own');
    });
});
