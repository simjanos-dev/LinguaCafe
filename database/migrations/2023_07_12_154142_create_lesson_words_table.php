<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonWordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('lesson_words');
        Schema::create('lesson_words', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->index();
            $table->integer('lesson_id')->index();
            $table->integer('word_index')->index();
            $table->integer('sentence_index')->index();
            $table->string('word', 256)->collation('utf8mb4_bin');
            $table->string('reading', 256)->collation('utf8mb4_bin');
            $table->string('lemma', 256)->collation('utf8mb4_bin');
            $table->string('lemma_reading', 256)->collation('utf8mb4_bin');
            $table->string('pos', 256);
            $table->string('phrase_ids', 512);
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
        Schema::dropIfExists('lesson_words');
    }
}
