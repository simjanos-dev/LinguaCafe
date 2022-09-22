<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Phrase;
use Illuminate\Support\Facades\Auth;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'name',
        'read_count',
        'word_count',
        'language',
        'raw_text',
        'processed_text',
    ];

    function getWordCounts($words) {
        $lessons = Lesson::where('user_id', Auth::user()->id)->where('course_id', $this->id)->get();
        $uniqueWordIds = json_decode($this->unique_word_ids);

        $wordCounts = new \StdClass();
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

    function updatePhraseIds($phraseId) {
        $words = json_decode(gzuncompress($this->processed_text));
        $phrase = Phrase::where('user_id', Auth::user()->id)->where('language', Auth::user()->selected_language)->where('id', $phraseId)->first();
        $phrase->words = json_decode($phrase->words);
        $phraseOccurences = [];
        foreach($words as $i => $word) {
            // find all instance of the new phrase in the text       
            // check if the current word is the start of the phrase
            if (mb_strtolower($word->word, 'UTF-8') == $phrase->words[0]) {
                $phraseOccurence = new \StdClass();
                $phraseOccurence->word = mb_strtolower($words[$i]->word, 'UTF-8');
                $phraseOccurence->wordIndex = $i;
                $phraseOccurence->newLineCount = 0;
                array_push($phraseOccurences, array($phraseOccurence));
            }

            // check if the current word is the continuation of a phrase
            for ($p = 0 ; $p < count($phraseOccurences); $p++) {
                if (count($phraseOccurences[$p]) == count($phrase->words)) {
                    continue;
                }

                if ($phrase->words[count($phraseOccurences[$p])] == mb_strtolower($words[$i]->word) &&
                    $i - 1 == $phraseOccurences[$p][count($phraseOccurences[$p]) - 1]->wordIndex + $phraseOccurences[$p][count($phraseOccurences[$p]) - 1]->newLineCount) {
                    
                    $phraseOccurence = new \StdClass();
                    $phraseOccurence->word = mb_strtolower($words[$i]->word);
                    $phraseOccurence->wordIndex = $i;
                    $phraseOccurence->newLineCount = 0;
                    array_push($phraseOccurences[$p], $phraseOccurence);
                }

                // count 'NEWLINE' words for comparison
                if ($word->word == 'NEWLINE') {
                    $phraseOccurences[$p][count($phraseOccurences[$p]) - 1]->newLineCount ++;
                }
            }
        }
        
        // mark all instance of the new phrase in the text
        for ($p = 0 ; $p < count($phraseOccurences); $p++) {
            if (count($phraseOccurences[$p]) < count($phrase->words)) {
                continue;
            }

            for ($i = 0; $i < count($phraseOccurences[$p]); $i++) {
                array_push($words[$phraseOccurences[$p][$i]->wordIndex]->phraseIds, $phrase->id);
            }
        }
        
        $this->processed_text = gzcompress(json_encode($words), 1);
        $this->save();
    }

    function deletePhraseIds($phraseId) {
        $words = json_decode(gzuncompress($this->processed_text));
        for ($i = count($words) - 1; $i >= 0; $i--) {
            $index = array_search($phraseId, $words[$i]->phraseIds);
            if ($index !== false) {
                array_splice($words[$i]->phraseIds, $index, 1);
                echo('deleted: '. $words[$i]->word . '<br>');
            }
        }
        
        
        $this->processed_text = gzcompress(json_encode($words), 1);
        $this->save();
    }
}
