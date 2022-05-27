<?php

namespace Traction\Laracord;

use Illuminate\Support\ServiceProvider;

class LaracordServiceProvider extends ServiceProvider {

    public $singletons = [
        // class name => singleton to make
    ];

    public function register()
    {
        // binding magic
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/discord.php' => config_path('discord.php')
        ]);
        
        if( $this->app->runningInConsole() ) {
            $this->commands([
                // register commands by class name
            ]);
        }

    }
}