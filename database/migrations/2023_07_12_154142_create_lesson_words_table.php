<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Lesson;
use App\Models\LessonWord;

class CreateLessonWordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('lesson_words');
        Schema::create('lesson_words', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->index();
            $table->integer('lesson_id')->index();
            $table->integer('word_index')->index();
            $table->integer('sentence_index')->index();
            $table->string('word', 256)->collation('utf8mb4_bin');
            $table->string('reading', 256)->collation('utf8mb4_bin');
            $table->string('lemma', 256)->collation('utf8mb4_bin');
            $table->string('lemma_reading', 256)->collation('utf8mb4_bin');
            $table->string('pos', 256);
            $table->string('phrase_ids', 512);
            $table->timestamps();
        });

        DB::beginTransaction();

        $lessons = Lesson::where('language', 'japanese')->get();
        foreach ($lessons as $lesson) {
            $processedText = json_decode(gzuncompress($lesson->processed_text));

            foreach($processedText as $wordIndex => $word) {
                $lessonWord = new LessonWord();
                $lessonWord->user_id = 1;
                $lessonWord->lesson_id = $lesson->id;
                $lessonWord->word_index = $wordIndex;
                $lessonWord->sentence_index = $word->sentenceIndex;
                $lessonWord->word = $word->word;
                $lessonWord->reading = $word->reading;
                $lessonWord->lemma = $word->lemma;
                $lessonWord->lemma_reading = $word->lemmaReading;
                $lessonWord->pos = '';
                $lessonWord->phrase_ids = json_encode($word->phraseIds);
                
                $lessonWord->save();
            }
        }

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lesson_words');
    }
}
