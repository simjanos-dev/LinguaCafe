<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDictionaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dictionary_ja_jmdict', function (Blueprint $table) {
            $table->id();
            $table->text('translations');
            $table->text('conjugations');
            $table->timestamps();
        });
        
        Schema::create('dictionary_ja_jmdict_words', function (Blueprint $table) {
            $table->id();    
            $table->integer('dictionary_ja_jmdict_id')->index();
            $table->string('word')->index();
            $table->timestamps();
        });

        Schema::create('dictionary_ja_jmdict_readings', function (Blueprint $table) {
            $table->id();
            $table->integer('dictionary_ja_jmdict_id')->index();
            $table->string('reading')->index();
            $table->text('word_restrictions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dict_ja_jmdict');
    }
}
