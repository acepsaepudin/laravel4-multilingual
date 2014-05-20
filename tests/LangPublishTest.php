<?php

namespace Thor\Language;

/**
 *
 */
class LangPublishTest extends PackageTestCase
{
    /**
     * 
     */
    public function testPublishLang(){
        $artisan = $this->app->make('artisan');

        // Publish lang
        // We need to specify the lang path here because the tests base_path() is the package src folder
        $artisan->call('lang:publish', array('package'=>'thor/language', '--path'=>'lang'));
        
        // Check files
        $this->assertFileExists($this->app['path'].'/lang/packages/language/en/header.php');
        $this->assertFileExists($this->app['path'].'/lang/packages/language/en/footer.php');
        
        // Remove packages folder
        $this->app['files']->deleteDirectory(realpath(__DIR__.'/fixture/app/lang/packages/'));
    }
    
    /**
     * 
     */
    public function testAlternateLangLoader(){
        $this->assertEquals('Password reminder sent!', $this->app['translator']->trans('reminders.sent'));
        $this->assertEquals('Copyright 2014 Thor Framework', $this->app['translator']->trans('language::footer.copyright'));
        $this->assertEquals('Thor Framework', $this->app['translator']->trans('language::header.brand'));
        $this->assertEquals('Lorem Ipsum', $this->app['translator']->trans('language::header.subtitle'));
    }
}
