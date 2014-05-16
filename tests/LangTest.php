<?php

namespace Thor\Language;

class LangTest extends BaseTestCase {

    public function testCanChangeLanguage() {
        $this->translator->setLocale('en');

        $this->assertEquals('en', $this->translator->getLocale());
    }

    public function testCanCheckCurrentLanguage() {
        $this->translator->setLocale('en');

        $this->assertTrue($this->translator->code() === 'en');
    }

}
