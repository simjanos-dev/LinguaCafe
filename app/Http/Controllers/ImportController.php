<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\TextBlock;
use App\Models\Book;
use App\Models\Lesson;
use App\Models\LessonWord;


class ImportController extends Controller
{
    public function importBook() {
        DB::disableQueryLog();
        $userId = Auth::user()->id;
        $selectedLanguage = Auth::user()->selected_language;

        $tokenizedWords = Http::post('langapp-python-service-dev:8678/tokenizer/import');
        $words = json_decode($tokenizedWords->body());
        
        $rawTextChunk = '';

        $book = new Book();
        $book->user_id = $userId;
        $book->cover_image = 'default.jpg';
        $book->language = $selectedLanguage;
        $book->name = 'test';
        $book->save();


        $textChunk = [];
        $textChunkIndex = 1;
        foreach ($words as $word) {
            if (count($textChunk) < 5000) {
                $textChunk[] = $word;
            } else {
                $lesson = new Lesson();
                $lesson->user_id = Auth::user()->id;
                $lesson->name = 'Test page ' . $textChunkIndex;
                $lesson->read_count = 0;
                $lesson->word_count = 0;
                $lesson->book_id = $book->id;
                $lesson->language = $selectedLanguage;
                $lesson->raw_text = json_encode($textChunk);
                $lesson->unique_words = '';
                $lesson->save();

                $textBlock = new TextBlock();
                $textBlock->tokenizedWords = $textChunk;
                $textBlock->processTokenizedWords();
                $textBlock->collectUniqueWords();
                $textBlock->updateAllPhraseIds();
                $textBlock->createNewEncounteredWords();

// should use $textBlock->fastTokenizeRawText();

                DB::beginTransaction();
                foreach ($textBlock->processedWords as $processedWord) {
                    $processedWord->phrase_ids = json_encode($processedWord->phrase_ids);
                    // DB::insert('
                    //     INSERT INTO lesson_words 
                    //         (user_id, lesson_id, word_index, sentence_index, word, reading, lemma, lemma_reading, pos, phrase_ids) 
                    //     VALUES 
                    //         (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',[
                    //     $processedWord->user_id, $lesson->id, $processedWord->word_index, $processedWord->sentence_index, $processedWord->word,
                    //     $processedWord->reading, $processedWord->lemma, $processedWord->lemma_reading, $processedWord->pos, $processedWord->phrase_ids]);
                }

                DB::commit();
                
                $uniqueWordIds = DB
                    ::table('encountered_words')
                    ->select('id')
                    ->where('user_id', Auth::user()->id)
                    ->where('language', $selectedLanguage)
                    ->whereIn('word', $textBlock->uniqueWords)
                    ->pluck('id')
                    ->toArray();

                // update lesson word data
                $lesson->word_count = $textBlock->getWordCount();
                $lesson->unique_words = json_encode($textBlock->uniqueWords);
                $lesson->unique_word_ids = json_encode($uniqueWordIds);
                $lesson->save();

                $textChunk = [];
                $textChunkIndex ++;
            }
        }

        // echo('<pre>');
        // var_dump(json_decode($text));
        // echo('</pre>');
        echo ('ok');
        exit;
    }
}
