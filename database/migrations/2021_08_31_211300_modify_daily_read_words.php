<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyDailyReadWords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('daily_read_words', 'daily_achivements');
        Schema::table('daily_achivements', function (Blueprint $table) {
            $table->renameColumn('quantity', 'read_words');
            $table->integer('reviewed_sentences');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_achivements');
    }
}
