<?php

namespace Thor\Language;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;

abstract class BaseTestCase extends ContainerTestCase {

    /**
     * Set up the tests
     */
    public function setUp() {
        parent::setUp();

        // Bind Polyglot classes
        $this->app = LanguageServiceProvider::make($this->app);

        // Configure facades
        Config::setFacadeApplication($this->app);
        Lang::swap($this->app['thor.language.translator']);
    }

}
