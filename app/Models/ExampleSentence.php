<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Phrase;
use Illuminate\Support\Facades\Auth;

class ExampleSentence extends Model
{
    use HasFactory;

    /*
        Loops through the text of the example sentence
        and adds the phrase's id to each word where
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

                if ($wordIndex - 1 === $phraseOccurences[$p][$phraseOccurenceLength - 1]->wordIndex && $phrase->words[$phraseOccurenceLength] === $lowercaseWord) {
                    $phraseOccurence = new \StdClass();
                    $phraseOccurence->word = $lowercaseWord;
                    $phraseOccurence->wordIndex = $wordIndex;
                    array_push($phraseOccurences[$p], $phraseOccurence);
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

    function deletePhraseId($phraseId) {
        $words = json_decode($this->words);
        $phraseIdFound = false;
        foreach ($words as $word) {
            var_dump($word);
            $index = array_search($phraseId, $word->phrase_ids);
            if ($index !== false) {
                $phraseIdFound = true;
                array_splice($word->phrase_ids, $index, 1);
            }
        }

        if ($phraseIdFound) {
            $this->words = json_encode($words);
            $this->save();
        }
    }

    function getTextBlockData() {
        $uniqueWords = json_decode($this->unique_words);
        $words = json_decode($this->words);
        $textBlockData = new \stdClass();
        $textBlockData->id = 0;

        // collect phrase ids
        $phraseIds = [];
        foreach ($words as $word) {
            $wordPhraseIds = $word->phrase_ids;
            foreach ($wordPhraseIds as $phraseId) {
                if (!in_array($phraseId, $phraseIds, true)) {
                    array_push($phraseIds, $phraseId);
                }
            }
        }

        $textBlockData->phrases = Phrase
            ::where('user_id', Auth::user()->id)
            ->whereIn('id', $phraseIds)
            ->get();

        foreach ($textBlockData->phrases as $phrase) {
            $phrase->words = json_decode($phrase->words);
        }

        $textBlockData->uniqueWords = EncounteredWord
            ::where('user_id', Auth::user()->id)
            ->whereIn('word', $uniqueWords)
            ->get();
    
        // replace phrase ids with phrase indexes
        foreach ($words as $wordIndex => $word) {
            $wordId = $textBlockData->uniqueWords->search(function ($item, $key) use($word) {
                return $item->word == mb_strtolower($word->word);
            });

            $word->wordIndex = $wordIndex;
            $word->sentence_index = 0;
            $word->selected = false;
            $word->hover = false;
            $word->phraseStage = 'learning';
            $word->phraseStart = false;
            $word->phraseEnd = false;
            $word->phraseIndexes = [];
            $word->stage = $textBlockData->uniqueWords[$wordId]->stage;
            $word->lookup_count = $textBlockData->uniqueWords[$wordId]->lookup_count;

            foreach($word->phrase_ids as $phraseId) {
                $index = array_search($phraseId, $phraseIds);
                array_push($word->phraseIndexes, $index);
            }
        }

        $textBlockData->words = $words;
        return $textBlockData;
    }
}
