<?php

namespace Thor\Language;

class RouterTest extends BaseTestCase {

    public function testCanCreateLangGroups() {
        $this->app['request'] = $this->mockRequest('en');
        $router = $this->router;

        $this->router->langGroup(array('before' => 'auth'), function () use ($router) {
            $router->get('foobar', 'foobar');
        });

        foreach ($this->router->getRoutes() as $r) {
            $route = $r;
        }

        $this->assertEquals('en/foobar', $route->getPath());
    }

    public function testCanCreateLangGroupsWithoutArrays() {
        $this->app['request'] = $this->mockRequest('en');
        $router = $this->router;

        $this->router->langGroup(function () use ($router) {
            $router->get('foobar', 'foobar');
        });

        foreach ($this->router->getRoutes() as $r) {
            $route = $r;
        }

        $this->assertEquals('en/foobar', $route->getPath());
    }

    public function testCanCreateLangGroupsWithPrefix() {
        $this->app['request'] = $this->mockRequest('en');
        $router = $this->router;

        $this->router->langGroup(array('before' => 'auth', 'prefix'=>'admin'), function () use ($router) {
            $router->get('foobar', 'foobar');
        });

        foreach ($this->router->getRoutes() as $r) {
            $route = $r;
        }

        $this->assertEquals('en/admin/foobar', $route->getPath());
    }

}
