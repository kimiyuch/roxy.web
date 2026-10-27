<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    private function configureRateLimiting(): void
    {
        // Code exchange and "email me a link": per IP, and per site so one site can't
        // be used to spray emails.
        RateLimiter::for('widget-login', fn (Request $request) => [
            Limit::perMinute(10)->by('ip:'.$request->ip()),
            Limit::perHour(100)->by('site:'.$request->route('site')?->id),
        ]);

        RateLimiter::for('widget', fn (Request $request) => Limit::perMinute(120)
            ->by('reviewer:'.$request->attributes->get('reviewer')?->id));

        RateLimiter::for('widget-feedback', fn (Request $request) => [
            Limit::perMinute(10)->by('reviewer:'.$request->attributes->get('reviewer')?->id),
            Limit::perHour(300)->by('site:'.$request->route('site')?->id),
        ]);

        RateLimiter::for('mcp', fn (Request $request) => Limit::perMinute(120)
            ->by('user:'.$request->user()?->id));
    }
}
