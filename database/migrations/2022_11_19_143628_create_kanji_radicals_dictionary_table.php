<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKanjiRadicalsDictionaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dictionary_ja_kanji_radicals', function (Blueprint $table) {
            $table->id();
            $table->string('kanji')->collation('utf8mb4_bin')->index();
            $table->text('radicals');
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
        Schema::dropIfExists('kanji_radicals_dictionary');
    }
}
