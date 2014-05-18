<?php

namespace Thor\Language;

/**
 * Base Container-mocking class
 */
abstract class BaseTestCase extends \Orchestra\Testbench\TestCase {

    /**
     * Define environment setup.
     *
     * @param  Illuminate\Foundation\Application    $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // reset base path to point to our package's src directory
        $app['path.base'] = __DIR__ . '/../src';

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', array(
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ));
    }

    protected function getPackageProviders() {
        return array('Thor\\Language\\LanguageServiceProvider');
    }

}
