<?php

namespace Thor\Language;

use App,
    View,
    Config;

class LanguageResolver {

    /**
     * 
     * @param int $routeSegment Route segment index, leave it null to read from the config
     * @return Language
     */
    public function resolve($routeSegment = null) {
        if (Config::get('language::use_database') === true) {
            return $this->fromDb($routeSegment);
        } else {
            return $this->fromConfig($routeSegment);
        }
    }

    /**
     * 
     * @param string $locale
     */
    protected function setLocale($locale) {
        Config::set('app.locale', $locale);
        App::setLocale($locale);
        setlocale(LC_ALL, $locale . '.utf8', $locale);
    }

    /**
     * 
     * @param \Thor\Language\Language $lang
     */
    protected function setCurrent(Language $lang) {
        Language::setCurrent($lang);
        View::share('language', $lang);
    }

    protected function fromDb($routeSegment = null) {
        $activeLangs = Language::sorted()->active()->get();
        if (count($activeLangs) > 0) {
            Config::set('language::default_language', $activeLangs[0]->code);
            Config::set('language::languages', array_pluck($activeLangs, 'full_locale', 'locale'));
            $currentCode = $this->fromRouteOrHeader($routeSegment);
            $defaultLangCode = Config::get('language::default_language');
            $defaultLang = null;
            foreach ($activeLangs as $ln) {
                if ($ln->code == $currentCode) {
                    self::setCurrent($ln);
                    $this->setLocale($ln->locale ? $ln->locale : $ln->code);
                    break;
                }
                if ($ln->code == $defaultLangCode) {
                    $defaultLang = $ln;
                }
            }
            if (Language::getCurrent() == null) {
                self::setCurrent($defaultLang);
                $this->setLocale($defaultLang->locale ? $defaultLang->locale : $defaultLang->code);
            }
        } else {
            throw new \Exception('The database has no active languages.');
        }
        return Language::getCurrent();
    }

    protected function fromConfig($routeSegment = null) {
        $activeLangs = Config::get('language::languages');
        $currentCode = $this->fromRouteOrHeader($routeSegment);
        foreach ($activeLangs as $code => $locale) {
            if ($code == $currentCode) {
                self::setCurrent(new Language(array('name' => $code, 'code' => $code, 'locale' => $locale)));
                $this->setLocale($locale);
                break;
            }
        }
        if (Language::getCurrent() == null) {
            $defaultLangCode = Config::get('language::default_language');
            self::setCurrent(new Language(array('name' => $defaultLangCode, 'code' => $defaultLangCode, 'locale' => $activeLangs[$defaultLangCode])));
            $this->setLocale($activeLangs[$defaultLangCode]);
        }
        return Language::getCurrent();
    }

    /**
     * Resolves language code from current request (route segment or HTTP_ACCEPT_LANGUAGE header as fallback)
     * @param int $routeSegment Route segment index, leave it null to read from the config
     * @return string
     */
    protected function fromRouteOrHeader($routeSegment = null) {
        $default = Config::get('language::use_header') ? $this->fromHeader('HTTP_ACCEPT_LANGUAGE') : Config::get('language::default_language');
        $language = $this->fromRoute($routeSegment, null);

        if ($language === null) {
            //if no language is specified in the url, use the default one
            $language = $default;
        }
        $langs = array_keys(Config::get('language::languages'));
        if (!in_array($language, $langs)) {
            \Event::fire('language::invalid_language', array($language, in_array($default, $langs) ? $default : Config::get('language::default_language')), false);
        }

        return $language;
    }

    /**
     * Resolves language code from the given route segment index
     * @param string $routeSegment Route segment index, leave it null to read from the config
     * @return string
     */
    protected function fromRoute($routeSegment = null) {
        $language = null;
        $routeSegment = ($routeSegment === null) ? Config::get('language::route_segment') : $routeSegment;
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
    protected function fromHeader($header = 'HTTP_ACCEPT_LANGUAGE') {
        return substr(\Request::server($header, null), 0, 2);
    }

}
