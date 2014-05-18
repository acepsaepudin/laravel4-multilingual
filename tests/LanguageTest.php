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
    
    public function testLanguagesTableHasTwoRecords(){
        $this->assertCount(2, Language::all());
    }

}