<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueueStatsChapterProcessingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queue_stats_chapter_processing', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('chapter_id');
            $table->integer('book_id');
            $table->integer('word_count')->nullable();
            $table->enum('status', ['failed', 'finished'])->default('finished');
            $table->dateTime('dispatched_at');
            $table->dateTime('started_at');
            $table->dateTime('finished_at');
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
        Schema::dropIfExists('queue_stats_chapter_processing');
    }
}
