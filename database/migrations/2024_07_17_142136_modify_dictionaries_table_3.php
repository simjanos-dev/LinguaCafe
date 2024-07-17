<?php

use App\Models\Dictionary;
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
        Schema::table('dictionaries', function (Blueprint $table) {
            $table->string('type', 64)->after('name')->default('supported');
        });

        $dictionaries = Dictionary::all();
        $dictionaries->each(function ($dictionary) {
            if (str_contains($dictionary->name, 'DeepL')) {
                $dictionary->type = 'deepl';
                $dictionary->save();
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE dictionaries DROP COLUMN type");
    }
};
