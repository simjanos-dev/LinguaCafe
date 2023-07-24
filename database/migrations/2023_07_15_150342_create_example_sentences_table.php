<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\EncounteredWord;
use App\Models\ExampleSentence;
use App\Models\Phrase;

class CreateExampleSentencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // target_type = word, phrase
        // target_id = i of unique word or phrase
        Schema::dropIfExists('example_sentences');
        Schema::create('example_sentences', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('language', 256);
            $table->integer('target_id');
            $table->string('target_type');
            $table->text('words');
            $table->text('unique_words');
            $table->timestamps();
        });

        // Create example sentences from words.
        $words = EncounteredWord
            ::where('language', 'japanese')
            ->where('example_sentence', '<>', '')
            ->where('stage', '<', 0)
            ->get();

        foreach ($words as $word) {
            $exampleSentence = new ExampleSentence();
            $exampleSentence->user_id = 1;
            $exampleSentence->language = $word->language;
            $exampleSentence->target_type = 'word';
            $exampleSentence->target_id = $word->id;
            $exampleSentence->words = [];
            $exampleSentence->unique_words = [];

            $exampleSentenceWords = json_decode($word->example_sentence);
            $exampleSentenceWordsArray = [];
            $uniqueWords = [];
            foreach ($exampleSentenceWords as $exampleSentenceWord) {
                $lowercaseWord = mb_strtolower($exampleSentenceWord, 'UTF-8');
                $wordData = new \stdClass();
                $wordData->word = $lowercaseWord;
                $wordData->phrase_ids = [];

                array_push($exampleSentenceWordsArray, $wordData);

                if (!in_array($lowercaseWord, $exampleSentence->unique_words, true)) {
                    array_push($uniqueWords, $lowercaseWord);
                }
            }

            $exampleSentence->words = json_encode($exampleSentenceWordsArray);
            $exampleSentence->unique_words = json_encode($uniqueWords);
            $exampleSentence->save();
        }

        // Create example sentences from phrases.
        $phrases = Phrase
            ::where('language', 'japanese')
            ->where('stage', '<', 0)
            ->get();

        foreach ($phrases as $phrase) {
            $exampleSentence = new ExampleSentence();
            $exampleSentence->user_id = 1;
            $exampleSentence->language = $word->language;
            $exampleSentence->target_type = 'phrase';
            $exampleSentence->target_id = $phrase->id;
            $exampleSentence->words = [];
            $exampleSentence->unique_words = [];


            // find phrase instance in text
            $exampleSentenceWords = json_decode($phrase->words);
            $exampleSentenceWordsArray = [];
            $uniqueWords = [];
            foreach ($exampleSentenceWords as $exampleSentenceWord) {
                $wordData = new \stdClass();
                $wordData->word = $exampleSentenceWord;
                $wordData->phrase_ids = [];

                array_push($exampleSentenceWordsArray, $wordData);

                if (!in_array($exampleSentenceWord, $exampleSentence->unique_words, true)) {
                    array_push($uniqueWords, $exampleSentenceWord);
                }
            }

            $exampleSentence->words = json_encode($exampleSentenceWordsArray);
            $exampleSentence->unique_words = json_encode($uniqueWords);
            $exampleSentence->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('example_sentences');
    }
}
