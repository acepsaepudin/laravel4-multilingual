<?php

namespace Thor\Language;

use Illuminate\Routing\UrlGenerator as IlluminateUrlGenerator;

/**
 * An UrlGenerator with multilingual features
 */
class UrlGenerator extends IlluminateUrlGenerator {

    /**
     * Get the language code of the current url
     *
     * @return string
     */
    public function lang() {
        return $this->request->segment($this->app['config']->get('language::route_segment', 1));
    }

    /**
     * Generate a absolute URL to the given path, with the current language code
     * as the prefix.
     *
     * @param  string  $path
     * @param  mixed  $extra
     * @param  bool  $secure
     * @return string
     */
    public function langTo($path, $extra = array(), $secure = null) {
        parent::to(Lang::code() . '/' . trim($path, '/'), $extra, $secure);
    }

    /**
     * Generate a absolute URL to the same page in another language
     *
     * @param  string  $language
     * @param  mixed   $extra
     * @param  bool    $secure
     * @return string
     */
    public function replaceLang($language, $extra = array(), $secure = null) {
        // Replace existing locale in current URL
        $current = preg_replace('#^/?([a-z]{2}/)?#', null, 
                preg_replace('#^/([a-z]{2})?$#', null, $this->request->getPathInfo()));

        return $this->to($language . '/' . $current, $extra, $secure);
    }

}
