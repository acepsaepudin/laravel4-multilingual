<?php

namespace Thor\Language;

use Illuminate\Container\Container;
use Illuminate\Support\Facades;
use Illuminate\Support\ServiceProvider;

class LanguageServiceProvider extends ServiceProvider {
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    //protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() {
        $this->package('thor/language', 'language');
        Facades\Lang::swap($this->app['thor.language.translator']);
        Facades\Route::swap($this->app['thor.language.router']);
        Facades\URL::swap($this->app['thor.language.url']);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app = $this->bindClasses($this->app);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return array('thor.language.translator', 'thor.language.router', 'thor.language.url');
    }

    /**
     * Create a Polyglot container
     *
     * @param  Container $app
     *
     * @return Container
     */
    public static function make($app = null) {
        if (!$app) {
            $app = new Container;
        }

        // Bind classes
        $provider = new static($app);
        $app = $provider->bindClasses($app);

        return $app;
    }

    /**
     * Bind package classes to a Container
     *
     * @param  Container $app
     * @return Container
     */
    public function bindClasses(Container $app) {
        $app['config']->package('thor/language', __DIR__ . '/../config');

        $app->singleton('thor.language.translator', function ($app) {
            return new Translator($app);
        });

        $app->singleton('thor.language.router', function ($app) {
            return new Router($app['events'], $app);
        });

        $app->singleton('thor.language.url', function ($app) {
            return new UrlGenerator($app['router']->getRoutes(), $app->rebinding('request', function ($app, $request) {
                        $app['url']->setRequest($request);
                    }));
        });

        return $app;
    }

}
