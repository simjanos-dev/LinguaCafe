<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyJmdictTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dict_jp_jmdict_words', function (Blueprint $table) {
            $table->string('word')->collation('utf8mb4_bin')->change();
        });

        Schema::table('dict_jp_jmdict_readings', function (Blueprint $table) {
            $table->string('reading')->collation('utf8mb4_bin')->change();
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
