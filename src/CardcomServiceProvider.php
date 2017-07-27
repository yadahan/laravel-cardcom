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
        $this->publishes([
            __DIR__.'/../config/cardcom.php' => config_path('cardcom.php'),
        ], 'cardcom-config');

        $this->mergeConfigFrom(__DIR__.'/../config/cardcom.php', 'cardcom');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cardcom', function () {
            return new Cardcom(config('cardcom.terminals.'.config('cardcom.terminal')));
        });
    }
}
