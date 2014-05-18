<?php

namespace Thor\Language;

/**
 * Language model test
 *
 */
class LanguageTest extends BaseTestCase {

    /**
     * Setup the test environment.
     */
    public function setUp() {
        parent::setUp();

        // create an artisan object for calling migrations
        $artisan = $this->app->make('artisan');

        // Migrate and seed
        $artisan->call('migrate', array(
            '--database' => 'testbench',
            '--path' => 'migrations',
        ));
        $artisan->call('db:seed', array(
            '--class' => 'Thor\\Language\\Seeder',
        ));
    }

    public function testLanguagesTableHasTwoRecords() {
        $this->assertCount(2, Language::all());
    }

    public function testFirstLanguage() {
        $lang = Language::find(1);
        $this->assertInstanceOf('Thor\\Language\\Language', $lang);
        $this->assertEquals('es', $lang->code);
        $this->assertEquals('es_ES', $lang->locale);
        $this->assertEquals(true, $lang->is_active);
        $this->assertEquals(1, $lang->sorting);
    }

    /**
     * @covers \Thor\Language\Language::scopeActive
     */
    public function testScopeActive() {
        $lang = Language::find(1);
        $lang->is_active = false;
        $lang->save();

        $langs = Language::active()->get();
        $this->assertCount(1, $langs);
        $this->assertEquals('en', $langs->first()->code);
    }

    /**
     * @covers \Thor\Language\Language::scopeToAssoc
     */
    public function testScopeToAssoc() {
        $langs = Language::toAssoc();
        $this->assertCount(2, $langs);
        $this->assertArrayHasKey('es', $langs);
        $this->assertArrayHasKey('en', $langs);
    }

    /**
     * @covers \Thor\Language\Language::scopeByCode
     */
    public function testScopeByCode() {
        $lang = Language::byCode('en')->first();
        $this->assertInstanceOf('Thor\\Language\\Language', $lang);
        $this->assertEquals('en', $lang->code);
        $this->assertEquals('en_US', $lang->locale);
    }

    /**
     * @covers \Thor\Language\Language::scopeByLocale
     */
    public function testScopeByLocale() {
        $lang = Language::byLocale('es_ES')->first();
        $this->assertInstanceOf('Thor\\Language\\Language', $lang);
        $this->assertEquals('es', $lang->code);
        $this->assertEquals('es_ES', $lang->locale);
    }

}
