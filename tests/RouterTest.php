<?php

namespace Thor\Language;

class RouterTest extends PackageTestCase {

    public function testRouteFacadeIsSwapped() {
        $this->assertArrayHasKey('router', $this->app);
        $this->assertArrayHasKey('thor.language.router', $this->app);
        $this->assertInstanceOf('Thor\\Language\\Router', $this->app['router']);
    }

    /**
     * @covers  \Thor\Language\Router::langGroup
     * @dataProvider langCodeProvider
     */
    public function testLangGroup($langCode) {
        $this->prepareRequest('/' . $langCode . '/');
        $router = $this->router;

        $this->router->langGroup(function () use ($router) {
            $router->get('foobar', 'foobar');
        });

        foreach ($this->router->getRoutes() as $r) {
            $route = $r;
            break;
        }

        $this->assertEquals($langCode . '/foobar', $route->getPath());
    }

    public function langCodeProvider() {
        return array(
            array('en'),
            array('es'),
            array('fr'),
            array('de'),
            array('it')
        );
    }

}
