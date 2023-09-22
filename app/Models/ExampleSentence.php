<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Phrase;
use Illuminate\Support\Facades\Auth;

class ExampleSentence extends Model
{
    use HasFactory;

    function deletePhraseId($phraseId) {
        $words = json_decode($this->words);
        $phraseIdFound = false;
        foreach ($words as $word) {
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
}
