<?php

namespace elegisandi\IABBotDetect;

use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Support\ServiceProvider;

/**
 * Class IabServiceProvider
 * @package elegisandi\IABBotDetect
 */
class IabServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the configuration
     *
     * @return void
     */
    public function boot()
    {
        $source = realpath(__DIR__ . '/../config/iab.php');

        if ($this->app instanceof LaravelApplication) {
            $this->publishes([$source => config_path('iab.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('iab');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                \elegisandi\IABBotDetect\Commands\RefreshIABList::class,
            ]);
        }

        $this->mergeConfigFrom($source, 'iab');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('iab', function ($app) {
            $config = $app->make('config')->get('iab');

            return new Validator(null, $config);
        });

        $this->app->alias('iab', 'elegisandi\IABBotDetect\Validator');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['iab', 'elegisandi\IABBotDetect\Validator'];
    }
}
