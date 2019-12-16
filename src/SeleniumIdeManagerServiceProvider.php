<?php

namespace Plum\SeleniumIdeManager;

use Illuminate\Support\ServiceProvider;

class SeleniumIdeManagerServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'plum');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'seleniumidemanager');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/selenium_ide_manager.php', 'selenium_ide_manager');

        // Register the service the package provides.
        $this->app->singleton('seleniumidemanager', function ($app) {
            return new SeleniumIdeManager;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['seleniumidemanager'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/selenium_ide_manager.php' => config_path('selenium_ide_manager.php'),
        ], 'seleniumidemanager.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/plum'),
        ], 'seleniumidemanager.views');*/

        // Publishing assets.
        $this->publishes([
            __DIR__ . '/../resources/assets' => public_path('vendor/plum'),
        ], 'selenium-ide-manager-assets');

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/plum'),
        ], 'seleniumidemanager.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
