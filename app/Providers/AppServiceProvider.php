<?php

namespace App\Providers;

use App\Http\View\Composers\CartComposer;
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
        View::composer(
            ['layouts.navigation', 'components.bottom-nav'],
            CartComposer::class
        );
    }
}
