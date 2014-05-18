<?php

namespace Thor\Language;

use Illuminate\Container\Container;

class Translator extends \Illuminate\Translation\Translator {

    /**
     * The IoC Container
     *
     * @var Container
     */
    protected $app;

    /**
     * The language model instance
     *
     * @var Language|null
     */
    protected $language;

    /**
     * List of active languages from the database
     *
     * @var \Illuminate\Database\Eloquent\Collection | Language[]
     */
    protected $activeLanguages = array();

    /**
     * Build the language class
     *
     * @param Container $app
     */
    public function __construct(Container $app) {
        $this->app = $app;
        $this->loader = $app['translation.loader'];
        $this->locale = $app['config']->get('app.locale');
        $this->fallback = $app['config']->get('app.fallback_locale');
        $this->language = null;
        $this->activeLanguages = new \Illuminate\Database\Eloquent\Collection(array());

        if ($app['config']->get('language::autoresolve') === true) {
            $this->resolve();
        } else {
            $this->language = new Language(array('id' => -1, 'name' => $this->locale, 'code' => $this->locale, 'locale' => $this->locale));
        }
    }

    /**
     * The current Language model ID
     * @return int
     */
    public function id() {
        return $this->language->id;
    }

    /**
     * The current Language model ISO 639-1 code
     * @return type
     */
    public function code() {
        return $this->language->code;
    }

    /**
     * The current Language model instance
     * @return Language
     */
    public function language() {
        return $this->language;
    }

    public function resolve($routeSegment = null) {
        return $this->resolveWith($this->resolveFromRequest(($routeSegment === null) ? $this->app['config']->get('language::route_segment') : $routeSegment));
    }

    public function resolveWith($langCode) {
        if ($this->app['config']->get('language::use_database') === true) {
            return $this->resolveFromDb($langCode);
        } else {
            return $this->resolveFromConfig($langCode);
        }
    }

    public function setLanguage(Language $language, $setInternals = true) {
        $this->language = $language;
        if ($setInternals === true) {
            $this->setInternalLocale($language->locale ? $language->locale : $language->code);
        }
    }

    public function setInternalLocale($locale) {
        $this->app['config']->set('app.locale', $locale);
        $this->app->setLocale($locale);
        $this->setLocale($locale);
        return setlocale(LC_ALL, $locale);
    }

    /**
     * List of active languages from the database
     *
     * @var \Illuminate\Database\Eloquent\Collection | Language[]
     */
    public function getActiveLanguages() {
        return $this->activeLanguages;
    }

    /**
     * 
     * @return array
     */
    public function getAvailableLocales() {
        return $this->app['config']->get('language::available_locales');
    }

    /**
     * 
     * @param string $locale
     * @return boolean
     */
    public function isValidLocale($locale) {
        return in_array($locale, array_values($this->getAvailableLocales()));
    }

    /**
     * 
     * @param string $code
     * @return boolean
     */
    public function isValidCode($code) {
        return in_array($code, array_keys($this->getAvailableLocales()));
    }

    protected function resolveFromDb($langCode) {
        $this->activeLanguages = Language::sorted()->active()->get();
        if (count($this->activeLanguages) > 0) {
            // Set the first language as the fallback
            $this->app['config']->set('app.fallback_locale', $this->activeLanguages[0]->locale);
            // Override available locales
            $this->app['config']->set('language::available_locales', array_pluck($this->activeLanguages, 'locale', 'code'));
            // Current fallback lang
            $fallbackLang = $this->activeLanguages[0]->code;
            $isFound = false;
            // Lookup for a matching language code
            foreach ($this->activeLanguages as $ln) {
                if ($ln->code == $langCode) {
                    $isFound = true;
                    $this->setLanguage($ln, true);
                    break;
                }
            }
            // If not found, set the current to the fallback language
            if ($isFound === false) {
                $this->setLanguage($fallbackLang, true);
            }
        } else {
            throw new \Exception('The database has no active languages.');
        }
        return $this->language;
    }

    protected function resolveFromConfig($langCode) {
        $availableLocales = $this->getAvailableLocales();
        $isFound = false;
        foreach ($availableLocales as $code => $locale) {
            if ($code == $langCode) {
                $isFound = true;
                $this->setLanguage(new Language(array('id' => -1, 'name' => $code, 'code' => $code, 'locale' => $locale)), true);
                break;
            }
        }
        if ($isFound === false) {
            $fallbackLocale = $this->app['config']->get('app.fallback_locale');
            $locale = isset($availableLocales[$fallbackLocale]) ? $availableLocales[$fallbackLocale] : $locale;
            $this->setLanguage(new Language(array('id' => -1, 'name' => $fallbackLocale, 'code' => $fallbackLocale, 'locale' => $locale)), true);
        }
        return $this->language;
    }

    /**
     * Resolves language code from current request (route segment or HTTP_ACCEPT_LANGUAGE header as fallback)
     * @param int $routeSegment Route segment index, leave it null to read from the config
     * @return string
     */
    protected function resolveFromRequest($routeSegment = null) {
        $fallbackLangCode = $this->app['config']->get('language::use_header') ? $this->resolveFromUserAgent() : null;
        $langCode = $this->resolveFromRoute($routeSegment);

        if ($langCode === null) {
            //if no language is specified in the url, use the default one
            $langCode = $fallbackLangCode;
        }
        if (!$this->isValidCode($langCode)) {
            $this->app['events']->fire('language::invalid_language', array($langCode, $fallbackLangCode), false);
            // The following line is commented because we want the app to throw a NotFoundException
            // $langCode = null;
        }

        return $langCode;
    }

    /**
     * Resolves language code from the given route segment index
     * @param string $routeSegment Route segment index
     * @return string|null
     */
    protected function resolveFromRoute($routeSegment = 1) {
        $code = null;
        if ($this->app['request']->segment($routeSegment) !== null) {
            $code = $this->app['request']->segment($routeSegment);
        }
        return empty($code) ? null : $code;
    }

    /**
     * Resolves language code from a http header
     * @return string|null
     */
    protected function resolveFromUserAgent() {
        $code = substr($this->app['request']->server('HTTP_ACCEPT_LANGUAGE', null), 0, 2);
        return empty($code) ? null : $code;
    }

}
