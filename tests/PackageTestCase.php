<?php

namespace Thor\Language;

/**
 * Base Container-mocking class
 */
abstract class PackageTestCase extends AppTestCase {

    /**
     *
     * @var Router
     */
    protected $router;

    /**
     *
     * @var UrlGenerator
     */
    protected $url;

    /**
     *
     * @var Translator
     */
    protected $translator;

    protected function getPackageProviders() {
        return array('Thor\\Language\\LanguageServiceProvider');
    }

    /**
     * Define environment setup.
     *
     * @param  Illuminate\Foundation\Application    $app
     * @return void
     */
    protected function getEnvironmentSetUp($app) {
        // reset base path to point to our package's src directory
        $app['path.base'] = __DIR__ . '/../src';

        // load package config
        $config = include $app['path.base'] . '/config/config.php';
        foreach ($config as $k => $v) {
            $app['config']->set('language::' . $k, $v);
        }

        // set default db to sqlite memory
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', array(
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ));
    }

    protected function prepareRequest($path = '/', $method = 'GET', $query = array(), $post = array()) {
        parent::prepareRequest($path, $method, $query, $post);
        // we need to resolve the language for each new request
        $this->translator->resolve();
    }

    public function setUp() {
        parent::setUp();

        $this->router = $this->app['router'];
        $this->url = $this->app['url'];
        $this->translator = $this->app['translator'];

        $this->prepareRequest('/'); // set default request to GET /
    }

}
