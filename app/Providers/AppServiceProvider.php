<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//        if(\App::environment('production')) {
//            $url->forceScheme('https');
//        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

}
