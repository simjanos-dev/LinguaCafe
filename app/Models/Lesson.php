<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Phrase;
use App\Models\LessonWord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'name',
        'read_count',
        'word_count',
        'language',
        'raw_text',
    ];

    function getWordCounts($words) {
        $lessons = Lesson::where('user_id', Auth::user()->id)->where('book_id', $this->id)->get();
        $uniqueWordIds = json_decode($this->unique_word_ids);
        $wordCounts = new \stdClass();
        $wordCounts->total = $this->word_count;
        $wordCounts->unique = count($uniqueWordIds);
        $wordCounts->known = 0;
        $wordCounts->highlighted = 0;
        $wordCounts->new = 0;
        
        foreach($uniqueWordIds as $wordId) {
            if ($words[$wordId]['stage'] < 0) {
                $wordCounts->highlighted ++;
            }

            if ($words[$wordId]['stage'] == 0) {
                $wordCounts->known ++;
            }

            if ($words[$wordId]['stage'] == 2) {
                $wordCounts->new ++;
            }
        }

        return $wordCounts;
    }

    /*
        Loops through the text of the lesson and 
        adds the phrase's id to each word where
        the phrase containes the word. 

        The words must be saved after the function
        has finished. It works this way to increase
        performance.
    */
    function updatePhraseIds($phraseId, &$words) {
        // Retrieve phrase.
        $phrase = Phrase
            ::where('user_id', Auth::user()->id)
            ->where('language', Auth::user()->selected_language)
            ->where('id', $phraseId)
            ->first();

        // Decode json content.
        $phrase->words = json_decode($phrase->words);
        $phraseLength = count($phrase->words);

        $phraseOccurences = [];
        foreach($words as $wordIndex => $word) {
            $lowercaseWord = mb_strtolower($word->word, 'UTF-8');
            
            // Check if the current word is the start of the phrase.
            if ($lowercaseWord == $phrase->words[0]) {
                $phraseOccurence = new \stdClass();
                $phraseOccurence->word = $lowercaseWord;
                $phraseOccurence->wordIndex = $wordIndex;
                $phraseOccurence->newLineCount = 0;
                array_push($phraseOccurences, array($phraseOccurence));
            }

            // Check if the current word is the continuation of a phrase.
            for ($p = 0 ; $p < count($phraseOccurences); $p++) {
                $phraseOccurenceLength = count($phraseOccurences[$p]);
                
                // If the phrase occurance length equals with phrase length
                // then it means it's an exact match match. There is no need 
                // for further comparison, so the loop can be skipped.
                if ($phraseOccurenceLength === $phraseLength) {
                    continue;
                }

                if ($wordIndex - 1 === $phraseOccurences[$p][$phraseOccurenceLength - 1]->wordIndex + $phraseOccurences[$p][$phraseOccurenceLength - 1]->newLineCount 
                    && $phrase->words[$phraseOccurenceLength] === $lowercaseWord) {
                    
                    $phraseOccurence = new \stdClass();
                    $phraseOccurence->word = $lowercaseWord;
                    $phraseOccurence->wordIndex = $wordIndex;
                    $phraseOccurence->newLineCount = 0;
                    array_push($phraseOccurences[$p], $phraseOccurence);
                }
 
                // Count 'NEWLINE' words. This is needed because phrases doesn't 
                // have them, so it must be skipped when comparing them with text. 
                if ($word->word === 'NEWLINE') {
                    $phraseOccurences[$p][$phraseOccurenceLength - 1]->newLineCount ++;
                }
            }
        }
        
        // Mark all instance of the phrase in text.
        for ($p = 0 ; $p < count($phraseOccurences); $p++) {

            // Skip partial phrase matches. 
            if (count($phraseOccurences[$p]) < count($phrase->words)) {
                continue;
            }

            for ($i = 0; $i < count($phraseOccurences[$p]); $i++) {
                $tempArray = $words[$phraseOccurences[$p][$i]->wordIndex]->phrase_ids;
                
                array_push($tempArray, $phrase->id);
                $words[$phraseOccurences[$p][$i]->wordIndex]->phrase_ids = $tempArray;
            }
        }
    }
}
