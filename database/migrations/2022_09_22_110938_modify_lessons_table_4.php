<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\EncounteredWord;
use App\Models\Lesson;

class ModifyLessonsTable4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE lessons ADD COLUMN unique_word_ids TEXT");
        DB::statement("ALTER TABLE lessons DROP COLUMN unique_word_count");
        DB::statement("ALTER TABLE lessons DROP COLUMN known_word_count");
        DB::statement("ALTER TABLE lessons DROP COLUMN highlighted_word_count");
        DB::statement("ALTER TABLE lessons DROP COLUMN new_word_count");
        
        $lessons = Lesson::all();
        foreach ($lessons as $lesson) {
            $uniqueWords = EncounteredWord::select(['id'])->where('language', $lesson->language)->whereIn('word', json_decode($lesson->unique_words))->get();
            $uniqueWordIds = [];

            foreach($uniqueWords as $word) {
                if (!in_array($word->id, $uniqueWordIds, true)) {
                    array_push($uniqueWordIds, $word->id);
                }
            }

            $lesson->unique_word_ids = json_encode($uniqueWordIds);
            $lesson->save();
        }
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
