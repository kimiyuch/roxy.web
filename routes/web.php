<?php

use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\MagicLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');

// The magic link from a reviewer's email; hands the widget a one-time code.
Route::get('r/{reviewer}', MagicLinkController::class)->middleware(['signed', 'throttle:30,1'])->name('reviewers.magic-link');

// The dashboard (Inertia + Vue). Login, logout and password reset are registered by Fortify.
Route::middleware('auth')->prefix('app')->group(function () {
    Route::redirect('/', '/app/feedback')->name('dashboard');

    Route::get('feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('feedback/{feedback}', [FeedbackController::class, 'show'])->name('feedback.show');
    Route::post('feedback/{feedback}/resolve', [FeedbackController::class, 'resolve'])->name('feedback.resolve');
    Route::delete('feedback/{feedback}', [FeedbackController::class, 'destroy'])->name('feedback.destroy');
});
