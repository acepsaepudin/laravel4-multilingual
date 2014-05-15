<?php

namespace Thor\Language;

class LanguageSeeder extends \Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        \DB::table('languages')->delete();

        Language::create(array('name' => 'Español', 'code' => 'es', 'locale' => 'es_ES', 'is_active' => true, 'sorting' => 1));
        Language::create(array('name' => 'English', 'code' => 'en', 'locale' => 'en_US', 'is_active' => true, 'sorting' => 2));
    }

}