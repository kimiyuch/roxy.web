<?php

use App\Http\Controllers\Widget\FeedbackController;
use App\Http\Controllers\Widget\MeController;
use App\Http\Controllers\Widget\SessionController;
use App\Http\Middleware\AuthenticateReviewer;
use App\Http\Middleware\WidgetCors;
use Illuminate\Support\Facades\Route;

// The widget API, called cross-origin by widget.js on client sites. Per-site CORS
// (WidgetCors answers preflights itself); writes need a reviewer token.
Route::prefix('widget/{site}')->middleware(WidgetCors::class)->name('widget.')->group(function () {
    Route::options('{any?}', fn () => response()->noContent())->where('any', '.*');

    Route::middleware('throttle:widget-login')->group(function () {
        Route::post('session', [SessionController::class, 'store'])->name('session.store');
        Route::post('link', [SessionController::class, 'link'])->name('link');
    });

    Route::middleware([AuthenticateReviewer::class, 'throttle:widget'])->group(function () {
        Route::get('me', MeController::class)->name('me');
        Route::delete('session', [SessionController::class, 'destroy'])->name('session.destroy');
        Route::get('feedback', [FeedbackController::class, 'index'])->name('feedback.index');
        Route::post('feedback', [FeedbackController::class, 'store'])->middleware('throttle:widget-feedback')->name('feedback.store');
    });
});
