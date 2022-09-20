<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEncounteredWordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('encountered_words', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('language');
            $table->integer('stage');
            $table->string('word');
            $table->string('kanji');
            $table->string('reading');
            $table->text('translation');
            $table->text('example_sentence');
            $table->timestamps();

            //stage
            // -10 -> -1 strength
            // 0 learned
            // 1 ignored
            // 2 newly encountered. only used in reading mode, changed to 0 when finishing lesson
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('encountered_words');
    }
}
