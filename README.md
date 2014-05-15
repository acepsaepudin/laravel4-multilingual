thor/language
=====

Laravel 4 Multilingual route support and more:

* Locale and language autodetection (based on URI segment or User Agent)
* Language model, migration and seeder
* Multilingual and non-multilingual route support at the same time

IMPORTANT: Even though there are 1.x tags, they are not ready for production as the library is changing very fast.
Only tags from 2.0+ will be considered stable enough.

## Setup

In the `require` key of `composer.json` file of your project add the following

    "thor/language": "dev-master"

Run the Composer update comand

    composer update

In your `config/app.php` add `'Thor\Language\LanguageServiceProvider'` to the end of the `$providers` array.
This will let your application to autodetect the language.

```php
'providers' => array(

    'Illuminate\Foundation\Providers\ArtisanServiceProvider',
    'Illuminate\Auth\AuthServiceProvider',
    ...
    'Thor\Language\LanguageServiceProvider',

),
```

## Database migration (optional)

In order to use the languages stored in a database, you must run the package migrations first. Seeding is also optional.

    php artisan migrate --package="thor/language"
    php artisan db:seed --class="Thor\Language\LanguageSeeder"

Then change `use_database` to `true` in the config file.

## Enabling multilingual routes

In your routes.php, put your multilingual routes inside a Route group
with the same prefix as the current language code:

```php
Route::group(array('prefix' => thor_language()->code), function() {
    // Multilingual routes here
});
```

## How it works
* When the package is booted, it looks for a matching language against the 
route segment specified in the config file (as `route_segment`) or the `HTTP_ACCEPT_LANGUAGE` header as a fallback (if `use_header` is true, disabled by default).
* If no language matches the route or the header, or they are empty, the `language::default_language` is used.
* If an invalid language is passed in the route, the `language::invalid_language` event is fired with
two parameters: the not found language and the default language.
* If you specified in the config that you want to use a database, the values of 
`language::languages` and `language::default_language` will be retrieved from the languages table and then overriden inside these variables. Disabled by default.
* A variable called `$language`, instance of the `\Thor\Language\Language` model, will be always shared through all views.


## Demo

To see how it works, use the following routes:

```php
<?php
// When users hit a route without a language,
// redirect them to the default one using a 302 redirect
Route::get('/', function() {
    return Redirect::to(thor_language()->code, 302);
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
Route::group(array('prefix' => thor_language()->code), function() {
    Route::any('/', function(){
        return 'Homepage in '.thor_language()->code;
    });
    Route::any('/foo/', function(){
        return 'Foo page in '.thor_language()->code;
    });
});
```

Try to navigate to these paths:

    /             (should redirect to the default language)
    /hey/
    /es/
    /en/
    /es/hola/
    /es/hello/    (this should throw a NotFoundHttpException)
    /en/hello/
    /en/foo/
    /es/foo/
    /foo/         (NotFoundHttpException)
