<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Chapter;

class ModifyChaptersTable1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->enum('processing_status', ['unprocessed', 'processed', 'failed'])->default('unprocessed');
        });

        Chapter
            ::query()
            ->update(['processing_status' => 'processed']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE chapters DROP COLUMN processing_status");
    }
}
