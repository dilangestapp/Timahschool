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
        $forceHttps = filter_var(env('FORCE_HTTPS', false), FILTER_VALIDATE_BOOL) || $this->app->environment('production');

        if ($forceHttps) {
            URL::forceScheme('https');
        }
    }
}
