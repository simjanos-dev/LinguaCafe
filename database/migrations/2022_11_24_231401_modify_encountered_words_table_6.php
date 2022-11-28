<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyEncounteredWordsTable6 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE encountered_words DROP COLUMN last_level_up");
        Schema::table('encountered_words', function (Blueprint $table) {
            // the date when the word was added to srs for the first time
            // it's null if it was not in the srs system yet
            $table->date('added_to_srs')->nullable();

            // it's being calculated at each stage change
            // stage 0,1 and 2 words' next_review column is null
            $table->date('next_review')->nullable();

            // if a review was answered incorrectly, then this column gets set on true
            // and the word will need to be reviewed again until it guessed correct.
            $table->boolean('relearning')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
