thor/i18n
===========

Laravel 4 Multilingual route support and more:

* Locale and language autodetection (based on URI segment or User Agent)
* Language model, migration and seeder
* Multilingual route support

## Setup

In the `require` key of `composer.json` file of your project add the following

    "thor/i18n": "dev-master"

Run the Composer update comand

    $ composer update

In your `config/app.php` add `'Thor\I18n\I18nServiceProvider'` to the end of the `$providers` array
it will bind the required route for you.

```php
'providers' => array(

    'Illuminate\Foundation\Providers\ArtisanServiceProvider',
    'Illuminate\Auth\AuthServiceProvider',
    ...
    'Thor\I18n\I18nServiceProvider',

),
```

## Enabling language autodetection

In your app/start/global.php file or somewhere in your code add the following:

```php
\Thor\I18n\Resolver::resolve();
```

The resolve method accepts 2 parameters: 

* $routeSegment: Route segment index, 1 by default
* $useDatabase: Whether to rely on the database languages or not (by default, the package config languages will be used)


## Database migration (optional)

In order to use the languages stored in the database, you must run the package migrations first. Seeding is optional.

    $ php artisan migrate --package="thor/i18n"
    $ php artisan db:seed --class="Thor\I18n\Seeder"

## Enabling multilingual routes

In your routes.php, put your multilingual routes inside a Route group
with the same prefix as the current language code:

```php
Route::group(array('prefix' => \Thor\I18n\Resolver::getCurrent()->code), function() {
    // Multilingual routes here
});
```

## How it works
* 
* A variable called `$language`, instance of `\Thor\I18n\Language`, will be always shared through all views.
* If an invalid language is passed in the route, the `i18n::invalid` event is fired.
