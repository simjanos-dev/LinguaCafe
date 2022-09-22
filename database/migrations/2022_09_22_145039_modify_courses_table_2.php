<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyCoursesTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE courses DROP COLUMN unique_word_count");
        DB::statement("ALTER TABLE courses DROP COLUMN known_word_count");
        DB::statement("ALTER TABLE courses DROP COLUMN highlighted_word_count");
        DB::statement("ALTER TABLE courses DROP COLUMN new_word_count");
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
