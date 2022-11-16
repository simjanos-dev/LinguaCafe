<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKanjiDictionaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dictionary_ja_kanji', function (Blueprint $table) {
            $table->id();
            $table->string('kanji')->collation('utf8mb4_bin')->index();
            $table->text('meanings');
            $table->text('readings_on');
            $table->text('readings_kun');
            $table->integer('grade');
            $table->integer('strokes');
            $table->integer('frequency');
            $table->integer('jlpt');
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
    }
}
