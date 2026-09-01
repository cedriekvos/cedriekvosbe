<?php

namespace App\Providers;

use Illuminate\Foundation\DevCommands;
use Illuminate\Support\Carbon;
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
        Carbon::setLocale(config('app.locale'));

        /*
         * Locally the site runs in Sail, whose container is already an
         * "artisan serve" on port 80. The dev command's own server would bind
         * 127.0.0.1:8000 — a port compose never publishes — and serve nobody.
         * The queue, log and Vite processes stay.
         */
        if ($this->app->environment('local')) {
            DevCommands::except('server');
        }
    }
}
