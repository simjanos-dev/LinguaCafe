<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('encountered_words', function (Blueprint $table) {
            $table->string('image')->nullable()->after('language');
        });

        Schema::table('phrases', function (Blueprint $table) {
            $table->string('image')->nullable()->after('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE encountered_words DROP COLUMN image');
        DB::statement('ALTER TABLE phrases DROP COLUMN image');
    }
};
