thorfw/i18n
===========

Laravel 4 Multilingual route support and more:

* Locale and language autodetection (based on URI segment or User Agent)
* Language model, migration and seeder
* Multilingual and non-multilingual route support at the same time

## Setup

In the `require` key of `composer.json` file of your project add the following

    "thorfw/i18n": "dev-master"

Run the Composer update comand

    composer update

In your `config/app.php` add `'Thor\I18n\I18nServiceProvider'` to the end of the `$providers` array.
This will let your application to autodetect the language.

```php
'providers' => array(

    'Illuminate\Foundation\Providers\ArtisanServiceProvider',
    'Illuminate\Auth\AuthServiceProvider',
    ...
    'Thor\I18n\I18nServiceProvider',

),
```

## Database migration (optional)

In order to use the languages stored in the database, you must run the package migrations first. Seeding is also optional.

    php artisan migrate --package="thorfw/i18n"
    php artisan db:seed --class="Thor\I18n\Seeder"

Then change `'use_database'` to true.

## Enabling multilingual routes

In your routes.php, put your multilingual routes inside a Route group
with the same prefix as the current language code:

```php
Route::group(array('prefix' => \Thor\I18n\Resolver::getCurrent()->code), function() {
    // Multilingual routes here
});
```

## How it works
* When the package is booted, it looks for a matching language against the current 
route segments or the `HTTP_ACCEPT_LANGUAGE` header as a fallback (if use_header is true, disabled by default).
* If no language matches the route or the header or they are empty, the `default_language` is used.
* If an invalid language is passed in the route, a `NotFoundHttpException` is thrown.
* If an invalid language is passed in the route, the `i18n::invalid_language` event is fired with
two parameters: the not found language and the default_language.
* If you specified in the config that you want to use a database, the i18n config 
`languages` and `default_language` are retrieved and overriden from the languages table. Disabled by default.
* A variable called `$language`, instance of the `\Thor\I18n\Language` model, will be always shared through all views.


## Demo

To see how it works, use the following routes:

```php
<?php
// When users hit a route without a language,
// redirect them to the default one using a 302 redirect
Route::get('/', function() {
    return Redirect::to(\Thor\I18n\Resolver::getCurrent()->code, 302);
});

// non-multilingual route
Route::any('/hey/', function(){
    return 'Hey, I\'m not a multilingual route!';
});

// specific route in spanish
Route::any('/es/hola/', function(){
    return 'Hola mundo!';
});

// specific route in english
Route::any('/en/hello/', function(){
    return 'Hello world!';
});

// all other routes that share the same path, common in all languages
Route::group(array('prefix' => \Thor\I18n\Resolver::getCurrent()->code), function() {
    Route::any('/', function(){
        return 'Homepage in '.\Thor\I18n\Resolver::getCurrent()->code;
    });
    Route::any('/foo/', function(){
        return 'Foo page in '.\Thor\I18n\Resolver::getCurrent()->code;
    });
});
```

Try to navigate to these paths:
* /             (should redirect to the default language)
* /hey/
* /es/
* /en/
* /es/hola/
* /es/hello/    (this should throw a NotFoundHttpException)
* /en/hello/
* /en/foo/
* /es/foo/
* /foo/         (NotFoundHttpException)
