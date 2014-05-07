<?php

namespace Thor\I18n;

use App,
    View,
    Config;

class Resolver {

    /**
     * Current language
     * @var Language|null
     */
    protected static $current = null;

    /**
     * Returns the current resolved language code
     * @return Language|null
     */
    public static function getCurrent() {
        return self::$current;
    }

    /**
     * 
     * @param Language $current
     */
    public static function setCurrent(Language $current) {
        self::$current = $current;
        View::share('language', $current);
    }

    /**
     * 
     * @param string $locale
     */
    public static function setLocale($locale) {
        Config::set('app.locale', $locale);
        App::setLocale($locale);
        setlocale(LC_ALL, $locale . '.utf8', $locale);
    }

    /**
     * 
     * @param int $routeSegment Route segment index, leave it null to read from the config
     * @return Language
     */
    public static function resolve($routeSegment = null) {
        if (Config::get('i18n::use_database') === true) {
            $activeLangs = Language::sorted()->active()->get();
            if (count($activeLangs) > 0) {
                Config::set('i18n::default_language', $activeLangs[0]->code);
                Config::set('i18n::languages', array_pluck($activeLangs, 'locale', 'code'));
                $currentCode = self::fromRouteOrHeader($routeSegment);
                foreach ($activeLangs as $ln) {
                    if ($ln->code == $currentCode) {
                        self::setCurrent($ln);
                        self::setLocale($ln->locale);
                        break;
                    }
                }
            }
        } else {
            $activeLangs = Config::get('i18n::languages');
            $currentCode = self::fromRouteOrHeader($routeSegment);
            foreach ($activeLangs as $code => $locale) {
                if ($code == $currentCode) {
                    self::setCurrent(new Language(array('name' => $code, 'code' => $code, 'locale' => $locale)));
                    self::setLocale($locale);
                    break;
                }
            }
        }
        return self::$current;
    }

    /**
     * Resolves language code from current request (route segment or HTTP_ACCEPT_LANGUAGE header as fallback)
     * @param int $routeSegment Route segment index, leave it null to read from the config
     * @return string
     */
    public static function fromRouteOrHeader($routeSegment = null) {
        $language = self::fromRoute($routeSegment, self::fromHeader('HTTP_ACCEPT_LANGUAGE', false));
        if ($language === null) {
            $language = self::fromHeader('HTTP_ACCEPT_LANGUAGE', false);
        }

        if (($language == false) or ( !in_array($language, array_keys(Config::get('i18n::languages'))))) {
            \Event::fire('i18n::invalid', array($language, Config::get('i18n::default_language')), false);
        }

        return $language;
    }

    /**
     * Resolves language code from the given route segment index
     * @param type $routeSegment Route segment index, leave it null to read from the config
     * @param type $default
     * @return string
     */
    public static function fromRoute($routeSegment = null, $default = false) {
        $language = $default;
        $routeSegment = ($routeSegment === null) ? Config::get('i18n::route_segment') : $routeSegment;
        if (\Request::segment($routeSegment) !== null) {
            $routeLanguage = \Request::segment($routeSegment);
            if (in_array($routeLanguage, array_keys(Config::get('i18n::languages')))) {
                $language = $routeLanguage;
            }
        } else {
            // empty route
            $language = null;
        }
        return $language;
    }

    /**
     * Resolves language code from a http header
     * @param string $header Header name, HTTP_ACCEPT_LANGUAGE by default
     * @param mixed $default
     * @return string
     */
    public static function fromHeader($header = 'HTTP_ACCEPT_LANGUAGE', $default = false) {
        return substr(\Request::server($header, $default), 0, 2);
    }

}
