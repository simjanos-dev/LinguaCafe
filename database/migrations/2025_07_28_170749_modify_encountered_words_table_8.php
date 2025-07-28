<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table("encountered_words")->chunkById(1000, function (Collection $encountered_words) {
            foreach ($encountered_words as $word) {
                DB::table("encountered_words")
                    ->where('id', $word->id)
                    ->update(['lemma' => $word->base_word]);
            }
        });


        Schema::table('encountered_words', function (Blueprint $table) {
            $table->dropColumn('base_word');
        });

        Schema::table("encountered_words", function (Blueprint $table) {
            $table->renameColumn('base_word_reading', 'lemma_reading');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('encountered_words', function (Blueprint $table) {
            $table->string('base_word');
        });

        DB::table("encountered_words")->chunkById(1000, function (Collection $encountered_words) {
            foreach ($encountered_words as $word) {
                DB::table("encountered_words")
                    ->where('id', $word->id)
                    ->update(['base_word' => $word->lemma]);
            }
        });

        Schema::table("encountered_words", function (Blueprint $table) {
            $table->renameColumn('lemma_reading', 'base_word_reading');
        });
    }
};
