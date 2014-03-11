mjolnic/language
===========

Multilingual features for Laravel 4

* Locale and languague autodetection (based on URI segment or User Agent)
* Language model and migration
* Multilingual route support

## Setup

In the `require` key of `composer.json` file of your project add the following

    "mjolnic/language": "dev-master"

Run the Composer update comand

    $ composer update

In your `config/app.php` add `'Mjolnic\Language\LanguageServiceProvider'` to the end of the `$providers` array
it will bind the required route for you.

```php
'providers' => array(

    'Illuminate\Foundation\Providers\ArtisanServiceProvider',
    'Illuminate\Auth\AuthServiceProvider',
    ...
    'Mjolnic\Thumbs\LanguageServiceProvider',

),
```

## Enabling language autodetection

In your app/start/global.php file or somewhere in your code add the following:

```php
\Mjolnic\Language\Resolver::resolve();
```

The resolve method accepts 2 parameters: 

* $routeSegment: Route segment index, 1 by default
* $useDatabase: Whether to rely on the database languages or not (by default, the package config languages will be used)


## Database migration

In order to use the languages stored in the database, you must run the package migrations first. Seeding is optional.

    $ php artisan migrate --package="mjolnic/language"
    $ php artisan db:seed --class="Mjolnic\Language\Seeder"

## Enabling multilanguage routes

In your routes.php, put your multilingual routes inside a Route group
with the same prefix as the current language code:

```php
// Multilingual routing usage sample
Route::group(array('prefix' => \Mjolnic\Language\Resolver::getCurrent()->code), function() {
    // Multilingual routes here
});
```