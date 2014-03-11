<?php

namespace Mjolnic\Language;

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
    public static function setCurrent($current) {
        self::$current = $current;
        \View::share('i18n_current_language', $current);
    }

    /**
     * 
     * @param string $locale
     */
    public static function setLocale($locale) {
        \Config::set('app.locale', $locale);
        \App::setLocale($locale);
        setlocale(LC_ALL, $locale . '.utf8', $locale);
    }

    /**
     * 
     * @param int $routeSegment Route segment index, 1 by default
     * @param boolean $useDatabase Whether to rely on the database languages or the config ones only (false by default)
     * @return Language
     */
    public static function resolve($routeSegment = 1, $useDatabase = false) {
        if ($useDatabase === true) {
            $activeLangs = Language::sorted()->active()->get();
            if (count($activeLangs) > 0) {
                \Config::set('i18n::default_locale', $activeLangs[0]->code);
                \Config::set('i18n::locales', array_pluck($activeLangs, 'locale', 'code'));
                $currentCode = self::fromRequest($routeSegment);
                foreach ($activeLangs as $ln) {
                    if ($ln->code == $currentCode) {
                        self::setCurrent($ln);
                        self::setLocale($ln->locale);
                        break;
                    }
                }
            }
        } else {
            $activeLangs = \Config::get('i18n::languages');
            $currentCode = self::fromRequest($routeSegment);
            foreach ($activeLangs as $code => $locale) {
                if ($code == $currentCode) {
                    self::setCurrent(new Language(array('name'=>$code, 'code'=>$code, 'locale'=>$locale)));
                    self::setLocale($locale);
                    break;
                }
            }
        }
        return self::$current;
    }

    /**
     * Resolves language code from current request (route segment or http header)
     * @param int $routeSegment Route segment index, 1 by default
     * @return string
     */
    public static function fromRequest($routeSegment = 1) {
        $language = self::fromRouteSegment($routeSegment, self::fromHttpHeader('HTTP_ACCEPT_LANGUAGE', false));
        if ($language === null) {
            $language = self::fromHttpHeader('HTTP_ACCEPT_LANGUAGE', false);
        }

        if (($language == false) or (!in_array($language, array_keys(\Config::get('i18n::languages'))))) {
            \Event::fire('i18n::invalid_route_language', array($language, \Config::get('i18n::default_language')), false);
        }

        return $language;
    }

    /**
     * Resolves language code from a http header
     * @param string $header Header name, HTTP_ACCEPT_LANGUAGE by default
     * @param mixed $default
     * @return string
     */
    public static function fromHttpHeader($header = 'HTTP_ACCEPT_LANGUAGE', $default = false) {
        return substr(\Request::server($header, $default), 0, 2);
    }

    /**
     * Resolves language code from the given route segment index
     * @param type $routeSegment Route segment index, 1 by default
     * @param type $default
     * @return string
     */
    public static function fromRouteSegment($routeSegment = 1, $default = false) {
        $language = $default;
        if (\Request::segment($routeSegment) !== null) {
            $routeLanguage = \Request::segment($routeSegment);
            if (in_array($routeLanguage, array_keys(\Config::get('i18n::languages')))) {
                $language = $routeLanguage;
            }
        } else {
            // empty route
            $language = null;
        }
        return $language;
    }

}
