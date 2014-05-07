<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLanguagesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('languages', function(Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->string('locale', 20)->unique();
            $table->boolean('is_active')->default(1);
            $table->integer('sorting')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('languages');
    }

}
