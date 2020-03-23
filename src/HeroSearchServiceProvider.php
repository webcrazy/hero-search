<?php

namespace CarroPublic\HeroSearch;

use Elasticsearch\ClientBuilder;
use Laravel\Scout\EngineManager;
use Illuminate\Support\ServiceProvider;
use CarroPublic\HeroSearch\Commands\ElasticSearchIndex;
use CarroPublic\HeroSearch\Engines\ElasticSearchEngine;

class HeroSearchServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'carropublic');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'carropublic');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();

            $this->commands([
                ElasticSearchIndex::class,
            ]);
        }

        $this->app->singleton('elasticsearch', function() {
            return ClientBuilder::create()
                ->setHosts([
                    config('herosearch.host') . ":" . config('herosearch.port')
                ])
                ->build();
        });

        resolve(EngineManager::class)->extend('elasticsearch', function() {
            return new ElasticSearchEngine(
                app('elasticsearch')
            );
        });
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/herosearch.php', 'herosearch');

        // Register the service the package provides.
        $this->app->singleton('herosearch', function ($app) {
            return new HeroSearch;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['herosearch'];
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
            __DIR__.'/../config/herosearch.php' => config_path('herosearch.php'),
        ], 'herosearch.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/carropublic'),
        ], 'herosearch.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/carropublic'),
        ], 'herosearch.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/carropublic'),
        ], 'herosearch.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
