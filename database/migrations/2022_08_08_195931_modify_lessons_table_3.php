<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Lesson;

class ModifyLessonsTable3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE lessons MODIFY raw_text MEDIUMBLOB");
        DB::statement("ALTER TABLE lessons MODIFY processed_text MEDIUMBLOB");

        $lessons = Lesson::all();
        foreach ($lessons as $lesson) {
            $sentences = json_decode($lesson->processed_text);
            $words = [];
            for ($j = 0; $j < count($sentences); $j++) {
                for ($i = 0; $i < count($sentences[$j]); $i++) {
                    $word = new \stdClass();
                    $word->word = $sentences[$j][$i];
                    $word->sentenceIndex = $j;
                    $word->phraseIds = [];
                    
                    array_push($words, $word);
                }
            }

            $lesson->processed_text = gzcompress(json_encode($words), 1);
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
