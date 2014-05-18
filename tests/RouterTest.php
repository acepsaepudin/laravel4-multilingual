<?php

namespace Thor\Language;

class RouterTest extends BaseTestCase {

    public function testRouteFacadeIsSwapped() {
        $this->assertArrayHasKey('router', $this->app);
        $this->assertArrayHasKey('thor.language.router', $this->app);
        $this->assertInstanceOf('Thor\\Language\\Router', $this->app['router']);
    }

}
