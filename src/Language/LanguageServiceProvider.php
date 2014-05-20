<?php

namespace Thor\Language;

use Illuminate\Container\Container;
use Illuminate\Support\Facades;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\TranslationServiceProvider as ServiceProviders;

class LanguageServiceProvider extends ServiceProvider
{
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
    public function boot()
    {
        Facades\Lang::swap($this->app['thor.language.translator']);
        Facades\Route::swap($this->app['thor.language.router']);
        Facades\URL::swap($this->app['thor.language.url']);

        $this->commands(array(
            'thor.language.command.publish'
        ));

        // We are using PSR-4, so we need to specify the path
        $this->package('thor/language', 'language', realpath(__DIR__ . '/../'));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app = $this->bindClasses($this->app);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array(
            'thor.language.translation.loader',
            'thor.language.translator',
            'thor.language.router',
            'thor.language.url'
        );
    }

    /**
     * Create a Polyglot container
     *
     * @param  Container $app
     *
     * @return Container
     */
    public static function make($app = null)
    {
        if(!$app) {
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
    public function bindClasses(Container $app)
    {
        $app['config']->package('thor/language', realpath(__DIR__ . '/../config'));

        $app->bindShared('thor.language.translation.loader', function($app) {
            return new FileLoader($app['files'], $app['path'] . '/lang');
        });

        $app->bindShared('thor.language.publisher', function($app) {
            return new Publisher($app['files'], $app['path'] . '/lang');
        });

        $app->bindShared('thor.language.command.publish', function($app) {
            return new PublishCommand($app['thor.language.publisher']);
        });

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
