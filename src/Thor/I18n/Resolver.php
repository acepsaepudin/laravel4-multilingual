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
                $defaultLangCode = Config::get('i18n::default_language');
                $defaultLang = null;
                foreach ($activeLangs as $ln) {
                    if ($ln->code == $currentCode) {
                        self::setCurrent($ln);
                        self::setLocale($ln->locale);
                        break;
                    }
                    if ($ln->code == $defaultLangCode) {
                        $defaultLang = $ln;
                    }
                }
                if (self::$current == null) {
                    self::setCurrent($defaultLang);
                    self::setLocale($defaultLang->code);
                }
            } else {
                throw new \Exception('The database has no active languages.');
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
            if (self::$current == null) {
                $defaultLangCode = Config::get('i18n::default_language');
                self::setCurrent(new Language(array('name' => $defaultLangCode, 'code' => $defaultLangCode, 'locale' => $activeLangs[$defaultLangCode])));
                self::setLocale($activeLangs[$defaultLangCode]);
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
        $default = Config::get('i18n::use_header') ? self::fromHeader('HTTP_ACCEPT_LANGUAGE') : Config::get('i18n::default_language');
        $language = self::fromRoute($routeSegment, null);

        if ($language === null) {
            //if no language is specified in the url, use the default one
            $language = $default;
        }
        $langs = array_keys(Config::get('i18n::languages'));
        if (!in_array($language, $langs)) {
            \Event::fire('i18n::invalid_language', array($language, in_array($default, $langs) ? $default : Config::get('i18n::default_language')), false);
        }

        return $language;
    }

    /**
     * Resolves language code from the given route segment index
     * @param string $routeSegment Route segment index, leave it null to read from the config
     * @return string
     */
    public static function fromRoute($routeSegment = null) {
        $language = null;
        $routeSegment = ($routeSegment === null) ? Config::get('i18n::route_segment') : $routeSegment;
        if (\Request::segment($routeSegment) !== null) {
            return \Request::segment($routeSegment);
        }
        return $language;
    }

    /**
     * Resolves language code from a http header
     * @param string $header Header name, HTTP_ACCEPT_LANGUAGE by default
     * @return string
     */
    public static function fromHeader($header = 'HTTP_ACCEPT_LANGUAGE') {
        return substr(\Request::server($header, null), 0, 2);
    }

}
