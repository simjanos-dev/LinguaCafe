<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('user_id');
            $table->string('language');
            $table->string('type');
            
            // goal types
            // review - review all srs due today. daily mode only. 
            // read_words - read x words. daily mode only.
            // learn_words - add x new words or phrases to srs. daily mode only.
            // read_book_chapter - read x chapter/day from a book. daily mode only.
            
            $table->string('target_id')->nullable();
            $table->integer('current_chapter')->nullable();

            // quantity (words read, chapters read, count of new words saved etc.)
            $table->integer('quantity');

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
        Schema::dropIfExists('goals');
    }
}
