<?php

namespace Thor\Language;

class TranslatorTest extends PackageTestCase {

    public function testLangFacadeIsSwapped() {
        $this->assertArrayHasKey('translator', $this->app);
        $this->assertArrayHasKey('thor.language.translator', $this->app);
        $this->assertInstanceOf('Thor\\Language\\Translator', $this->app['translator']);
    }

}
