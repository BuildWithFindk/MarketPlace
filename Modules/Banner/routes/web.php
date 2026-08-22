<?php

use Illuminate\Support\Facades\Route;
use Modules\Banner\App\Http\Controllers\AdRequestController;
use Modules\Banner\App\Http\Controllers\BannerController;

Route::middleware('web')->group(function () {
    Route::get('/banners/{banner}/click', [BannerController::class, 'redirect'])
        ->name('banners.redirect');

    Route::middleware(['auth', 'check.verified'])
        ->prefix('advertise')
        ->name('advertise.')
        ->group(function () {
            Route::get('/', [AdRequestController::class, 'create'])->name('create');
            Route::post('/', [AdRequestController::class, 'store'])->name('store');
            Route::get('/mine', [AdRequestController::class, 'myRequests'])->name('mine');
        });
});
