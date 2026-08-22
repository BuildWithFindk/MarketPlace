<?php

use Illuminate\Support\Facades\Route;
use Modules\Listing\Http\Controllers\ListingController;

Route::middleware('web')->group(function () {

    // ═══════════════════════════════════
    // Public Routes (no auth needed)
    // ═══════════════════════════════════
    Route::prefix('listings')->name('listings.')
        ->group(function () {

            // Browse all listings
            Route::get('/', [ListingController::class, 'index'])
                ->name('index');

            // Show single listing
            Route::get('/{listing}',
                [ListingController::class, 'show'])
                ->name('show');
        });

    // ═══════════════════════════════════
    // Auth Routes (require login)
    // ═══════════════════════════════════
    Route::middleware(['auth', 'check.verified'])
        ->prefix('listings')
        ->name('listings.')
        ->group(function () {

            // Create listing form
            Route::get('/create/new',
                [ListingController::class, 'create'])
                ->name('create');

            // Store new listing
            Route::post('/',
                [ListingController::class, 'store'])
                ->name('store');

            // Edit listing form
            Route::get('/{listing}/edit',
                [ListingController::class, 'edit'])
                ->name('edit');

            // Update listing
            Route::post('/{listing}/update',
                [ListingController::class, 'update'])
                ->name('update');

            // Delete listing
            Route::delete('/{listing}',
                [ListingController::class, 'destroy'])
                ->name('destroy');

            // Delete single image (AJAX)
            Route::delete('/{listing}/images/{mediaId}',
                [ListingController::class, 'deleteImage'])
                ->name('images.delete');
        });
});
