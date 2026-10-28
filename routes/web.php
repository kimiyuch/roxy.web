<?php

use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\MagicLinkController;
use App\Http\Controllers\ReviewerController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/legal', 'legal')->name('legal');

// Source page for the Open Graph image; only reachable during local development.
if (app()->isLocal()) {
    Route::view('/og-image', 'og-image');

    // A stand-in client site for trying the widget
    Route::view('/widget-test', 'widget-test');
}

// The magic link from a reviewer's email; hands the widget a one-time code.
Route::get('r/{reviewer}', MagicLinkController::class)->middleware(['signed', 'throttle:30,1'])->name('reviewers.magic-link');

// The dashboard (Inertia + Vue). Login, logout and password reset are registered by Fortify.
Route::middleware('auth')->prefix('app')->group(function () {
    Route::redirect('/', '/app/feedback')->name('dashboard');

    Route::get('feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('feedback/{feedback}', [FeedbackController::class, 'show'])->name('feedback.show');
    Route::post('feedback/{feedback}/resolve', [FeedbackController::class, 'resolve'])->name('feedback.resolve');
    Route::delete('feedback/{feedback}', [FeedbackController::class, 'destroy'])->name('feedback.destroy');

    Route::resource('sites', SiteController::class)->except('edit');
    Route::post('sites/{site}/reviewers', [ReviewerController::class, 'store'])->name('reviewers.store');
    Route::post('sites/{site}/reviewers/{reviewer}/resend', [ReviewerController::class, 'resend'])->name('reviewers.resend');
    Route::delete('sites/{site}/reviewers/{reviewer}', [ReviewerController::class, 'destroy'])->name('reviewers.destroy');
});
