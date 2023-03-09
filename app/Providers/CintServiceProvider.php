<?php

namespace App\Providers;

use App\Cint\Cint;
use Illuminate\Support\ServiceProvider;

class CintServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cint', function () {
            return new Cint();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
