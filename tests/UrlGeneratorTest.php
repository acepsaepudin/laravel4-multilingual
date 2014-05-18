<?php

namespace Thor\Language;

class UrlGeneratorTest extends BaseTestCase {

    public function testURLFacadeIsSwapped() {
        $this->assertArrayHasKey('url', $this->app);
        $this->assertArrayHasKey('thor.language.url', $this->app);
        $this->assertInstanceOf('Thor\\Language\\UrlGenerator', $this->app['url']);
    }

}
