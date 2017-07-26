<?php

namespace Yadahan\Cardcom;

use Illuminate\Support\ServiceProvider;

class CardcomServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cardcom', function () {
            return new Cardcom();
        });
    }
}
