thor/language
=====

Laravel 4 Multilingual route support and more:

* Locale and language autodetection (based on URI segment or User Agent)
* Optional language model, migration and seeder
* Multilingual and non-multilingual route support at the same time
* Lang, Route and URL facades are extended with useful multilingual methods like:
`Lang::code()`, `Route::langGroup()`, `URL::langTo()`, ...

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
    php artisan db:seed --class="Thor\Language\Seeder"

Then change `use_database` to `true` in the config file.

## Enabling multilingual routes

In your routes.php, put your multilingual routes inside a Route group
with the same prefix as the current language code. This is accomplished with
the a new function called `langGroup`:

```php
Route::langGroup(function() {
    // Multilingual routes here
});
```

## How it works
* This package swaps the singleton instances of Lang, Route and URL facades for extending them with multilingual features.
* When the package is booted, and `language::autoresolve` is `true` (enabled by default), it looks for a matching language against the 
route segment specified in `language::route_segment` or the `HTTP_ACCEPT_LANGUAGE` header as a
fallback (if `language::use_useragent` is true, disabled by default).
* If no language matches the route segment or the header, or they are empty, the app config `fallback_locale` variable is used.
* If an invalid language is passed in the route, the `language::invalid_language` event is fired with
two parameters: the invalid language segment and the fallback locale.
* If you specified in the config that you want to use a database in `language::use_database`, the values of 
`language::available_languages` and `fallback_locale` will be retrieved from the languages table and then overriden inside these variables. Disabled by default.


## Demo

To see how it works, use the following routes:

```php
<?php
// When users hit a route without a language,
// redirect them to the default one using a 302 redirect
Route::get('/', function() {
    return Redirect::to(Lang::code(), 302);
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
Route::langGroup(function() {
    Route::any('/', function(){
        return 'Homepage in '.Lang::code();
    });
    Route::any('/foo/', function(){
        return 'Foo page in '.Lang::code();
    });
    // current Language model instance
    Route::any('/info/', function(){
        return Lang::language();
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
    /es/info/
    /en/info/