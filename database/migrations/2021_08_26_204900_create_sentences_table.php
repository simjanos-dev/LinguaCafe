<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateSentencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sentences', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('sentence_collection_id');
            $table->string('language');
            $table->text('sentence_raw');
            $table->text('sentence_processed');
            $table->text('translation');
            $table->integer('correct_review_count');
            $table->integer('wrong_review_count');
            $table->date('last_reviewed')->nullable();
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
        Schema::dropIfExists('sentences');
    }
}
