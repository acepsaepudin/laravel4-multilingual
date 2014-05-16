<?php

namespace Thor\Language;

class UrlGeneratorTest extends BaseTestCase {

    public function testCanGetLocaleFromUrl() {
        $locale = $this->url->lang();
        $this->assertEquals('fr', $locale);

        $request = $this->mockRequest();
        $request->shouldReceive('segment')->with(1)->andReturn('ds');
        $this->mockUrl($request);

        $this->assertEquals('fr', $this->url->lang());
    }

}
