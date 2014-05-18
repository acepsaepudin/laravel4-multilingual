<?php

namespace Thor\Language;

/**
 * Base Container-mocking class
 */
abstract class BaseTestCase extends \PHPUnit_Framework_TestCase {
    public function testDemo() {
        $this->assertTrue(true);
    }
}
