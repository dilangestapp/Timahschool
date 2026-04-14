<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        $forceHttps = (bool) env('FORCE_HTTPS', false) || $this->app->environment('production');

        if ($forceHttps) {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
