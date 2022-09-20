<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySentencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('sentences', 'flash_cards');
        Schema::table('flash_cards', function (Blueprint $table) {
            $table->renameColumn('sentence_collection_id', 'flash_card_collection_id');
            $table->dropColumn('correct_review_count');
            $table->dropColumn('wrong_review_count');
            $table->text('reading');
            $table->integer('level')->default(1);
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
