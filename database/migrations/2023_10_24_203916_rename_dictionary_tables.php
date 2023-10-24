<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameDictionaryTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('dictionary_ja_jmdict', 'dict_jp_jmdict');
        Schema::rename('dictionary_ja_jmdict_readings', 'dict_jp_jmdict_readings');
        Schema::rename('dictionary_ja_jmdict_words', 'dict_jp_jmdict_words');
        Schema::rename('dictionary_ja_kanji', 'dict_jp_kanji');
        Schema::rename('dictionary_ja_kanji_radicals', 'dict_jp_kanji_radicals');

        Schema::table('dict_jp_jmdict_readings', function(Blueprint $table) {
            $table->renameColumn('dictionary_ja_jmdict_id', 'dict_jp_jmdict_id');
        });

        Schema::table('dict_jp_jmdict_words', function(Blueprint $table) {
            $table->renameColumn('dictionary_ja_jmdict_id', 'dict_jp_jmdict_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('dict_jp_jmdict', 'dictionary_ja_jmdict');
        Schema::rename('dict_jp_jmdict_readings', 'dictionary_ja_jmdict_readings');
        Schema::rename('dict_jp_jmdict_words', 'dictionary_ja_jmdict_words');
        Schema::rename('dict_jp_kanji', 'dictionary_ja_kanji');
        Schema::rename('dict_jp_kanji_radicals', 'dictionary_ja_kanji_radicals');

        Schema::table('dictionary_ja_jmdict_readings', function(Blueprint $table) {
            $table->renameColumn('dict_jp_jmdict_id', 'dictionary_ja_jmdict_id');
        });

        Schema::table('dictionary_ja_jmdict_words', function(Blueprint $table) {
            $table->renameColumn('dict_jp_jmdict_id', 'dictionary_ja_jmdict_id');
        });
    }
}
