<?php

namespace App\Providers;

use App\Mail\Transport\PhpMailTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
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
        // "phpmail": PHP's mail(), for hosts that do not allow Laravel's sendmail driver.
        Mail::extend('phpmail', fn () => new PhpMailTransport);

        View::composer('layouts.navigation', function ($view) {
            $view->with([
                'cartCount' => array_sum(session('cart', [])),
                'compareCount' => count(session('compare', [])),
            ]);
        });
    }
}
