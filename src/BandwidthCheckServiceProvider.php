<?php

namespace Heath\BandwidthCheck;

use Illuminate\Support\ServiceProvider;

class BandwidthCheckServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->publishes([__DIR__ . '/config.php' => config_path('bandwidth-check.php')]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                BandwidthCheckCommand::class,
            ]);
        }
    }
}